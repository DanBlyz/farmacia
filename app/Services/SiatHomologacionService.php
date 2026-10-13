<?php

namespace App\Services;

use App\Http\Controllers\SiatController;
use App\Models\Cufd;
use App\Models\Cuis;
use App\Models\Detalle;
use App\Models\EventoSignificativo;
use App\Models\Factura;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Models\TipoEventoSignificativo;
use App\Models\UrlApiServicio;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;

class SiatHomologacionService
{
    public function __construct(
        public SiatController $siatController = new SiatController,
    ) {}

    /**
     * Los 18 métodos SOAP oficiales de sincronización de catálogos evaluados por el SIN
     *
     * @return array<int, array{metodo: string, nombre: string}>
     */
    public function metodosCatalogos(): array
    {
        return [
            ['metodo' => 'sincronizarActividades', 'nombre' => 'Actividades Económicas'],
            ['metodo' => 'sincronizarFechaHora', 'nombre' => 'Fecha y Hora Oficial'],
            ['metodo' => 'sincronizarListaActividadesDocumentoSector', 'nombre' => 'Actividades Documento Sector'],
            ['metodo' => 'sincronizarListaLeyendasFactura', 'nombre' => 'Leyendas de Factura'],
            ['metodo' => 'sincronizarListaMensajesServicios', 'nombre' => 'Mensajes de Servicios'],
            ['metodo' => 'sincronizarListaProductosServicios', 'nombre' => 'Productos y Servicios SIN'],
            ['metodo' => 'sincronizarParametricaEventosSignificativos', 'nombre' => 'Eventos Significativos'],
            ['metodo' => 'sincronizarParametricaMotivoAnulacion', 'nombre' => 'Motivos de Anulación'],
            ['metodo' => 'sincronizarParametricaPaisOrigen', 'nombre' => 'Países de Origen'],
            ['metodo' => 'sincronizarParametricaTipoDocumentoIdentidad', 'nombre' => 'Documentos de Identidad'],
            ['metodo' => 'sincronizarParametricaTipoDocumentoSector', 'nombre' => 'Documentos Sector'],
            ['metodo' => 'sincronizarParametricaTipoEmision', 'nombre' => 'Tipos de Emisión'],
            ['metodo' => 'sincronizarParametricaTipoHabitacion', 'nombre' => 'Tipos de Habitación'],
            ['metodo' => 'sincronizarParametricaTipoMetodoPago', 'nombre' => 'Métodos de Pago'],
            ['metodo' => 'sincronizarParametricaTipoMoneda', 'nombre' => 'Tipos de Moneda'],
            ['metodo' => 'sincronizarParametricaTipoPuntoVenta', 'nombre' => 'Tipos de Punto de Venta'],
            ['metodo' => 'sincronizarParametricaTiposFactura', 'nombre' => 'Tipos de Factura'],
            ['metodo' => 'sincronizarParametricaUnidadMedida', 'nombre' => 'Unidades de Medida'],
        ];
    }

