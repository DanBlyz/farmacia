<?php

namespace App\Livewire\Ventas;

use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Cufd;
use App\Models\Detalle;
use App\Models\Factura;
use App\Models\Lote;
use App\Models\Movimiento;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Services\SiatFacturacionService;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Pos extends Component
{
    // Datos de la Caja del Usuario
    public ?int $cajaId = null;

    public ?int $sucursalId = null;

    public ?int $puntoVentaId = null;

    // Buscador de Medicamentos
    public string $productoSearch = '';

    public ?int $selectedProductoId = null;

    public ?int $selectedLoteId = null;

    public float|string $cantidadAgregar = 1;

    public float|string $descuentoItemAgregar = 0;

    // Carrito de Ventas
    // Estructura de cada item:
    // ['producto_id', 'lote_id', 'nombre', 'codigo', 'numero_lote', 'fecha_vencimiento', 'precio', 'cantidad', 'descuento', 'total', 'stock_max']
    public array $carrito = [];

    // Descuento General
    public float|string $descuentoGeneral = 0;

    // Buscador y Selección de Cliente
    public string $clienteSearch = '';

    public ?int $clienteId = null;

    public string $clienteNombre = 'Sin Nombre';

    public string $clienteDocumento = '99001';

    public string $clienteRazonSocial = 'S/N';

    // Modal de Registro Rápido de Nuevo Cliente
    public bool $showNuevoClienteModal = false;

    public string $nuevoClienteNombres = '';

    public string $nuevoClienteApellidos = '';

    public string $nuevoClienteDocumento = '';

    public string $nuevoClienteComplemento = '';

    public string $nuevoClienteRazonSocial = '';

    public string $nuevoClienteCelular = '';

    // Métodos de Pago
    public float|string $montoEfectivo = 0;

    public float|string $montoQr = 0;

    public float|string $montoTransferencia = 0;

    // Modal de Impresión / Factura Emitida
    public bool $showTicketModal = false;

    public ?int $facturaEmitidaId = null;

    public function mount(): void
    {
        $this->verificarCaja();
        $this->asignarClienteDefault();
    }

    public function verificarCaja(): void
    {
        $caja = Caja::obtenerCajaAbierta(Auth::id());
        if ($caja) {
            $this->cajaId = $caja->id;
            $this->sucursalId = $caja->sucursal_id;
            $this->puntoVentaId = $caja->punto_venta_id;
        } else {
            $this->cajaId = null;
            $this->sucursalId = null;
            $this->puntoVentaId = null;
        }
    }

    public function asignarClienteDefault(): void
    {
        $clienteDefault = Cliente::find(1);
        if ($clienteDefault) {
            $this->clienteId = $clienteDefault->id;
            $this->clienteNombre = $clienteDefault->nombres ?: 'Sin Nombre';
            $this->clienteDocumento = $clienteDefault->nit ?: ($clienteDefault->cedula ?: '99001');
            $this->clienteRazonSocial = $clienteDefault->razon_social ?: 'S/N';
        } else {
            $this->clienteId = 1;
            $this->clienteNombre = 'Sin Nombre';
            $this->clienteDocumento = '99001';
            $this->clienteRazonSocial = 'S/N';
        }
    }

    public function seleccionarProducto(int $id): void
    {
        $prod = Producto::find($id);
        if (! $prod) {
            return;
        }

        $this->selectedProductoId = $prod->id;
        $this->productoSearch = '';
        $this->cantidadAgregar = 1;
        $this->descuentoItemAgregar = 0;

        // Buscar lotes vigentes de este producto en la sucursal del cajero ordenados por FEFO
        $primerLote = Lote::where('producto_id', $prod->id)
            ->where('sucursal_id', $this->sucursalId)
            ->where('stock_actual', '>', 0)
            ->where('fecha_vencimiento', '>=', now()->toDateString())
            ->whereIn('estado', ['ACTIVO', 'OPTIMO', 'PROXIMO_VENCER'])
            ->orderBy('fecha_vencimiento', 'asc')
            ->first();

        $this->selectedLoteId = $primerLote?->id;
    }

    public function cancelarSeleccionProducto(): void
    {
        $this->selectedProductoId = null;
        $this->selectedLoteId = null;
        $this->cantidadAgregar = 1;
        $this->descuentoItemAgregar = 0;
    }

    public function agregarAlCarrito(): void
    {
        if (! $this->cajaId) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Caja Requerida', message: 'Debe abrir caja antes de agregar productos al carrito.');

            return;
        }

        $this->validate([
            'selectedProductoId' => 'required|exists:productos,id',
            'selectedLoteId' => 'required|exists:lotes,id',
            'cantidadAgregar' => 'required|numeric|min:0.1',
            'descuentoItemAgregar' => 'nullable|numeric|min:0',
        ], [
            'selectedLoteId.required' => 'Debe seleccionar un lote con existencia disponible.',
            'cantidadAgregar.min' => 'La cantidad debe ser mayor a cero.',
        ]);

        $prod = Producto::findOrFail($this->selectedProductoId);
        $lote = Lote::where('id', $this->selectedLoteId)
            ->where('sucursal_id', $this->sucursalId)
            ->firstOrFail();

        $cant = (float) $this->cantidadAgregar;
        $desc = (float) $this->descuentoItemAgregar;
        $precio = (float) ($prod->precio_venta > 0 ? $prod->precio_venta : ($lote->precio_venta > 0 ? $lote->precio_venta : 0));

        if ($desc >= ($precio * $cant)) {
            $this->addError('descuentoItemAgregar', 'El descuento no puede ser igual o mayor al total del ítem.');

            return;
        }

        // Validar si ya está en carrito
        $enCarritoIndex = null;
        foreach ($this->carrito as $index => $item) {
            if ($item['producto_id'] === $prod->id && $item['lote_id'] === $lote->id) {
                $enCarritoIndex = $index;
                break;
            }
        }

        $cantTotalRequerida = $enCarritoIndex !== null ? ($this->carrito[$enCarritoIndex]['cantidad'] + $cant) : $cant;

        if ($cantTotalRequerida > (float) $lote->stock_actual) {
            $this->addError('cantidadAgregar', "Existencia insuficiente. El lote dispone de {$lote->stock_actual} unidades.");

            return;
        }

        $subtotal = max(0, ($precio * $cant) - $desc);

        if ($enCarritoIndex !== null) {
            $nuevaCant = $this->carrito[$enCarritoIndex]['cantidad'] + $cant;
            $nuevoDesc = $this->carrito[$enCarritoIndex]['descuento'] + $desc;
            $this->carrito[$enCarritoIndex]['cantidad'] = $nuevaCant;
            $this->carrito[$enCarritoIndex]['descuento'] = $nuevoDesc;
            $this->carrito[$enCarritoIndex]['total'] = max(0, ($precio * $nuevaCant) - $nuevoDesc);
        } else {
            $this->carrito[] = [
                'producto_id' => $prod->id,
                'lote_id' => $lote->id,
                'nombre' => $prod->nombre,
                'codigo' => $prod->codigo,
                'numero_lote' => $lote->numero_lote,
                'fecha_vencimiento' => Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y'),
                'precio' => $precio,
                'cantidad' => $cant,
                'descuento' => $desc,
                'total' => $subtotal,
                'stock_max' => (float) $lote->stock_actual,
            ];
        }

        $this->cancelarSeleccionProducto();
        $this->actualizarMontosPago();
    }

    public function modificarCantidad(int $index, float $delta): void
    {
        if (! isset($this->carrito[$index])) {
            return;
        }

        $item = &$this->carrito[$index];
        $nuevaCantidad = $item['cantidad'] + $delta;

        if ($nuevaCantidad <= 0) {
            $this->eliminarDelCarrito($index);

            return;
        }

        if ($nuevaCantidad > $item['stock_max']) {
            $this->dispatch('swal-alert', icon: 'warning', title: 'Stock Límite', message: "Solo hay {$item['stock_max']} unidades disponibles en este lote.");

            return;
        }

        $item['cantidad'] = $nuevaCantidad;
        $item['total'] = max(0, ($item['precio'] * $nuevaCantidad) - $item['descuento']);
        $this->actualizarMontosPago();
    }

    public function eliminarDelCarrito(int $index): void
    {
        if (isset($this->carrito[$index])) {
            unset($this->carrito[$index]);
            $this->carrito = array_values($this->carrito);
            $this->actualizarMontosPago();
        }
    }

    public function limpiarCarrito(): void
    {
        $this->carrito = [];
        $this->descuentoGeneral = 0;
        $this->montoEfectivo = 0;
        $this->montoQr = 0;
        $this->montoTransferencia = 0;
        $this->cancelarSeleccionProducto();
    }

    public function updatedDescuentoGeneral(): void
    {
        $this->actualizarMontosPago();
    }

    public function actualizarMontosPago(): void
    {
        $total = $this->calcularTotal();
        // Por defecto, sugerir pago en efectivo por el total
        $this->montoEfectivo = $total;
        $this->montoQr = 0;
        $this->montoTransferencia = 0;
    }

    public function calcularSubtotal(): float
    {
        $sum = 0;
        foreach ($this->carrito as $item) {
            $sum += (float) $item['total'];
        }

        return $sum;
    }

    public function calcularTotal(): float
    {
        $subtotal = $this->calcularSubtotal();
        $descGen = (float) $this->descuentoGeneral;

        return max(0, $subtotal - $descGen);
    }

    public function seleccionarCliente(int $id): void
    {
        $cliente = Cliente::find($id);
        if ($cliente) {
            $this->clienteId = $cliente->id;
            $this->clienteNombre = $cliente->nombres ?: 'Sin Nombre';
            $this->clienteDocumento = $cliente->nit ?: ($cliente->cedula ?: '99001');
            $this->clienteRazonSocial = $cliente->razon_social ?: ($cliente->nombres ?: 'S/N');
            $this->clienteSearch = '';
        }
    }

    public function abrirModalNuevoCliente(): void
    {
        $this->nuevoClienteNombres = '';
        $this->nuevoClienteApellidos = '';
        $this->nuevoClienteDocumento = '';
        $this->nuevoClienteComplemento = '';
        $this->nuevoClienteRazonSocial = '';
        $this->nuevoClienteCelular = '';
        $this->showNuevoClienteModal = true;
    }

    public function guardarNuevoCliente(): void
    {
        $this->validate([
            'nuevoClienteNombres' => 'required|string|min:2|max:100',
            'nuevoClienteDocumento' => 'required|string|min:3|max:50',
            'nuevoClienteRazonSocial' => 'required|string|min:2|max:150',
        ], [
            'nuevoClienteNombres.required' => 'El nombre es obligatorio.',
            'nuevoClienteDocumento.required' => 'El NIT o C.I. es obligatorio.',
            'nuevoClienteRazonSocial.required' => 'La razón social es obligatoria.',
        ]);

        $doc = trim($this->nuevoClienteDocumento);
        $esNit = strlen($doc) >= 8;

        $cliente = Cliente::create([
            'nombres' => trim($this->nuevoClienteNombres),
            'ap_paterno' => trim($this->nuevoClienteApellidos),
            'cedula' => $esNit ? null : $doc,
            'nit' => $doc,
            'complemento' => trim($this->nuevoClienteComplemento) ?: null,
            'razon_social' => strtoupper(trim($this->nuevoClienteRazonSocial)),
            'numero_celular' => trim($this->nuevoClienteCelular) ?: null,
            'estado' => 'ACTIVO',
            'usuario_creador_id' => Auth::id(),
        ]);

        $this->seleccionarCliente($cliente->id);
        $this->showNuevoClienteModal = false;
        $this->dispatch('swal-alert', icon: 'success', title: 'Cliente Registrado', message: 'Cliente registrado y vinculado a la venta actual.');
    }

    /**
     * Procesar Cobro y Emisión de Factura Computarizada en Línea SIAT
     */
    public function procesarVenta(SiatFacturacionService $siatService): void
    {
        abort_if(! Auth::user()?->tienePermiso('ventas.pos.vender'), 403, 'No tiene permiso para procesar ventas.');

        $this->verificarCaja();
        if (! $this->cajaId) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Caja No Aperturada', message: 'Debe aperturar su turno de caja antes de emitir facturas.');

            return;
        }

        if (empty($this->carrito)) {
            $this->dispatch('swal-alert', icon: 'warning', title: 'Carrito Vacío', message: 'Debe agregar al menos un medicamento para cobrar.');

            return;
        }

        $totalAPagar = $this->calcularTotal();
        $pagoEf = (float) $this->montoEfectivo;
        $pagoQr = (float) $this->montoQr;
        $pagoTransf = (float) $this->montoTransferencia;
        $totalPagado = $pagoEf + $pagoQr + $pagoTransf;

        if ($totalPagado < $totalAPagar) {
            $falta = $totalAPagar - $totalPagado;
            $this->dispatch('swal-alert', icon: 'warning', title: 'Pago Insuficiente', message: 'El total pagado no cubre el monto de la compra. Faltan Bs. '.number_format($falta, 2));

            return;
        }

        $cambioEfectivo = max(0, $pagoEf - max(0, $totalAPagar - ($pagoQr + $pagoTransf)));

        // Resolver Punto de Venta y Sucursal
        $caja = Caja::findOrFail($this->cajaId);
        $sucursal = $caja->sucursal ?? Sucursal::first();
        $puntoVenta = $caja->puntoVenta ?? PuntoVenta::where('sucursal_id', $sucursal->id)->first();

        // Obtener CUFD vigente
        $cufd = $siatService->obtenerCufdVigente($sucursal->id, $puntoVenta->id);
        if (! $cufd) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Sin CUFD Vigente', message: 'No se pudo obtener un CUFD vigente para el punto de venta.');

            return;
        }

        // Obtener Correlativo y Parámetros para CUF
        $numeroFactura = $siatService->obtenerSiguienteNumeroFactura($sucursal->id, $puntoVenta->id);
        $fechaActual = now();
        $fechaHoraCadena = $fechaActual->format('YmdHis').'000';
        $nitEmisor = config('siat.nit', '698610039');
        $codigoModalidad = (int) config('siat.codigo_modalidad', 2);

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

        // Determinar Método de Pago principal para cabecera fiscal SIAT (1 = EFECTIVO, 2 = TARJETA, 7 = TRANSFERENCIA/QR)
        $metodoPagoSiat = 1;
        if ($pagoQr > 0 && $pagoEf == 0) {
            $metodoPagoSiat = 7;
        } elseif ($pagoTransf > 0 && $pagoEf == 0) {
            $metodoPagoSiat = 7;
        }

        DB::beginTransaction();
        try {
            // 1. Crear Registro de Factura
            $factura = Factura::create([
                'usuario_creador_id' => Auth::id(),
                'cufd_id' => $cufd->id,
                'sucursal_id' => $sucursal->id,
                'caja_id' => $caja->id,
                'cliente_id' => $this->clienteId ?: 1,
                'punto_venta_id' => $puntoVenta->id,
                'fecha' => $fechaActual,
                'nit' => $this->clienteDocumento,
                'razon_social' => $this->clienteRazonSocial,
                'numero_factura' => (string) $numeroFactura,
                'facturado' => 'SI',
                'codigo_metodo_pago' => $metodoPagoSiat,
                'total' => $totalAPagar,
                'monto_total_subjeto_iva' => $totalAPagar,
                'descuento_adicional' => (float) $this->descuentoGeneral,
                'cuf' => $cuf,
                'tipo_factura' => '1',
                'estado_pago' => 'PAGADO',
                'estado' => 'EMITIDA',
            ]);

            $detallesCreados = [];

            // 2. Descontar Stock FEFO y Registrar Detalles + Movimientos en Kardex
            foreach ($this->carrito as $item) {
                $lote = Lote::where('id', $item['lote_id'])->lockForUpdate()->firstOrFail();
                $cantPedida = (float) $item['cantidad'];

                if ($cantPedida > (float) $lote->stock_actual) {
                    throw new Exception("Existencia insuficiente durante el cobro en el lote {$lote->numero_lote}. Stock disponible: {$lote->stock_actual}");
                }

                $nuevoStock = (float) $lote->stock_actual - $cantPedida;
                $lote->stock_actual = $nuevoStock;
                if ($nuevoStock <= 0) {
                    $lote->estado = 'AGOTADO';
                }
                $lote->usuario_modificador_id = Auth::id();
                $lote->save();

                // Detalle de Factura
                $detalle = Detalle::create([
                    'usuario_creador_id' => Auth::id(),
                    'factura_id' => $factura->id,
                    'producto_id' => $item['producto_id'],
                    'lote_id' => $lote->id,
                    'sucursal_id' => $sucursal->id,
                    'punto_venta_id' => $puntoVenta->id,
                    'nombre_producto' => $item['nombre'],
                    'numero_lote' => $lote->numero_lote,
                    'fecha_vencimiento' => $lote->fecha_vencimiento,
                    'precio' => $item['precio'],
                    'cantidad' => $cantPedida,
                    'descuento' => $item['descuento'],
                    'total' => $item['total'],
                    'importe' => $item['total'],
                    'fecha' => $fechaActual,
                    'estado' => 'ACTIVO',
                ]);
                $detallesCreados[] = $detalle;

                // Movimiento en Kardex
                Movimiento::create([
                    'producto_id' => $item['producto_id'],
                    'sucursal_id' => $sucursal->id,
                    'lote_id' => $lote->id,
                    'tipo_movimiento' => 'VENTA',
                    'ingreso' => 0,
                    'salida' => $cantPedida,
                    'saldo_stock' => $nuevoStock,
                    'numero_lote' => $lote->numero_lote,
                    'fecha_caducidad' => $lote->fecha_vencimiento,
                    'fecha' => $fechaActual,
                    'descripcion' => "Venta en mostrador según Factura N° {$numeroFactura}",
                    'precio_compra' => (float) $lote->precio_compra,
                    'precio_venta' => (float) $item['precio'],
                    'estado' => 'ACTIVO',
                    'usuario_creador_id' => Auth::id(),
                ]);
            }

            // 3. Registrar Pagos
            if ($pagoEf > 0) {
                Pago::create([
                    'usuario_creador_id' => Auth::id(),
                    'factura_id' => $factura->id,
                    'punto_venta_id' => $puntoVenta->id,
                    'caja_id' => $caja->id,
                    'tipo_pago' => 'EFECTIVO',
                    'monto' => $pagoEf,
                    'cambio' => $cambioEfectivo,
                    'fecha' => $fechaActual,
                    'descripcion' => "Pago en efectivo Factura #{$numeroFactura}",
                    'estado' => 'ACTIVO',
                ]);
            }

            if ($pagoQr > 0) {
                Pago::create([
                    'usuario_creador_id' => Auth::id(),
                    'factura_id' => $factura->id,
                    'punto_venta_id' => $puntoVenta->id,
                    'caja_id' => $caja->id,
                    'tipo_pago' => 'QR',
                    'monto' => $pagoQr,
                    'cambio' => 0,
                    'fecha' => $fechaActual,
                    'descripcion' => "Pago con código QR Factura #{$numeroFactura}",
                    'estado' => 'ACTIVO',
                ]);
            }

            if ($pagoTransf > 0) {
                Pago::create([
                    'usuario_creador_id' => Auth::id(),
                    'factura_id' => $factura->id,
                    'punto_venta_id' => $puntoVenta->id,
                    'caja_id' => $caja->id,
                    'tipo_pago' => 'TRANSFERENCIA',
                    'monto' => $pagoTransf,
                    'cambio' => 0,
                    'fecha' => $fechaActual,
                    'descripcion' => "Pago por transferencia Factura #{$numeroFactura}",
                    'estado' => 'ACTIVO',
                ]);
            }

            // 4. Actualizar Acumuladores de Caja
            $efectivoRealIngresado = max(0, $pagoEf - $cambioEfectivo);
            $caja->total_venta = (float) $caja->total_venta + $totalAPagar;
            $caja->venta_contado = (float) $caja->venta_contado + $efectivoRealIngresado;
            $caja->total_qr = (float) $caja->total_qr + $pagoQr;
            $caja->total_transferencia = (float) $caja->total_transferencia + $pagoTransf;
            $caja->saldo = (float) $caja->monto_apertura + (float) $caja->venta_contado + (float) $caja->otro_ingreso - (float) $caja->total_salida;
            $caja->usuario_modificador_id = Auth::id();
            $caja->save();

            // 5. Generar XML y Enviar a SIAT
            $xml = $siatService->generarXmlFactura($factura, $detallesCreados, $cufd, $sucursal, $puntoVenta);
            $factura->productos_xml = $xml;

            // Intentar envío SOAP a SIAT en tiempo real
            $resSiat = $siatService->enviarFacturaSiat($factura, $xml);
            if ($resSiat['success']) {
                $factura->codigo_recepcion = $resSiat['codigo_recepcion'];
                $factura->codigo_transaccion = 'VALIDADA';
                $factura->codigo_descripcion = 'VALIDADA';
                $factura->estado = 'VALIDADA';
            } else {
                $factura->codigo_recepcion = $resSiat['codigo_recepcion'] ?? null;
                $factura->codigo_descripcion = $resSiat['mensaje'] ?? 'PENDIENTE';
                $factura->estado = $resSiat['en_contingencia'] ? 'PENDIENTE_ENVIO' : 'OBSERVADA';
            }
            $factura->save();

            DB::commit();

            // Desplegar Ticket y Limpiar Carrito
            $this->facturaEmitidaId = $factura->id;
            $this->showTicketModal = true;
            $this->limpiarCarrito();

            $this->dispatch('abrir-ticket-flotante', url: route('ventas.factura.imprimir', $factura->id));

            $msg = $factura->estado === 'VALIDADA'
                ? "¡Factura N° {$numeroFactura} emitida y validada exitosamente ante el SIAT!"
                : "¡Venta completada! Factura registrada en contingencia ({$factura->estado}).";

            $this->dispatch('swal-alert', icon: 'success', title: '¡Venta Realizada!', message: $msg);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar venta POS: '.$e->getMessage());
            $this->dispatch('swal-alert', icon: 'error', title: 'Error al Facturar', message: $e->getMessage());
        }
    }

    public function render(): View
    {
        $caja = Caja::obtenerCajaAbierta(Auth::id());

        // Búsqueda de Productos con stock disponible en la sucursal del cajero
        $productosEncontrados = [];
        if (strlen(trim($this->productoSearch)) >= 2 && $this->sucursalId) {
            $productosEncontrados = Producto::with(['lotes' => function ($q) {
                $q->where('sucursal_id', $this->sucursalId)
                    ->where('stock_actual', '>', 0)
                    ->where('fecha_vencimiento', '>=', now()->toDateString())
                    ->whereIn('estado', ['ACTIVO', 'OPTIMO', 'PROXIMO_VENCER'])
                    ->orderBy('fecha_vencimiento', 'asc');
            }])
                ->where(function ($q) {
                    $q->where('nombre', 'like', "%{$this->productoSearch}%")
                        ->orWhere('codigo', 'like', "%{$this->productoSearch}%")
                        ->orWhere('principio_activo', 'like', "%{$this->productoSearch}%");
                })
                ->where('estado', 'ACTIVO')
                ->take(8)
                ->get();
        }

        // Lotes del producto actualmente seleccionado
        $lotesProducto = [];
        if ($this->selectedProductoId && $this->sucursalId) {
            $lotesProducto = Lote::where('producto_id', $this->selectedProductoId)
                ->where('sucursal_id', $this->sucursalId)
                ->where('stock_actual', '>', 0)
                ->where('fecha_vencimiento', '>=', now()->toDateString())
                ->whereIn('estado', ['ACTIVO', 'OPTIMO', 'PROXIMO_VENCER'])
                ->orderBy('fecha_vencimiento', 'asc')
                ->get();
        }

        // Búsqueda de Clientes
        $clientesEncontrados = [];
        if (strlen(trim($this->clienteSearch)) >= 2) {
            $clientesEncontrados = Cliente::where(function ($q) {
                $q->where('nombres', 'like', "%{$this->clienteSearch}%")
                    ->orWhere('ap_paterno', 'like', "%{$this->clienteSearch}%")
                    ->orWhere('nit', 'like', "%{$this->clienteSearch}%")
                    ->orWhere('cedula', 'like', "%{$this->clienteSearch}%")
                    ->orWhere('razon_social', 'like', "%{$this->clienteSearch}%");
            })
                ->where('estado', 'ACTIVO')
                ->take(6)
                ->get();
        }

        $facturaReciente = $this->facturaEmitidaId ? Factura::with(['detalles', 'pagos', 'cliente', 'sucursal'])->find($this->facturaEmitidaId) : null;

        return view('livewire.ventas.pos', [
            'caja' => $caja,
            'productosEncontrados' => $productosEncontrados,
            'lotesProducto' => $lotesProducto,
            'clientesEncontrados' => $clientesEncontrados,
            'facturaReciente' => $facturaReciente,
            'subtotalCarrito' => $this->calcularSubtotal(),
            'totalCarrito' => $this->calcularTotal(),
        ]);
    }
}
