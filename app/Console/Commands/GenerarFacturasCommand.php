<?php

namespace App\Console\Commands;

use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Cufd;
use App\Models\Cuis;
use App\Models\Detalle;
use App\Models\Factura;
use App\Models\Lote;
use App\Models\Movimiento;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\SiatFacturacionService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerarFacturasCommand extends Command
{
    /**
     * Nombre y firma del comando en consola.
     *
     * @var string
     */
    protected $signature = 'siat:generar-facturas
                            {--cantidad=5 : Cantidad de facturas a generar y emitir (por defecto: 5)}
                            {--pv= : Código de Punto de Venta (0 o 1) o ID del registro}
                            {--punto-venta= : Alias alternativo para --pv}
                            {--cliente= : ID o NIT/CI del cliente específico (opcional)}';

    /**
     * Descripción del comando en consola.
     *
     * @var string
     */
    protected $description = 'Genera y emite facturas individuales válidas ante el SIAT (con registro completo en BD, stock y Kardex) para pruebas de anulación y reversión';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle(SiatFacturacionService $siatService): int
    {
        $this->info('================================================================');
        $this->info('  SIAT BOLIVIA - GENERADOR DE FACTURAS EN LÍNEA (COMPRA-VENTA)  ');
        $this->info('  Emisión y Registro Atómico en BD para Pruebas y Anulaciones   ');
        $this->info('================================================================');

        $cantidad = (int) ($this->option('cantidad') ?: 5);
        if ($cantidad <= 0) {
            $this->error('La cantidad de facturas a generar debe ser mayor a cero.');

            return 1;
        }

        // 1. Resolver Punto de Venta
        $pvInput = $this->option('pv') ?? $this->option('punto-venta');
        if ($pvInput !== null) {
            $puntoVenta = PuntoVenta::where('codigo', $pvInput)->first() ?? PuntoVenta::find($pvInput);
            if (! $puntoVenta) {
                $this->error("No se encontró ningún Punto de Venta con código o ID '{$pvInput}'.");

                return 1;
            }
        } else {
            // Por defecto Punto de Venta 0 (Casa Matriz / Central)
            $puntoVenta = PuntoVenta::where('codigo', 0)->first() ?? PuntoVenta::first();
        }

        if (! $puntoVenta) {
            $this->error('No existen Puntos de Venta registrados en el sistema.');

            return 1;
        }

        // 2. Resolver Sucursal
        $sucursal = $puntoVenta->sucursal ?? Sucursal::orderBy('codigo_sucursal', 'asc')->first();
        if (! $sucursal) {
            $this->error('No existe ninguna sucursal registrada en el sistema.');

            return 1;
        }

        // 3. Resolver Usuario del Sistema
        $usuario = User::first();
        if (! $usuario) {
            $this->error('No se encontró ningún usuario en la base de datos para registrar las ventas.');

            return 1;
        }

        // 4. Resolver Turno de Caja Activo
        $caja = Caja::where('punto_venta_id', $puntoVenta->id)
            ->whereIn('estado', ['ABIERTA', 'Abierta'])
            ->latest('id')
            ->first();

        if (! $caja) {
            $caja = Caja::create([
                'usuario_id' => $usuario->id,
                'sucursal_id' => $sucursal->id,
                'punto_venta_id' => $puntoVenta->id,
                'monto_apertura' => 100.00,
                'saldo' => 100.00,
                'total_venta' => 0.00,
                'venta_contado' => 0.00,
                'total_qr' => 0.00,
                'total_transferencia' => 0.00,
                'otro_ingreso' => 0.00,
                'total_salida' => 0.00,
                'estado' => 'ABIERTA',
                'fecha_apertura' => now(),
                'usuario_creador_id' => $usuario->id,
            ]);
            $this->comment("  ✔ Se aperturó automáticamente una caja activa (ID: {$caja->id}) para el Punto de Venta {$puntoVenta->codigo}.");
        }

        // 5. Validar Credenciales SIAT (CUFD y CUIS)
        $cufd = $siatService->obtenerCufdVigente($sucursal->id, $puntoVenta->id);
        if (! $cufd) {
            $this->warn("  ⚠ Sin CUFD vigente en BD para PV {$puntoVenta->codigo}. Solicitando renovación automática...");
            $resCufd = $siatService->obtenerCufd($sucursal->id, $puntoVenta->id);
            if (! ($resCufd['success'] ?? false)) {
                $this->error('  ✖ Error crítico: No se pudo obtener CUFD vigente: '.($resCufd['mensaje'] ?? 'Fallo SOAP'));

                return 1;
            }
            $cufd = $resCufd['cufd'] ?? Cufd::where('sucursal_id', $sucursal->id)->where('punto_venta_id', $puntoVenta->id)->latest('id')->first();
        }

        $cuis = Cuis::where('sucursal_id', $sucursal->id)
            ->where('punto_venta_id', $puntoVenta->id)
            ->where('estado', 'ACTIVO')
            ->latest('id')
            ->first() ?? Cuis::where('sucursal_id', $sucursal->id)->latest('id')->first();

        if (! $cuis) {
            $this->error("  ✖ Error: No existe CUIS activo para el Punto de Venta {$puntoVenta->codigo}.");

            return 1;
        }

        // 6. Resolver Cliente
        $clienteOpt = $this->option('cliente');
        $cliente = null;
        if ($clienteOpt) {
            $cliente = Cliente::find($clienteOpt)
                ?? Cliente::where('nit', $clienteOpt)->orWhere('cedula', $clienteOpt)->first();
        }
        if (! $cliente) {
            $cliente = Cliente::firstOrCreate(
                ['id' => 1],
                [
                    'nombres' => 'S/N',
                    'apellidos' => 'S/N',
                    'razon_social' => 'S/N',
                    'cedula' => null,
                    'complemento' => null,
                    'nit' => '99001',
                    'estado' => 'ACTIVO',
                ]
            );
        }

        // 7. Resolver Producto y Lote Físico con Stock
        $producto = Producto::where('estado', 'ACTIVO')->first();
        if (! $producto) {
            $producto = Producto::create([
                'nombre' => 'PARACETAMOL 500MG COMPRIMIDOS',
                'principio_activo' => 'PARACETAMOL',
                'presentacion' => 'CAJA X 100',
                'laboratorio' => 'LABORATORIOS COFAR S.A.',
                'precio_venta' => 12.00,
                'precio_compra' => 6.00,
                'codigo_producto_sin' => 62111,
                'unidad_medida_sin' => 57,
                'estado' => 'ACTIVO',
            ]);
        }

        $lote = Lote::where('sucursal_id', $sucursal->id)
            ->where('stock_actual', '>=', 1)
            ->where('estado', 'ACTIVO')
            ->where('fecha_vencimiento', '>', now())
            ->orderBy('fecha_vencimiento', 'asc')
            ->first();

        if (! $lote) {
            $lote = Lote::create([
                'producto_id' => $producto->id,
                'sucursal_id' => $sucursal->id,
                'numero_lote' => 'LOT-TEST-'.strtoupper(Str::random(5)),
                'fecha_vencimiento' => now()->addYears(2)->toDateString(),
                'precio_compra' => (float) ($producto->precio_compra ?: 6.00),
                'precio_venta' => (float) ($producto->precio_venta ?: 12.00),
                'stock_inicial' => 500,
                'stock_actual' => 500,
                'stock_minimo' => 5,
                'estado' => 'ACTIVO',
                'usuario_creador_id' => $usuario->id,
            ]);
            $this->comment("  ✔ Se habilitó lote de prueba con stock: {$lote->numero_lote} (Stock: {$lote->stock_actual}).");
        }

        $this->line('');
        $this->info('Parámetros de Configuración:');
        $this->line("  • Punto de Venta : PV {$puntoVenta->codigo} - {$puntoVenta->nombre} (ID: {$puntoVenta->id})");
        $this->line("  • Sucursal       : Sucursal {$sucursal->codigo_sucursal} - {$sucursal->nombre}");
        $this->line('  • CUFD Vigente   : '.substr($cufd->codigo, 0, 30).'... (Vence: '.$cufd->fecha_vigencia.')');
        $this->line("  • CUIS Activo    : {$cuis->codigo}");
        $this->line("  • Cliente        : {$cliente->razon_social} (NIT/CI: ".($cliente->nit ?: ($cliente->cedula ?: '99001')).')');
        $this->line("  • Producto       : {$producto->nombre} | Precio Venta: Bs. ".number_format($producto->precio_venta, 2));
        $this->line("  • Cantidad       : {$cantidad} facturas");
        $this->line('');

        $this->comment("Iniciando emisión de {$cantidad} facturas en el SIAT y registro en BD...");

        $facturasEmitidas = [];
        $validadas = 0;
        $observadas = 0;
        $nitEmisor = (string) config('siat.nit');
        $codigoModalidad = (int) config('siat.codigo_modalidad');

        for ($i = 1; $i <= $cantidad; $i++) {
            $precioUnitario = (float) $producto->precio_venta;
            $numeroFactura = $siatService->obtenerSiguienteNumeroFactura($sucursal->id, $puntoVenta->id);
            $fechaActual = now();
            $fechaHoraCadena = $fechaActual->format('YmdHis').'000';

            $cuf = $siatService->calcularCuf(
                $nitEmisor,
                $fechaHoraCadena,
                (int) $sucursal->codigo_sucursal,
                $codigoModalidad,
                1, // Tipo Emisión 1 (En Línea)
                1, // Tipo Factura 1 (Con derecho a Crédito Fiscal)
                1, // Tipo Documento Sector 1 (Factura Compra Venta)
                $numeroFactura,
                (int) $puntoVenta->codigo,
                $cufd->codigo_control
            );

            DB::beginTransaction();
            try {
                // Asegurar stock suficiente en el lote
                $lote = Lote::where('id', $lote->id)->lockForUpdate()->first();
                if ((float) $lote->stock_actual < 1) {
                    $lote->stock_actual = 500;
                    $lote->estado = 'ACTIVO';
                    $lote->save();
                }

                $nuevoStock = (float) $lote->stock_actual - 1;
                $lote->stock_actual = $nuevoStock;
                if ($nuevoStock <= 0) {
                    $lote->estado = 'AGOTADO';
                }
                $lote->usuario_modificador_id = $usuario->id;
                $lote->save();

                // 1. Factura
                $factura = Factura::create([
                    'usuario_creador_id' => $usuario->id,
                    'cufd_id' => $cufd->id,
                    'sucursal_id' => $sucursal->id,
                    'caja_id' => $caja->id,
                    'cliente_id' => $cliente->id,
                    'punto_venta_id' => $puntoVenta->id,
                    'fecha' => $fechaActual,
                    'nit' => $cliente->nit ?: ($cliente->cedula ?: '99001'),
                    'razon_social' => $cliente->razon_social ?: 'S/N',
                    'numero_factura' => (string) $numeroFactura,
                    'facturado' => 'SI',
                    'codigo_metodo_pago' => 1, // Efectivo
                    'total' => $precioUnitario,
                    'monto_total_subjeto_iva' => $precioUnitario,
                    'descuento_adicional' => 0.00,
                    'cuf' => $cuf,
                    'tipo_factura' => '1',
                    'estado_pago' => 'PAGADO',
                    'estado' => 'EMITIDA',
                ]);

                // 2. Detalle
                $detalle = Detalle::create([
                    'usuario_creador_id' => $usuario->id,
                    'factura_id' => $factura->id,
                    'producto_id' => $producto->id,
                    'lote_id' => $lote->id,
                    'sucursal_id' => $sucursal->id,
                    'punto_venta_id' => $puntoVenta->id,
                    'nombre_producto' => $producto->nombre,
                    'numero_lote' => $lote->numero_lote,
                    'fecha_vencimiento' => $lote->fecha_vencimiento,
                    'precio' => $precioUnitario,
                    'cantidad' => 1,
                    'descuento' => 0,
                    'total' => $precioUnitario,
                    'importe' => $precioUnitario,
                    'fecha' => $fechaActual,
                    'estado' => 'ACTIVO',
                ]);

                // 3. Kardex
                Movimiento::create([
                    'producto_id' => $producto->id,
                    'sucursal_id' => $sucursal->id,
                    'lote_id' => $lote->id,
                    'tipo_movimiento' => 'VENTA',
                    'ingreso' => 0,
                    'salida' => 1,
                    'saldo_stock' => $nuevoStock,
                    'numero_lote' => $lote->numero_lote,
                    'fecha_caducidad' => $lote->fecha_vencimiento,
                    'fecha' => $fechaActual,
                    'descripcion' => "Venta generada según Factura N° {$numeroFactura}",
                    'precio_compra' => (float) $lote->precio_compra,
                    'precio_venta' => $precioUnitario,
                    'estado' => 'ACTIVO',
                    'usuario_creador_id' => $usuario->id,
                ]);

                // 4. Pago
                Pago::create([
                    'usuario_creador_id' => $usuario->id,
                    'factura_id' => $factura->id,
                    'punto_venta_id' => $puntoVenta->id,
                    'caja_id' => $caja->id,
                    'tipo_pago' => 'EFECTIVO',
                    'monto' => $precioUnitario,
                    'cambio' => 0.00,
                    'fecha' => $fechaActual,
                    'descripcion' => "Pago en efectivo Factura #{$numeroFactura}",
                    'estado' => 'ACTIVO',
                ]);

                // 5. Acumuladores de Caja
                $caja->total_venta = (float) $caja->total_venta + $precioUnitario;
                $caja->venta_contado = (float) $caja->venta_contado + $precioUnitario;
                $caja->saldo = (float) $caja->saldo + $precioUnitario;
                $caja->save();

                // 6. Generar XML Oficial
                $xml = $siatService->generarXmlFactura($factura, [$detalle], $cufd, $sucursal, $puntoVenta);
                $factura->productos_xml = $xml;

                // 7. Enviar por SOAP al SIAT
                $resSiat = $siatService->enviarFacturaSiat($factura, $xml);

                if ($resSiat['success']) {
                    $factura->codigo_recepcion = $resSiat['codigo_recepcion'];
                    $factura->codigo_transaccion = 'VALIDADA';
                    $factura->codigo_descripcion = 'VALIDADA';
                    $factura->estado = 'VALIDADA';
                    $factura->save();

                    DB::commit();
                    $validadas++;

                    $this->line("  • [{$i}/{$cantidad}] Factura N° {$numeroFactura} | Bs. ".number_format($precioUnitario, 2)." | SIAT: <info>VALIDADA</info> | Recepción: {$resSiat['codigo_recepcion']}");

                    $facturasEmitidas[] = [
                        'id' => $factura->id,
                        'numero' => $factura->numero_factura,
                        'pv' => $puntoVenta->codigo,
                        'total' => 'Bs. '.number_format($precioUnitario, 2),
                        'cuf' => substr($cuf, 0, 20).'...',
                        'estado' => 'VALIDADA',
                        'recepcion' => $resSiat['codigo_recepcion'],
                    ];
                } else {
                    $factura->codigo_recepcion = $resSiat['codigo_recepcion'] ?? null;
                    $errorCompleto = $resSiat['mensaje'] ?? ($resSiat['codigo_descripcion'] ?? 'OBSERVADA');
                    $factura->codigo_descripcion = mb_substr($errorCompleto, 0, 255);
                    $factura->estado = 'OBSERVADA';
                    $factura->save();

                    DB::commit();
                    $observadas++;

                    $this->line("  • [{$i}/{$cantidad}] Factura N° {$numeroFactura} | Bs. ".number_format($precioUnitario, 2)." | SIAT: <comment>{$errorCompleto}</comment>");

                    $facturasEmitidas[] = [
                        'id' => $factura->id,
                        'numero' => $factura->numero_factura,
                        'pv' => $puntoVenta->codigo,
                        'total' => 'Bs. '.number_format($precioUnitario, 2),
                        'cuf' => substr($cuf, 0, 20).'...',
                        'estado' => 'OBSERVADA',
                        'recepcion' => $factura->codigo_descripcion,
                    ];
                }
            } catch (Exception $e) {
                DB::rollBack();
                $this->error("  ✖ Error al procesar factura {$i}: ".$e->getMessage());
            }

            if (! app()->environment('testing')) {
                usleep(150000); // 150 ms anti-saturación
            }
        }

        $this->line('');
        $this->info('================================================================');
        $this->info("  RESUMEN DE FACTURAS EMITIDAS (PV {$puntoVenta->codigo})");
        $this->info('================================================================');

        $this->table(
            ['ID BD', 'N° Factura', 'Punto Venta', 'Total', 'CUF (Truncado)', 'Estado SIAT', 'Cod. Recepción'],
            $facturasEmitidas
        );

        $this->line('');
        $this->info("✔ Total generadas: {$cantidad} | Validadas por SIAT: {$validadas} | Observadas: {$observadas}");
        $this->line('💡 Todas las facturas generadas están registradas en la base de datos.');
        $this->line('   Puedes ingresar al sistema en <comment>Ventas -> Historial de Ventas</comment> y anularlas directamente desde la columna de <comment>Acciones</comment>.');

        return 0;
    }
}