    /**
     * Resuelve el contexto SOAP para un punto de venta determinado
     *
     * @return array{success: bool, message?: string, token?: string, urlSinc?: string, urlCodigos?: string, ambiente?: int, modalidad?: int, codigoPuntoVenta?: int, puntoVentaId?: int, sucursalId?: int, codigoSistema?: string, codigoSucursal?: int, cuis?: string, nit?: string}
     */
    public function resolverContexto(int $codigoPuntoVenta = 0): array
    {
        $ambiente = (int) config('siat.codigo_ambiente', env('CODIGO_AMBIENTE', 2));
        $modalidad = (int) config('siat.codigo_modalidad', env('CODIGO_MODALIDAD', 2));
        $token = config('siat.api_token', env('API_TOKEN'));
        $codigoSistema = config('siat.codigo_sistema', env('CODIGO_SISTEMA'));
        $nit = (string) config('siat.nit', env('NIT'));

        $sucursal = Sucursal::orderBy('codigo_sucursal', 'asc')->first();
        if (! $sucursal) {
            return ['success' => false, 'message' => 'No se encontró sucursal activa en la base de datos.'];
        }

        $puntoVenta = PuntoVenta::where('sucursal_id', $sucursal->id)
            ->where('codigo', $codigoPuntoVenta)
            ->first();

        if (! $puntoVenta) {
            return ['success' => false, 'message' => "No existe Punto de Venta con código {$codigoPuntoVenta} en la sucursal {$sucursal->nombre}."];
        }

        $cuisModel = (new Cuis)->cuisVigente($sucursal->id, $puntoVenta->id, $ambiente);
        if (! $cuisModel || Carbon::parse($cuisModel->fecha_vigencia)->lessThan(now())) {
            return ['success' => false, 'message' => "El Punto de Venta {$codigoPuntoVenta} no tiene un CUIS vigente."];
        }

        $urlSinc = (new UrlApiServicio)->getUrlSincronizacion($ambiente, $modalidad);
        $urlCodigos = (new UrlApiServicio)->getUrlCodigos($ambiente, $modalidad);

        if (! $urlSinc || ! $urlSinc->url_servicio) {
            return ['success' => false, 'message' => 'URL de sincronización no configurada en url_api_servicios.'];
        }

        return [
            'success' => true,
            'token' => $token,
            'urlSinc' => $urlSinc->url_servicio,
            'urlCodigos' => $urlCodigos?->url_servicio ?? '',
            'ambiente' => $ambiente,
            'modalidad' => $modalidad,
            'codigoPuntoVenta' => $codigoPuntoVenta,
            'puntoVentaId' => $puntoVenta->id,
            'sucursalId' => $sucursal->id,
            'codigoSistema' => $codigoSistema,
            'codigoSucursal' => (int) $sucursal->codigo_sucursal,
            'cuis' => $cuisModel->codigo,
            'cuisId' => $cuisModel->id,
            'nit' => $nit,
        ];
    }

    /**
     * Ejecuta las pruebas de la Etapa II (Sincronización de Catálogos)
     *
     * @param  int  $repeticiones  Cantidad de llamadas por registro (normalmente 50)
     * @param  int|null  $pvFiltrado  1, 0, o null para ambos
     * @param  string|null  $metodoFiltrado  Método SOAP específico si se desea correr solo uno
     * @param  callable|null  $onProgress  Callback ($registroActual, $totalRegistros, $info)
     * @return array{total_intentos: int, exitosos: int, fallidos: int, detalle: array}
     */
    public function ejecutarEtapaCatalogos(
        int $repeticiones = 50,
        ?int $pvFiltrado = null,
        ?string $metodoFiltrado = null,
        ?callable $onProgress = null
    ): array {
        $pvs = $pvFiltrado !== null ? [$pvFiltrado] : [1, 0];
        $metodos = $this->metodosCatalogos();

        if ($metodoFiltrado) {
            $metodos = array_values(array_filter($metodos, fn ($m) => $m['metodo'] === $metodoFiltrado));
        }

        $totalRegistros = count($metodos) * count($pvs);
        $registroIndex = 0;
        $totalIntentos = 0;
        $totalExitosos = 0;
        $totalFallidos = 0;
        $detalle = [];

        foreach ($metodos as $m) {
            $metodo = $m['metodo'];
            $nombre = $m['nombre'];

            foreach ($pvs as $pv) {
                $registroIndex++;
                $contexto = $this->resolverContexto($pv);

                if (! $contexto['success']) {
                    $detalle[] = [
                        'metodo' => $metodo,
                        'nombre' => $nombre,
                        'pv' => $pv,
                        'exitosos' => 0,
                        'fallidos' => $repeticiones,
                        'error' => $contexto['message'],
                    ];
                    $totalIntentos += $repeticiones;
                    $totalFallidos += $repeticiones;

                    continue;
                }

                $exitososRegistro = 0;
                $fallidosRegistro = 0;
                $ultimoError = null;

                for ($i = 1; $i <= $repeticiones; $i++) {
                    $totalIntentos++;
                    try {
                        $respuestaJson = $this->siatController->{$metodo}(
                            $contexto['token'],
                            $contexto['urlSinc'],
                            $contexto['ambiente'],
                            $contexto['codigoPuntoVenta'],
                            $contexto['codigoSistema'],
                            $contexto['codigoSucursal'],
                            $contexto['cuis'],
                            $contexto['nit']
                        );

                        $data = json_decode($respuestaJson);
                        if ($data && $data->estado === 'success') {
                            $exitososRegistro++;
                            $totalExitosos++;
                        } else {
                            $fallidosRegistro++;
                            $totalFallidos++;
                            $ultimoError = $data->msg ?? $data->mensaje ?? 'Error SOAP';
                        }
                    } catch (Exception $e) {
                        $fallidosRegistro++;
                        $totalFallidos++;
                        $ultimoError = $e->getMessage();
                    }

                    if ($onProgress) {
                        $onProgress($registroIndex, $totalRegistros, [
                            'metodo' => $metodo,
                            'nombre' => $nombre,
                            'pv' => $pv,
                            'iteracion' => $i,
                            'total_iteraciones' => $repeticiones,
                            'exitosos' => $exitososRegistro,
                            'fallidos' => $fallidosRegistro,
                            'error' => $ultimoError,
                        ]);
                    }

                    // Pausa entre llamadas para evitar bloqueos por rate-limiting del SIN
                    usleep(120000); // 120 ms
                }

                $detalle[] = [
                    'metodo' => $metodo,
                    'nombre' => $nombre,
                    'pv' => $pv,
                    'exitosos' => $exitososRegistro,
                    'fallidos' => $fallidosRegistro,
                    'error' => $ultimoError,
                ];
            }
        }

        return [
            'total_intentos' => $totalIntentos,
            'exitosos' => $totalExitosos,
            'fallidos' => $totalFallidos,
            'detalle' => $detalle,
        ];
    }

