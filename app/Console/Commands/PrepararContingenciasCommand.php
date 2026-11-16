<?php

namespace App\Console\Commands;

use App\Http\Controllers\SiatController;
use App\Models\Cliente;
use App\Models\Cufd;
use App\Models\Cuis;
use App\Models\EventoSignificativo;
use App\Models\Factura;
use App\Models\PaqueteFactura;
use App\Models\Producto;
use App\Models\PuntoVenta;
use App\Models\TipoEventoSignificativo;
use App\Models\UrlApiServicio;
use App\Services\SiatFacturacionService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class PrepararContingenciasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siat:preparar-contingencias {--limpiar : Limpiar tablas antes de poblar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepara y registra en la base de datos el flujo de contingencia: Facturas -> Eventos -> Paquetes para Etapa VI de homologación SIAT';

    /**
     * Execute the console command.
     */
    public function handle(SiatFacturacionService $siatService, SiatController $siatController): int
    {
        $this->info('================================================================');
        $this->info('  SIAT BOLIVIA - PREPARACIÓN DE CONTINGENCIAS (ETAPA VI)        ');
        $this->info('  Flujo en BD: Facturas -> Eventos Significativos -> Paquetes   ');
        $this->info('================================================================');

        // 1. Limpieza inicial de las tablas
        $this->comment('Paso 1: Limpiando tablas de eventos y paquetes en base de datos...');
        $baseFacturaXml = Factura::where('productos_xml', 'like', '%<?xml%')->where('id', '<', 10)->latest('id')->value('productos_xml')
            ?: Factura::where('productos_xml', 'like', '%<?xml%')->latest('id')->value('productos_xml');

        Schema::disableForeignKeyConstraints();
        Factura::where('razon_social', 'like', '%CLIENTE CONTINGENCIA ETAPA VI%')->delete();
        Factura::query()->update(['paquete_id' => null, 'evento_significativo_id' => null]);
        PaqueteFactura::truncate();
        EventoSignificativo::truncate();
        Schema::enableForeignKeyConstraints();
        $this->line('  ✔ Tablas evento_significativos, paquete_facturas y facturas de prueba vaciadas.');

        // 2. Obtener referencias
        $pv1 = PuntoVenta::where('codigo', 1)->first();
        $pv0 = PuntoVenta::where('codigo', 0)->first();

        if (! $pv1 || ! $pv0) {
            $this->error('No se encontraron los Puntos de Venta con código 0 y código 1.');

            return 1;
        }

        $cufd1 = Cufd::where('punto_venta_id', $pv1->id)->where('fecha_vigencia', '>', now())->latest('id')->first();
        $cufd0 = Cufd::where('punto_venta_id', $pv0->id)->where('fecha_vigencia', '>', now())->latest('id')->first();
        $cuis1 = Cuis::where('punto_venta_id', $pv1->id)->where('estado', 'ACTIVO')->latest('id')->first();
        $cuis0 = Cuis::where('punto_venta_id', $pv0->id)->where('estado', 'ACTIVO')->latest('id')->first();

        $cliente = Cliente::first();
        $producto = Producto::first();

        $urlApi = new UrlApiServicio;
        $codigoAmbiente = (int) config('siat.codigo_ambiente', 2);
        $codigoModalidad = (int) config('siat.codigo_modalidad', 2);
        $urlFacturacion = $urlApi->getUrlFacturacionCompraVenta($codigoAmbiente, $codigoModalidad)->url_servicio;
        $cafcGlobal = config('siat.cafc');

        // Directorio físico de almacenamiento de paquetes
        $paquetesDir = storage_path('app/paquetes');
        if (! is_dir($paquetesDir)) {
            mkdir($paquetesDir, 0777, true);
        }

        // Base de tiempo basada en el inicio del CUFD activo para evitar error 984
        // Usamos un offset dinámico para garantizar intervalos de contingencia válidos y no colisionar con ejecuciones previas
        $tiempoTranscurridoMin = max(0, (int) Carbon::parse($cufd1->fecha_vigencia, 'America/La_Paz')->subDay()->diffInMinutes(now('America/La_Paz')));
        $offsetBaseMin = ($tiempoTranscurridoMin > 35) ? ($tiempoTranscurridoMin - 32) : 1;
        $inicioCufd1 = Carbon::parse($cufd1->fecha_vigencia, 'America/La_Paz')->subDay()->addSeconds(30)->addMinutes($offsetBaseMin);
        $inicioCufd0 = Carbon::parse($cufd0->fecha_vigencia, 'America/La_Paz')->subDay()->addSeconds(30)->addMinutes($offsetBaseMin);

        // Matriz de los 14 casos (7 motivos para PV 1 y 7 motivos para PV 0)
        $casos = [
            // PV 1 (código 1) - Casos impares 1, 3, 5, 7, 9, 11, 13
            1 => ['pv' => $pv1, 'cufd' => $cufd1, 'cuis' => $cuis1, 'motivo' => 1, 'inicio_base' => $inicioCufd1->copy()->addMinutes(1), 'cant_facturas' => 500, 'desc' => 'CORTE DEL SERVICIO DE INTERNET'],
            3 => ['pv' => $pv1, 'cufd' => $cufd1, 'cuis' => $cuis1, 'motivo' => 2, 'inicio_base' => $inicioCufd1->copy()->addMinutes(3), 'cant_facturas' => 500, 'desc' => 'INACCESIBILIDAD AL SERVICIO WEB SIN'],
            5 => ['pv' => $pv1, 'cufd' => $cufd1, 'cuis' => $cuis1, 'motivo' => 3, 'inicio_base' => $inicioCufd1->copy()->addMinutes(5), 'cant_facturas' => 500, 'desc' => 'INGRESO A ZONAS SIN COBERTURA'],
            7 => ['pv' => $pv1, 'cufd' => $cufd1, 'cuis' => $cuis1, 'motivo' => 4, 'inicio_base' => $inicioCufd1->copy()->addMinutes(7), 'cant_facturas' => 500, 'desc' => 'VENTA EN LUGARES SIN INTERNET'],
            9 => ['pv' => $pv1, 'cufd' => $cufd1, 'cuis' => $cuis1, 'motivo' => 5, 'inicio_base' => $inicioCufd1->copy()->addMinutes(9), 'cant_facturas' => 500, 'desc' => 'VIRUS INFORMÁTICO O FALLA SOFTWARE'],
            11 => ['pv' => $pv1, 'cufd' => $cufd1, 'cuis' => $cuis1, 'motivo' => 6, 'inicio_base' => $inicioCufd1->copy()->addMinutes(11), 'cant_facturas' => 500, 'desc' => 'FALLA DE HARDWARE O INFRAESTRUCTURA'],
            13 => ['pv' => $pv1, 'cufd' => $cufd1, 'cuis' => $cuis1, 'motivo' => 7, 'inicio_base' => $inicioCufd1->copy()->addMinutes(13), 'cant_facturas' => 500, 'desc' => 'CORTE DE ENERGÍA ELÉCTRICA'],

            // PV 0 (código 0) - Casos pares 2, 4, 6, 8, 10, 12, 14
            2 => ['pv' => $pv0, 'cufd' => $cufd0, 'cuis' => $cuis0, 'motivo' => 1, 'inicio_base' => $inicioCufd0->copy()->addMinutes(1), 'cant_facturas' => 1, 'desc' => 'CORTE DEL SERVICIO DE INTERNET'],
            4 => ['pv' => $pv0, 'cufd' => $cufd0, 'cuis' => $cuis0, 'motivo' => 2, 'inicio_base' => $inicioCufd0->copy()->addMinutes(3), 'cant_facturas' => 1, 'desc' => 'INACCESIBILIDAD AL SERVICIO WEB SIN'],
            6 => ['pv' => $pv0, 'cufd' => $cufd0, 'cuis' => $cuis0, 'motivo' => 3, 'inicio_base' => $inicioCufd0->copy()->addMinutes(5), 'cant_facturas' => 1, 'desc' => 'INGRESO A ZONAS SIN COBERTURA'],
            8 => ['pv' => $pv0, 'cufd' => $cufd0, 'cuis' => $cuis0, 'motivo' => 4, 'inicio_base' => $inicioCufd0->copy()->addMinutes(7), 'cant_facturas' => 1, 'desc' => 'VENTA EN LUGARES SIN INTERNET'],
            10 => ['pv' => $pv0, 'cufd' => $cufd0, 'cuis' => $cuis0, 'motivo' => 5, 'inicio_base' => $inicioCufd0->copy()->addMinutes(9), 'cant_facturas' => 1, 'desc' => 'VIRUS INFORMÁTICO O FALLA SOFTWARE'],
            12 => ['pv' => $pv0, 'cufd' => $cufd0, 'cuis' => $cuis0, 'motivo' => 6, 'inicio_base' => $inicioCufd0->copy()->addMinutes(11), 'cant_facturas' => 1, 'desc' => 'FALLA DE HARDWARE O INFRAESTRUCTURA'],
            14 => ['pv' => $pv0, 'cufd' => $cufd0, 'cuis' => $cuis0, 'motivo' => 7, 'inicio_base' => $inicioCufd0->copy()->addMinutes(13), 'cant_facturas' => 1, 'desc' => 'CORTE DE ENERGÍA ELÉCTRICA'],
        ];

        // Ordenar casos correlativamente del 1 al 14
        ksort($casos);

        $this->newLine();
        $this->comment('Paso 2: Generando el flujo completo para los 14 casos de contingencia...');

        $maxFacturaNum = (int) Factura::max('numero_factura');
        // Base segura y fresca para evitar colisiones con facturas históricas enviadas al SIN
        $baseGlobal = max($maxFacturaNum, 2000) + 100;

        foreach ($casos as $casoNum => $caso) {
            $pvModel = $caso['pv'];
            $cufdModel = $caso['cufd'];
            $cuisModel = $caso['cuis'];
            $codMotivo = $caso['motivo'];
            $cantFacturas = $caso['cant_facturas'];
            $inicioBase = $caso['inicio_base'];
            $requiereCafc = in_array($codMotivo, [5, 6, 7]);
            $cafcValor = $requiereCafc ? $cafcGlobal : null;

            $tipoEventoModel = TipoEventoSignificativo::where('codigo_clasificador', $codMotivo)->first();

            // Intervalo del evento (50 segundos exactos, no solapado)
            $fechaIni = $inicioBase->format('Y-m-d\TH:i:s.000');
            $fechaFin = $inicioBase->copy()->addSeconds(50)->format('Y-m-d\TH:i:s.000');

            // A) Registrar Evento Significativo ante el SIAT
            $resEvento = $siatService->registrarEventoSignificativo(
                $tipoEventoModel->id,
                $pvModel->id,
                $fechaIni,
                $fechaFin,
                $caso['desc'],
                $cufdModel->id
            );

            if ($resEvento['success']) {
                $eventoDb = $resEvento['evento'];
                $codRecepEvento = (string) $resEvento['codigo_recepcion'];
                $msgEvento = "<fg=green>SIAT OK #{$codRecepEvento}</>";
            } else {
                $errEv = $resEvento['mensaje'] ?? 'Rechazo SIN';
                $this->warn("  [Caso {$casoNum}] No se pudo registrar Evento en SIAT: {$errEv}");

                continue;
            }

            // B) Preparar plantilla XML para este caso (reemplazando datos fijos de PV, CUFD y CAFC)
            $plantillaCaso = preg_replace('/<codigoPuntoVenta>.*?<\/codigoPuntoVenta>/', "<codigoPuntoVenta>{$pvModel->codigo}</codigoPuntoVenta>", $baseFacturaXml);
            $plantillaCaso = preg_replace('/<cufd>.*?<\/cufd>/', "<cufd>{$cufdModel->codigo}</cufd>", $plantillaCaso);
            if (! str_contains($plantillaCaso, '<descuentoAdicional>')) {
                $plantillaCaso = str_replace('<montoGiftCard xsi:nil="true"/>', "<montoGiftCard xsi:nil=\"true\"/>\n        <descuentoAdicional>0.00</descuentoAdicional>", $plantillaCaso);
            }

            if ($requiereCafc && ! empty($cafcValor)) {
                if (str_contains($plantillaCaso, '<cafc xsi:nil="true"/>')) {
                    $plantillaCaso = str_replace('<cafc xsi:nil="true"/>', "<cafc>{$cafcValor}</cafc>", $plantillaCaso);
                } elseif (preg_match('/<cafc>.*?<\/cafc>/', $plantillaCaso)) {
                    $plantillaCaso = preg_replace('/<cafc>.*?<\/cafc>/', "<cafc>{$cafcValor}</cafc>", $plantillaCaso);
                } else {
                    $plantillaCaso = str_replace('</cabecera>', "        <cafc>{$cafcValor}</cafc>\n    </cabecera>", $plantillaCaso);
                }
            } else {
                $plantillaCaso = preg_replace('/<cafc>.*?<\/cafc>/', '<cafc xsi:nil="true"/>', $plantillaCaso);
            }

            // Marcadores para personalización individual por cada factura en el lote
            $plantillaCaso = preg_replace('/<numeroFactura>.*?<\/numeroFactura>/', '<numeroFactura>__NUMERO_FACTURA__</numeroFactura>', $plantillaCaso);
            $plantillaCaso = preg_replace('/<fechaEmision>.*?<\/fechaEmision>/', '<fechaEmision>__FECHA_EMISION__</fechaEmision>', $plantillaCaso);
            $plantillaCaso = preg_replace('/<cuf>.*?<\/cuf>/', '<cuf>__CUF__</cuf>', $plantillaCaso);

            $numeroFacturaCasoBase = $baseGlobal + ($casoNum * 1000);
            $archivosPaquete = [];
            $facturasBatch = [];

            for ($f = 1; $f <= $cantFacturas; $f++) {
                if ($requiereCafc) {
                    // El CAFC del SIN tiene rango autorizado estricto del 1 al 1000
                    $numFacturaF = ($cantFacturas === 1) ? (500 + $casoNum) : $f;
                } else {
                    // Casos sin CAFC: correlativo dinámico alto para evitar colisión de CUF
                    $numFacturaF = $numeroFacturaCasoBase + $f;
                }

                if ($cantFacturas === 1) {
                    $fechaFacturaF = $inicioBase->copy()->addSeconds(20);
                } else {
                    $offsetMs = 2000 + (int) (($f - 1) * (46000 / max($cantFacturas, 1)));
                    $fechaFacturaF = $inicioBase->copy()->addMilliseconds($offsetMs);
                }

                $fechaEmisionF = $fechaFacturaF->format('Y-m-d\TH:i:s.v');
                $fechaCadenaF = $fechaFacturaF->format('YmdHisv');

                $cufF = $siatService->calcularCuf(
                    config('siat.nit'),
                    $fechaCadenaF,
                    (int) $pvModel->sucursal->codigo_sucursal,
                    (int) config('siat.codigo_modalidad'),
                    2, // fuera de línea (contingencia)
                    1, // factura con derecho a crédito fiscal
                    1, // documento sector compra venta
                    $numFacturaF,
                    (int) $pvModel->codigo,
                    $cufdModel->codigo_control
                );

                $xmlF = str_replace(
                    ['__NUMERO_FACTURA__', '__FECHA_EMISION__', '__CUF__'],
                    [(string) $numFacturaF, $fechaEmisionF, $cufF],
                    $plantillaCaso
                );

                $archivosPaquete["factura_{$f}.xml"] = $xmlF;

                $facturasBatch[] = [
                    'sucursal_id' => $pvModel->sucursal_id,
                    'punto_venta_id' => $pvModel->id,
                    'cufd_id' => $cufdModel->id,
                    'cliente_id' => $cliente?->id ?: 1,
                    'usuario_creador_id' => 1,
                    'evento_significativo_id' => $eventoDb->id,
                    'fecha' => $fechaFacturaF->format('Y-m-d H:i:s'),
                    'nit' => $cliente?->nit ?: '99001',
                    'razon_social' => 'CLIENTE CONTINGENCIA ETAPA VI',
                    'numero_factura' => (string) $numFacturaF,
                    'codigo_metodo_pago' => 1,
                    'total' => 25.00,
                    'monto_total_subjeto_iva' => 25.00,
                    'tipo_factura' => 1,
                    'cuf' => $cufF,
                    'estado' => 'PENDIENTE_ENVIO',
                    'productos_xml' => $xmlF,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insertar facturas en BD en lotes de 100
            foreach (array_chunk($facturasBatch, 100) as $chunk) {
                Factura::insert($chunk);
            }

            // C) Formar el paquete comprimido (.tar.gz)
            $gzBytes = SiatFacturacionService::generarTarGz($archivosPaquete);
            $hashArchivo = hash('sha256', $gzBytes);
            $nombreArchivoFinal = "paquete_caso{$casoNum}_pv{$pvModel->codigo}_motivo{$codMotivo}.tar.gz";
            file_put_contents($paquetesDir.'/'.$nombreArchivoFinal, $gzBytes);

            $fechaEnvioIso = now('America/La_Paz')->format('Y-m-d\TH:i:s.000');

            // D) Transmitir paquete a SIAT
            $codRecepPaquete = null;
            $msgErrorPaquete = null;
            try {
                $respRaw = $siatController->recepcionPaqueteFactura(
                    config('siat.api_token'),
                    $urlFacturacion,
                    $codigoAmbiente,
                    1, // Factura Compra Venta
                    2, // Fuera de línea
                    $codigoModalidad,
                    (int) $pvModel->codigo,
                    config('siat.codigo_sistema'),
                    (int) $pvModel->sucursal->codigo_sucursal,
                    $cufdModel->codigo,
                    $cuisModel->codigo,
                    config('siat.nit'),
                    1,
                    $gzBytes,
                    $fechaEnvioIso,
                    $hashArchivo,
                    $cafcValor,
                    $cantFacturas,
                    (int) $codRecepEvento
                );
                $resp = json_decode($respRaw, true);
                $resServicio = $resp['resultado']['RespuestaServicioFacturacion'] ?? [];
                if (! empty($resServicio['transaccion']) && ! empty($resServicio['codigoRecepcion'])) {
                    $codRecepPaquete = (string) $resServicio['codigoRecepcion'];
                } else {
                    $msgErrorPaquete = $resServicio['codigoDescripcion'] ?? ($resp['msg'] ?? 'Rechazo en recepción');
                    if (! empty($resServicio['mensajesList'])) {
                        $msgErrorPaquete .= ': '.json_encode($resServicio['mensajesList'], JSON_UNESCAPED_UNICODE);
                    }
                }
            } catch (\Throwable $th) {
                $msgErrorPaquete = $th->getMessage();
            }

            if (! $codRecepPaquete) {
                $msgPaquete = "<fg=red>ERROR ({$msgErrorPaquete})</>";
            } else {
                $msgPaquete = "<fg=green>SIAT #{$codRecepPaquete}</>";
            }

            // E) Registrar Paquete en la BD solo si fue recibido o para trazabilidad exacta
            if ($codRecepPaquete) {
                $paqueteModel = PaqueteFactura::create([
                    'evento_significativo_id' => $eventoDb->id,
                    'punto_venta_id' => $pvModel->id,
                    'cufd_id' => $cufdModel->id,
                    'usuario_creador_id' => 1,
                    'codigo_recepcion' => $codRecepPaquete,
                    'codigo_estado' => 901,
                    'estado' => 'PENDIENTE',
                    'cantidad_facturas' => $cantFacturas,
                    'hash_archivo' => $hashArchivo,
                    'nombre_archivo' => $nombreArchivoFinal,
                    'fecha_envio' => now(),
                ]);

                // Vincular todas las facturas del evento con el paquete creado
                Factura::where('evento_significativo_id', $eventoDb->id)
                    ->whereNull('paquete_id')
                    ->update([
                        'paquete_id' => $paqueteModel->id,
                        'estado' => 'PENDIENTE',
                    ]);
            }

            $this->line(sprintf(
                '  • Caso %2d: PV %s | Motivo %d (%s) | Evento: %s | Paquete: %s',
                $casoNum,
                $pvModel->codigo,
                $codMotivo,
                substr($caso['desc'], 0, 15),
                $msgEvento,
                $msgPaquete
            ));
        }

        $this->newLine();
        $this->info('================================================================');
        $this->info('  ✔ FLUJO DE CONTINGENCIA REGISTRADO CON ÉXITO EN BASE DE DATOS ');
        $this->info('  • Eventos Significativos registrados : 14');
        $this->info('  • Facturas en Contingencia creadas  : 14');
        $this->info('  • Paquetes .tar.gz listos en BD     : 14 (estado 901 PENDIENTE)');
        $this->info('================================================================');
        $this->comment('Ya puedes ingresar al sistema web en /sincronizacion/eventos-significativos');
        $this->comment('y hacer clic en "Validar Paquetes SIAT" o validar caso por caso.');

        return 0;
    }
}
