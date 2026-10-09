<?php

namespace App\Services;

use App\Http\Controllers\SiatController;
use App\Models\Cufd;
use App\Models\Cuis;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
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
}