    /**
     * Ejecuta las pruebas de la Etapa III (Obtención de CUFD)
     *
     * @param  int  $repeticiones  Normalmente 100 por punto de venta
     * @param  int|null  $pvFiltrado  1, 0 o null para ambos
     * @param  callable|null  $onProgress  Callback ($registroActual, $totalRegistros, $info)
     * @return array{total_intentos: int, exitosos: int, fallidos: int, detalle: array}
     */
    public function ejecutarEtapaCufd(
        int $repeticiones = 100,
        ?int $pvFiltrado = null,
        ?callable $onProgress = null
    ): array {
        $pvs = $pvFiltrado !== null ? [$pvFiltrado] : [1, 0];
        $totalRegistros = count($pvs);
        $registroIndex = 0;
        $totalIntentos = 0;
        $totalExitosos = 0;
        $totalFallidos = 0;
        $detalle = [];
        $userId = Auth::id();

        foreach ($pvs as $pv) {
            $registroIndex++;
            $contexto = $this->resolverContexto($pv);

            if (! $contexto['success']) {
                $detalle[] = [
                    'pv' => $pv,
                    'exitosos' => 0,
                    'fallidos' => $repeticiones,
                    'error' => $contexto['message'],
                ];
                $totalIntentos += $repeticiones;
                $totalFallidos += $repeticiones;

                continue;
            }

            if (! $contexto['urlCodigos']) {
                $msg = 'URL de facturacionCodigos no configurada.';
                $detalle[] = [
                    'pv' => $pv,
                    'exitosos' => 0,
                    'fallidos' => $repeticiones,
                    'error' => $msg,
                ];
                $totalIntentos += $repeticiones;
                $totalFallidos += $repeticiones;

                continue;
            }

            $exitososRegistro = 0;
            $fallidosRegistro = 0;
            $ultimoError = null;

            for ($i = 1; $i <= $repeticiones; $i++) {
                $totalIntentos++;
                try {
                    $respuestaJson = $this->siatController->cufd(
                        $contexto['token'],
                        $contexto['urlCodigos'],
                        $contexto['ambiente'],
                        $contexto['modalidad'],
                        $contexto['codigoPuntoVenta'],
                        $contexto['codigoSistema'],
                        $contexto['codigoSucursal'],
                        $contexto['cuis'],
                        $contexto['nit']
                    );

                    $data = json_decode($respuestaJson);
                    $resp = $data->resultado->RespuestaCufd ?? null;

                    if ($data && $data->estado === 'success' && $resp && $resp->transaccion) {
                        $exitososRegistro++;
                        $totalExitosos++;

                        // Actualizar en base de datos el último CUFD
                        Cufd::updateOrCreate([
                            'sucursal_id' => $contexto['sucursalId'],
                            'punto_venta_id' => $contexto['puntoVentaId'],
                            'codigo_ambiente' => (string) $contexto['ambiente'],
                        ], [
                            'usuario_creador_id' => $userId,
                            'usuario_modificador_id' => $userId,
                            'codigo' => $resp->codigo ?? null,
                            'codigo_control' => $resp->codigoControl ?? null,
                            'direccion' => $resp->direccion ?? null,
                            'fecha_vigencia' => $resp->fechaVigencia ? Carbon::parse($resp->fechaVigencia)->format('Y-m-d H:i:s') : now()->addDay(),
                            'estado' => 'ACTIVO',
                        ]);
                    } else {
                        $fallidosRegistro++;
                        $totalFallidos++;
                        $ultimoError = $data->msg ?? $data->mensaje ?? 'Respuesta SOAP inválida o transacción fallida';
                    }
                } catch (Exception $e) {
                    $fallidosRegistro++;
                    $totalFallidos++;
                    $ultimoError = $e->getMessage();
                }

                if ($onProgress) {
                    $onProgress($registroIndex, $totalRegistros, [
                        'pv' => $pv,
                        'iteracion' => $i,
                        'total_iteraciones' => $repeticiones,
                        'exitosos' => $exitososRegistro,
                        'fallidos' => $fallidosRegistro,
                        'error' => $ultimoError,
                    ]);
                }

                // Pausa prudente de 120 ms
                usleep(120000);
            }

            $detalle[] = [
                'pv' => $pv,
                'exitosos' => $exitososRegistro,
                'fallidos' => $fallidosRegistro,
                'error' => $ultimoError,
            ];
        }

        return [
            'total_intentos' => $totalIntentos,
            'exitosos' => $totalExitosos,
            'fallidos' => $totalFallidos,
            'detalle' => $detalle,
        ];
    }

    /**
     * Ejecuta la ETAPA IV de Homologación: Consumo de métodos de emisión individual (250 Casos: 125 en PV 1 + 125 en PV 0)
     */
    public function ejecutarEtapaEmision(
        int $repeticiones = 125,
        ?int $puntoVentaFiltro = null,
        ?callable $onProgress = null
    ): array {
        $pvs = $puntoVentaFiltro !== null ? [$puntoVentaFiltro] : [1, 0];
        $totalRegistros = count($pvs);
        $registroIndex = 0;
        $totalIntentos = 0;
        $totalExitosos = 0;
        $totalFallidos = 0;
        $detalle = [];

        $facturacionService = app(SiatFacturacionService::class);
        $urlApi = new UrlApiServicio;
        $ambiente = (int) config('siat.codigo_ambiente', 2);
        $modalidad = (int) config('siat.codigo_modalidad', 2);
        $urlCompraVenta = $urlApi->getUrlFacturacionCompraVenta($ambiente, $modalidad);

        $sucursal = Sucursal::orderBy('codigo_sucursal', 'asc')->first();

        foreach ($pvs as $pv) {
            $registroIndex++;
            $contexto = $this->resolverContexto($pv);

            if (! $contexto['success'] || ! $urlCompraVenta) {
                $msg = $contexto['message'] ?? 'URL de Facturación Compra-Venta no configurada.';
                $detalle[] = [
                    'pv' => $pv,
                    'exitosos' => 0,
                    'fallidos' => $repeticiones,
                    'error' => $msg,
                ];
                $totalIntentos += $repeticiones;
                $totalFallidos += $repeticiones;

                continue;
            }

            $cufd = Cufd::where('sucursal_id', $contexto['sucursalId'])
                ->where('punto_venta_id', $contexto['puntoVentaId'])
                ->where('fecha_vigencia', '>', now())
                ->latest('id')
                ->first() ?? Cufd::where('sucursal_id', $contexto['sucursalId'])->latest('id')->first();

            if (! $cufd) {
                $detalle[] = [
                    'pv' => $pv,
                    'exitosos' => 0,
                    'fallidos' => $repeticiones,
                    'error' => "Sin CUFD vigente para el Punto de Venta {$pv}.",
                ];
                $totalIntentos += $repeticiones;
                $totalFallidos += $repeticiones;

                continue;
            }

            $exitososRegistro = 0;
            $fallidosRegistro = 0;
            $ultimoError = null;
            $puntoVentaModel = PuntoVenta::find($contexto['puntoVentaId']);

            $siguienteFacturaBase = $facturacionService->obtenerSiguienteNumeroFactura($sucursal->id, $contexto['puntoVentaId']);

            for ($i = 1; $i <= $repeticiones; $i++) {
                $totalIntentos++;
                try {
                    $fechaActual = now();
                    $numeroFactura = $siguienteFacturaBase + ($i - 1);
                    $fechaHoraCadena = $fechaActual->format('YmdHis').'000';
                    $cuf = $facturacionService->calcularCuf(
                        $contexto['nit'],
                        $fechaHoraCadena,
                        $contexto['codigoSucursal'],
                        $contexto['modalidad'],
                        1,
                        1,
                        1,
                        $numeroFactura,
                        $contexto['codigoPuntoVenta'],
                        $cufd->codigo_control
                    );

                    // Mock / Instancia rápida de factura para homologación
                    $facturaDummy = new Factura([
                        'fecha' => $fechaActual,
                        'numero_factura' => (string) $numeroFactura,
                        'cuf' => $cuf,
                        'codigo_tipo_documento_identidad' => 1,
                        'numero_documento' => '1234567',
                        'razon_social' => 'JUAN PEREZ',
                        'total' => 15.00,
                        'monto_total_sujeto_iva' => 15.00,
                        'descuento_adicional' => 0,
                        'codigo_metodo_pago' => 1,
                    ]);

                    $detalleDummy = [
                        new Detalle([
                            'producto_id' => 1,
                            'nombre_producto' => 'PARACETAMOL 500MG COMPRIMIDOS',
                            'precio' => 15.00,
                            'cantidad' => 1,
                            'descuento' => 0,
                            'total' => 15.00,
                        ]),
                    ];

                    $xml = $facturacionService->generarXmlFactura($facturaDummy, $detalleDummy, $cufd, $sucursal, $puntoVentaModel);
                    $gzip = $facturacionService->comprimirXmlGzip($xml);
                    $hash = $facturacionService->calcularHashSha256($gzip);
                    $fechaEnvioIso = $fechaActual->format('Y-m-d\TH:i:s').'.000';

                    $respRaw = $this->siatController->recepcionFactura(
                        $contexto['token'],
                        $urlCompraVenta->url_servicio,
                        $contexto['ambiente'],
                        1, // Sector 1
                        $contexto['modalidad'],
                        $contexto['codigoPuntoVenta'],
                        $contexto['codigoSistema'],
                        $contexto['codigoSucursal'],
                        $cufd->codigo,
                        $contexto['cuis'],
                        $contexto['nit'],
                        1, // Crédito Fiscal
                        $gzip,
                        $fechaEnvioIso,
                        $hash
                    );

                    $resp = json_decode($respRaw, true);
                    $resFacturacion = $resp['resultado']['RespuestaServicioFacturacion'] ?? [];
                    $codigoEstado = $resFacturacion['codigoEstado'] ?? null;
                    $codigoDescripcion = $resFacturacion['codigoDescripcion'] ?? null;
                    $transaccion = $resFacturacion['transaccion'] ?? false;

                    if (($resp['estado'] ?? '') === 'success' && ($codigoEstado == 908 || $codigoDescripcion === 'VALIDADA' || $transaccion === true)) {
                        $exitososRegistro++;
                        $totalExitosos++;
                    } else {
                        $fallidosRegistro++;
                        $totalFallidos++;
                        $ultimoError = $codigoDescripcion ?? ($resp['msg'] ?? 'Fallo SOAP en recepción individual');
                        if (! empty($resFacturacion['mensajesList'])) {
                            $msgs = is_array($resFacturacion['mensajesList']) ? $resFacturacion['mensajesList'] : [$resFacturacion['mensajesList']];
                            $ultimoError .= ': '.json_encode($msgs, JSON_UNESCAPED_UNICODE);
                        }
                    }
                } catch (Exception $e) {
                    $fallidosRegistro++;
                    $totalFallidos++;
                    $ultimoError = $e->getMessage();
                }

                if ($onProgress) {
                    $onProgress($registroIndex, $totalRegistros, [
                        'pv' => $pv,
                        'iteracion' => $i,
                        'total_iteraciones' => $repeticiones,
                        'exitosos' => $exitososRegistro,
                        'fallidos' => $fallidosRegistro,
                        'error' => $ultimoError,
                    ]);
                }

                usleep(120000); // Pausa anti-bloqueo 120 ms
            }

            $detalle[] = [
                'pv' => $pv,
                'exitosos' => $exitososRegistro,
                'fallidos' => $fallidosRegistro,
                'error' => $ultimoError,
            ];
        }

        return [
            'total_intentos' => $totalIntentos,
            'exitosos' => $totalExitosos,
            'fallidos' => $totalFallidos,
            'detalle' => $detalle,
        ];
    }

    /**
     * Ejecuta las pruebas de la Etapa V (Registro de Eventos Significativos)
     *
     * @param  int  $repeticiones  Normalmente 5 por registro (14 combinaciones x 5 = 70 pruebas)
     * @param  int|null  $pvFiltrado  1, 0 o null para ambos
     * @param  int|null  $motivoFiltrado  1 a 7 o null para todos
     * @param  callable|null  $onProgress  Callback ($registroActual, $totalRegistros, $info)
     * @return array{total_intentos: int, exitosos: int, fallidos: int, detalle: array}
     */
    public function ejecutarEtapaEventosSignificativos(
        int $repeticiones = 5,
        ?int $pvFiltrado = null,
        ?int $motivoFiltrado = null,
        ?callable $onProgress = null
    ): array {
        $pvs = $pvFiltrado !== null ? [$pvFiltrado] : [1, 0];

        $motivosOficiales = [
            1 => 'CORTE DEL SERVICIO DE INTERNET',
            2 => 'INACCESIBILIDAD AL SERVICIO WEB DE LA ADMINISTRACIÓN TRIBUTARIA',
            3 => 'INGRESO A ZONAS SIN INTERNET POR DESPLIEGUE DE PUNTO DE VENTA',
            4 => 'VENTA EN LUGARES SIN INTERNET',
            5 => 'VIRUS INFORMÁTICO O FALLA DE SOFTWARE',
            6 => 'CAMBIO DE INFRAESTRUCTURA DE SISTEMA O FALLA DE HARDWARE',
            7 => 'CORTE DE SUMINISTRO DE ENERGÍA ELÉCTRICA',
        ];

        if ($motivoFiltrado !== null && isset($motivosOficiales[$motivoFiltrado])) {
            $motivos = [$motivoFiltrado => $motivosOficiales[$motivoFiltrado]];
        } else {
            $motivos = $motivosOficiales;
        }

        // Matriz de combinaciones: Motivos x Puntos de Venta
        $combinaciones = [];
        foreach ($motivos as $codMotivo => $descMotivo) {
            foreach ($pvs as $pv) {
                $combinaciones[] = [
                    'motivo' => $codMotivo,
                    'descripcion' => $descMotivo,
                    'pv' => $pv,
                ];
            }
        }

        $totalRegistros = count($combinaciones);
        $registroIndex = 0;
        $totalIntentos = 0;
        $totalExitosos = 0;
        $totalFallidos = 0;
        $detalle = [];

        $urlApi = new UrlApiServicio;
        $ambiente = (int) config('siat.codigo_ambiente', 2);
        $modalidad = (int) config('siat.codigo_modalidad', 2);
        $urlOperacionesModel = $urlApi->getUrlFacturacionOperaciones($ambiente, $modalidad);
        $urlOperaciones = is_object($urlOperacionesModel) ? $urlOperacionesModel->url_servicio : (string) $urlOperacionesModel;

        $userId = Auth::id() ?? 1;
        $cursorFecha = null;

        foreach ($combinaciones as $comb) {
            $registroIndex++;
            $pv = $comb['pv'];
            $codMotivo = $comb['motivo'];
            $descMotivo = $comb['descripcion'];

            $contexto = $this->resolverContexto($pv);

            if (! $contexto['success'] || ! $urlOperaciones) {
                $msg = $contexto['message'] ?? 'URL de Facturación Operaciones no configurada.';
                $detalle[] = [
                    'motivo' => $codMotivo,
                    'descripcion' => $descMotivo,
                    'pv' => $pv,
                    'exitosos' => 0,
                    'fallidos' => $repeticiones,
                    'error' => $msg,
                ];
                $totalIntentos += $repeticiones;
                $totalFallidos += $repeticiones;

                continue;
            }

            $cufd = Cufd::where('sucursal_id', $contexto['sucursalId'])
                ->where('punto_venta_id', $contexto['puntoVentaId'])
                ->where('fecha_vigencia', '>', now())
                ->latest('id')
                ->first() ?? Cufd::where('sucursal_id', $contexto['sucursalId'])->latest('id')->first();

            if (! $cufd) {
                $detalle[] = [
                    'motivo' => $codMotivo,
                    'descripcion' => $descMotivo,
                    'pv' => $pv,
                    'exitosos' => 0,
                    'fallidos' => $repeticiones,
                    'error' => "Sin CUFD vigente para el Punto de Venta {$pv}.",
                ];
                $totalIntentos += $repeticiones;
                $totalFallidos += $repeticiones;

                continue;
            }

            $inicioCufd = Carbon::parse($cufd->fecha_vigencia, 'America/La_Paz')->subDay();
            $limiteSuperior = now('America/La_Paz')->subMinutes(3);

            if ($cursorFecha === null) {
                $maxFinExistente = EventoSignificativo::where('punto_venta_id', $contexto['puntoVentaId'])->max('fecha_fin');
                if ($maxFinExistente && Carbon::parse($maxFinExistente, 'America/La_Paz')->gt($inicioCufd)) {
                    $cursorFecha = Carbon::parse($maxFinExistente, 'America/La_Paz')->addMinutes(5);
                } else {
                    $cursorFecha = $inicioCufd->copy()->addMinutes(18);
                }
            } else {
                $cursorFecha = $cursorFecha->copy()->addMinutes(3);
            }

            if ($cursorFecha->gte($limiteSuperior)) {
                $cursorFecha = $inicioCufd->copy()->addMinutes(22);
            }

            $exitososRegistro = 0;
            $fallidosRegistro = 0;
            $ultimoError = null;
            $ultimoCodigoRecepcion = null;

            for ($i = 1; $i <= $repeticiones; $i++) {
                $totalIntentos++;
                $intentosRetry = 0;
                $exitoIteracion = false;

                while ($intentosRetry < 15 && ! $exitoIteracion) {
                    $intentosRetry++;
                    if ($cursorFecha->gte($limiteSuperior)) {
                        $cursorFecha = $inicioCufd->copy()->addMinutes(15 + ($intentosRetry * 5));
                    }

                    $fechaIni = $cursorFecha->format('Y-m-d\TH:i:s').'.000';
                    $fechaFin = $cursorFecha->copy()->addMinute()->format('Y-m-d\TH:i:s').'.000';

                    try {
                        $respRaw = $this->siatController->registroEventoSignificativo(
                            $contexto['token'],
                            $urlOperaciones,
                            $contexto['ambiente'],
                            $contexto['codigoPuntoVenta'],
                            $contexto['codigoSistema'],
                            $contexto['codigoSucursal'],
                            $cufd->codigo,
                            $contexto['cuis'],
                            $contexto['nit'],
                            $codMotivo,
                            $cufd->codigo,
                            $descMotivo,
                            $fechaIni,
                            $fechaFin
                        );

                        $resp = json_decode($respRaw, true);
                        $resEvento = $resp['resultado']['RespuestaListaEventos'] ?? [];
                        $transaccion = $resEvento['transaccion'] ?? false;
                        $codigoRecepcion = $resEvento['codigoRecepcionEventoSignificativo'] ?? null;

                        if ($transaccion && $codigoRecepcion) {
                            $exitososRegistro++;
                            $totalExitosos++;
                            $ultimoCodigoRecepcion = $codigoRecepcion;
                            $exitoIteracion = true;

                            // Registrar en base de datos para trazabilidad
                            try {
                                $tipoEventoModel = TipoEventoSignificativo::where('codigo_clasificador', $codMotivo)->first();
                                EventoSignificativo::create([
                                    'punto_venta_id' => $contexto['puntoVentaId'],
                                    'tipo_evento_significativo_id' => $tipoEventoModel?->id ?? 1,
                                    'codigo_recepcion' => (string) $codigoRecepcion,
                                    'fecha_ini' => Carbon::parse($fechaIni),
                                    'fecha_fin' => Carbon::parse($fechaFin),
                                    'descripcion' => $descMotivo,
                                    'cufd_activo_id' => $cufd->id,
                                    'cufd_evento_id' => $cufd->id,
                                    'cui_id' => $contexto['cuisId'] ?? null,
                                    'usuario_creador_id' => $userId,
                                    'estado' => 'REGISTRADO',
                                ]);
                            } catch (\Throwable $th) {
                                // Continuar sin abortar
                            }

                            $cursorFecha = $cursorFecha->addMinutes(3);
                        } else {
                            $msgs = $resEvento['mensajesList'] ?? [];
                            $msgArray = is_array($msgs) ? (isset($msgs['codigo']) ? [$msgs] : $msgs) : [];
                            $esSolapamiento = false;
                            foreach ($msgArray as $m) {
                                if (($m['codigo'] ?? null) == 981) {
                                    $esSolapamiento = true;
                                    break;
                                }
                            }

                            if ($esSolapamiento && $intentosRetry < 15) {
                                $cursorFecha = $cursorFecha->addMinutes(6);

                                continue;
                            }

                            $ultimoError = $resp['msg'] ?? 'Fallo en registro de evento';
                            if (! empty($resEvento['mensajesList'])) {
                                $ultimoError .= ': '.json_encode($msgArray, JSON_UNESCAPED_UNICODE);
                            }
                            $cursorFecha = $cursorFecha->addMinutes(3);
                            break;
                        }
                    } catch (Exception $e) {
                        $ultimoError = $e->getMessage();
                        $cursorFecha = $cursorFecha->addMinutes(3);
                        break;
                    }
                }

                if (! $exitoIteracion) {
                    $fallidosRegistro++;
                    $totalFallidos++;
                }

                if ($onProgress) {
                    $onProgress($registroIndex, $totalRegistros, [
                        'motivo' => $codMotivo,
                        'descripcion' => $descMotivo,
                        'pv' => $pv,
                        'iteracion' => $i,
                        'total_iteraciones' => $repeticiones,
                        'exitosos' => $exitososRegistro,
                        'fallidos' => $fallidosRegistro,
                        'codigo_recepcion' => $ultimoCodigoRecepcion,
                        'error' => $ultimoError,
                    ]);
                }

                usleep(120000); // Pausa anti-bloqueo 120 ms
            }

            $detalle[] = [
                'motivo' => $codMotivo,
                'descripcion' => $descMotivo,
                'pv' => $pv,
                'exitosos' => $exitososRegistro,
                'fallidos' => $fallidosRegistro,
                'codigo_recepcion' => $ultimoCodigoRecepcion,
                'error' => $ultimoError,
            ];
        }

        return [
            'total_intentos' => $totalIntentos,
            'exitosos' => $totalExitosos,
            'fallidos' => $totalFallidos,
            'detalle' => $detalle,
        ];
    }
}
