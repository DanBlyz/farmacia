<?php

namespace App\Services;

use App\Http\Controllers\SiatController;
use App\Models\Cuis;
use App\Models\DependeActividad;
use App\Models\LeyendaFactura;
use App\Models\MotivoAnulacion;
use App\Models\ProductoServicio;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Models\TipoDocumentoIdentidad;
use App\Models\TipoDocumentoSector;
use App\Models\TipoEventoSignificativo;
use App\Models\TipoMetodoPago;
use App\Models\TipoMoneda;
use App\Models\TipoPuntoVenta;
use App\Models\UnidadMedida;
use App\Models\UrlApiServicio;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;

class SiatSincronizacionService
{
    public function __construct(
        public SiatController $siatController = new SiatController,
    ) {}

    /**
     * Catálogos soportados y sus metadatos
     *
     * @return array<string, array{nombre: string, model: string, col_codigo: string}>
     */
    public function catalogoConfigs(): array
    {
        return [
            'TipoDocumentoSector' => [
                'nombre' => 'Documentos Sector',
                'model' => TipoDocumentoSector::class,
                'col_codigo' => 'codigo_clasificador',
            ],
            'TipoPuntoVenta' => [
                'nombre' => 'Puntos de Venta',
                'model' => TipoPuntoVenta::class,
                'col_codigo' => 'codigo_clasificador',
            ],
            'UnidadMedida' => [
                'nombre' => 'Unidades de Medida',
                'model' => UnidadMedida::class,
                'col_codigo' => 'codigo_clasificador',
            ],
            'TipoDocumentoIdentidad' => [
                'nombre' => 'Documentos de Identidad',
                'model' => TipoDocumentoIdentidad::class,
                'col_codigo' => 'codigo_clasificador',
            ],
            'TipoMetodoPago' => [
                'nombre' => 'Métodos de Pago',
                'model' => TipoMetodoPago::class,
                'col_codigo' => 'tipo_clasificador',
            ],
            'TipoMoneda' => [
                'nombre' => 'Tipos de Moneda',
                'model' => TipoMoneda::class,
                'col_codigo' => 'tipo_clasificador',
            ],
            'MotivoAnulacion' => [
                'nombre' => 'Motivos de Anulación',
                'model' => MotivoAnulacion::class,
                'col_codigo' => 'codigo_clasificador',
            ],
            'TipoEventoSignificativo' => [
                'nombre' => 'Eventos Significativos',
                'model' => TipoEventoSignificativo::class,
                'col_codigo' => 'codigo_clasificador',
            ],
            'DependeActividad' => [
                'nombre' => 'Actividades Económicas',
                'model' => DependeActividad::class,
                'col_codigo' => 'codigo_caeb',
            ],
            'ProductoServicio' => [
                'nombre' => 'Productos y Servicios SIN',
                'model' => ProductoServicio::class,
                'col_codigo' => 'codigo_producto',
            ],
            'LeyendaFactura' => [
                'nombre' => 'Leyendas de Factura',
                'model' => LeyendaFactura::class,
                'col_codigo' => 'codigo_actividad',
            ],
        ];
    }

    /**
     * Sincroniza un catálogo individual desde el SIAT
     *
     * @return array{success: bool, message: string, count: int}
     */
    public function sincronizarIndividual(string $tipoCatalogo, ?int $sucursalId = null, ?int $puntoVentaCodigo = null): array
    {
        $configs = $this->catalogoConfigs();
        if (! array_key_exists($tipoCatalogo, $configs)) {
            return [
                'success' => false,
                'message' => "El catálogo '{$tipoCatalogo}' no está configurado en el sistema.",
                'count' => 0,
            ];
        }

        $contexto = $this->obtenerContextoSiat($sucursalId, $puntoVentaCodigo);
        if (! $contexto['success']) {
            return [
                'success' => false,
                'message' => $contexto['message'],
                'count' => 0,
            ];
        }

        $token = $contexto['token'];
        $url = $contexto['url'];
        $ambiente = $contexto['ambiente'];
        $codigoPuntoVenta = $contexto['codigoPuntoVenta'];
        $codigoSistema = $contexto['codigoSistema'];
        $codigoSucursal = $contexto['codigoSucursal'];
        $cuis = $contexto['cuis'];
        $nit = $contexto['nit'];
        $userId = Auth::id();

        try {
            switch ($tipoCatalogo) {
                case 'TipoDocumentoSector':
                    $respuestaJson = $this->siatController->sincronizarParametricaTipoDocumentoSector(
                        $token,
                        $url,
                        $ambiente,
                        $codigoPuntoVenta,
                        $codigoSistema,
                        $codigoSucursal,
                        $cuis,
                        $nit
                    );

                    return $this->procesarListaParametricaGenerica($respuestaJson, TipoDocumentoSector::class, 'codigo_clasificador', $userId);

                case 'TipoPuntoVenta':
                    $respuestaJson = $this->siatController->sincronizarParametricaTipoPuntoVenta(
                        $token,
                        $url,
                        $ambiente,
                        $codigoPuntoVenta,
                        $codigoSistema,
                        $codigoSucursal,
                        $cuis,
                        $nit
                    );

                    return $this->procesarListaParametricaGenerica($respuestaJson, TipoPuntoVenta::class, 'codigo_clasificador', $userId);

                case 'UnidadMedida':
                    $respuestaJson = $this->siatController->sincronizarParametricaUnidadMedida(
                        $token,
                        $url,
                        $ambiente,
                        $codigoPuntoVenta,
                        $codigoSistema,
                        $codigoSucursal,
                        $cuis,
                        $nit
                    );

                    return $this->procesarListaParametricaGenerica($respuestaJson, UnidadMedida::class, 'codigo_clasificador', $userId);

                case 'TipoDocumentoIdentidad':
                    $respuestaJson = $this->siatController->sincronizarParametricaTipoDocumentoIdentidad(
                        $token,
                        $url,
                        $ambiente,
                        $codigoPuntoVenta,
                        $codigoSistema,
                        $codigoSucursal,
                        $cuis,
                        $nit
                    );

                    return $this->procesarListaParametricaGenerica($respuestaJson, TipoDocumentoIdentidad::class, 'codigo_clasificador', $userId);

                case 'TipoMetodoPago':
                    $respuestaJson = $this->siatController->sincronizarParametricaTipoMetodoPago(
                        $token,
                        $url,
                        $ambiente,
                        $codigoPuntoVenta,
                        $codigoSistema,
                        $codigoSucursal,
                        $cuis,
                        $nit
                    );

                    return $this->procesarListaParametricaGenerica($respuestaJson, TipoMetodoPago::class, 'tipo_clasificador', $userId);

                case 'TipoMoneda':
                    $respuestaJson = $this->siatController->sincronizarParametricaTipoMoneda(
                        $token,
                        $url,
                        $ambiente,
                        $codigoPuntoVenta,
                        $codigoSistema,
                        $codigoSucursal,
                        $cuis,
                        $nit
                    );

                    return $this->procesarListaParametricaGenerica($respuestaJson, TipoMoneda::class, 'tipo_clasificador', $userId);

                case 'MotivoAnulacion':
                    $respuestaJson = $this->siatController->sincronizarParametricaMotivoAnulacion(
                        $token,
                        $url,
                        $ambiente,
                        $codigoPuntoVenta,
                        $codigoSistema,
                        $codigoSucursal,
                        $cuis,
                        $nit
                    );

                    return $this->procesarListaParametricaGenerica($respuestaJson, MotivoAnulacion::class, 'codigo_clasificador', $userId);

                case 'TipoEventoSignificativo':
                    $respuestaJson = $this->siatController->sincronizarParametricaEventosSignificativos(
                        $token,
                        $url,
                        $ambiente,
                        $codigoPuntoVenta,
                        $codigoSistema,
                        $codigoSucursal,
                        $cuis,
                        $nit
                    );

                    return $this->procesarListaParametricaGenerica($respuestaJson, TipoEventoSignificativo::class, 'codigo_clasificador', $userId);

                case 'DependeActividad':
                    $respuestaJson = $this->siatController->sincronizarActividades(
                        $token,
                        $url,
                        $ambiente,
                        $codigoPuntoVenta,
                        $codigoSistema,
                        $codigoSucursal,
                        $cuis,
                        $nit
                    );

                    return $this->procesarActividades($respuestaJson, $ambiente, $userId);

                case 'ProductoServicio':
                    $respuestaJson = $this->siatController->sincronizarListaProductosServicios(
                        $token,
                        $url,
                        $ambiente,
                        $codigoPuntoVenta,
                        $codigoSistema,
                        $codigoSucursal,
                        $cuis,
                        $nit
                    );

                    return $this->procesarProductosServicios($respuestaJson, $ambiente, $userId);

                case 'LeyendaFactura':
                    $respuestaJson = $this->siatController->sincronizarListaLeyendasFactura(
                        $token,
                        $url,
                        $ambiente,
                        $codigoPuntoVenta,
                        $codigoSistema,
                        $codigoSucursal,
                        $cuis,
                        $nit
                    );

                    return $this->procesarLeyendasFactura($respuestaJson, $ambiente, $userId);

                default:
                    return [
                        'success' => false,
                        'message' => 'Catálogo no implementado.',
                        'count' => 0,
                    ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Excepción durante la sincronización: ' . $e->getMessage(),
                'count' => 0,
            ];
        }
    }

    /**
     * Sincroniza todos los catálogos en lote
     *
     * @return array{success: bool, total_actualizados: int, resultados: array<string, array{success: bool, message: string, count: int}>}
     */
    public function sincronizarTodos(?int $sucursalId = null, ?int $puntoVentaCodigo = null): array
    {
        $resultados = [];
        $totalActualizados = 0;
        $todosExitosos = true;

        foreach (array_keys($this->catalogoConfigs()) as $catalogo) {
            $res = $this->sincronizarIndividual($catalogo, $sucursalId, $puntoVentaCodigo);
            $resultados[$catalogo] = $res;
            if ($res['success']) {
                $totalActualizados += $res['count'];
            } else {
                $todosExitosos = false;
            }
            // Pequeña pausa para no saturar las conexiones SOAP
            usleep(150000);
        }

        return [
            'success' => $todosExitosos,
            'total_actualizados' => $totalActualizados,
            'resultados' => $resultados,
        ];
    }

    /**
     * Prepara el contexto fiscal (Sucursal, Punto de Venta, CUIS, URL SOAP, credenciales)
     *
     * @return array{success: bool, message?: string, token?: string, url?: string, ambiente?: int, codigoPuntoVenta?: int, codigoSistema?: string, codigoSucursal?: int, cuis?: string, nit?: string}
     */
    public function obtenerContextoSiat(?int $sucursalId = null, ?int $puntoVentaCodigo = null): array
    {
        $ambiente = (int) config('siat.codigo_ambiente');
        $modalidad = (int) config('siat.codigo_modalidad');
        $token = config('siat.api_token');
        $codigoSistema = config('siat.codigo_sistema');
        $nit = config('siat.nit');

        $sucursal = $sucursalId
            ? Sucursal::find($sucursalId)
            : Sucursal::orderBy('codigo_sucursal', 'asc')->first();

        if (! $sucursal) {
            return ['success' => false, 'message' => 'No hay ninguna sucursal registrada en el sistema.'];
        }

        $puntoVentaQuery = PuntoVenta::where('sucursal_id', $sucursal->id);
        if ($puntoVentaCodigo !== null) {
            $puntoVenta = $puntoVentaQuery->where('codigo', $puntoVentaCodigo)->first();
        } else {
            $puntoVenta = $puntoVentaQuery->orderBy('codigo', 'asc')->first();
        }

        if (! $puntoVenta) {
            return ['success' => false, 'message' => 'No se encontró punto de venta para la sucursal.'];
        }

        $cuisModel = (new Cuis)->cuisVigente($sucursal->id, $puntoVenta->id, $ambiente);
        if (! $cuisModel || Carbon::parse($cuisModel->fecha_vigencia)->lessThan(now())) {
            return ['success' => false, 'message' => "No existe un CUIS vigente para el Punto de Venta {$puntoVenta->codigo}. Genere un CUIS primero."];
        }

        $urlApi = (new UrlApiServicio)->getUrlSincronizacion($ambiente, $modalidad);
        if (! $urlApi || ! $urlApi->url_servicio) {
            return ['success' => false, 'message' => 'No se encontró la URL de servicio "url_facturacion_sincronizacion" en la base de datos.'];
        }

        return [
            'success' => true,
            'token' => $token,
            'url' => $urlApi->url_servicio,
            'ambiente' => $ambiente,
            'codigoPuntoVenta' => (int) $puntoVenta->codigo,
            'codigoSistema' => $codigoSistema,
            'codigoSucursal' => (int) $sucursal->codigo_sucursal,
            'cuis' => $cuisModel->codigo,
            'nit' => (string) $nit,
        ];
    }

    /**
     * Procesa respuestas estándar de parametricas (listaCodigos)
     *
     * @return array{success: bool, message: string, count: int}
     */
    protected function procesarListaParametricaGenerica(string $respuestaJson, string $modelClass, string $codigoKey, ?int $userId): array
    {
        $data = json_decode($respuestaJson);
        $resp = $data->resultado->RespuestaListaParametricas ?? $data->resultado->RespuestaListaEventos ?? null;

        if ($data && $data->estado === 'success' && $resp) {
            if ($resp->transaccion) {
                $lista = $resp->listaCodigos ?? [];
                if (! is_array($lista)) {
                    $lista = $lista ? [$lista] : [];
                }

                $count = 0;
                foreach ($lista as $item) {
                    if (! is_object($item)) {
                        continue;
                    }
                    $codigo = $item->codigoClasificador ?? null;
                    $descripcion = $item->descripcion ?? null;
                    if ($codigo === null || $descripcion === null) {
                        continue;
                    }

                    $insertData = [
                        'descripcion' => $descripcion,
                        'usuario_creador_id' => $userId,
                        'usuario_modificador_id' => $userId,
                    ];

                    $modelClass::updateOrCreate([
                        $codigoKey => $codigo,
                    ], $insertData);
                    $count++;
                }

                return [
                    'success' => true,
                    'message' => "Sincronizados {$count} registros exitosamente.",
                    'count' => $count,
                ];
            }

            $errorMsg = $this->extractSiatMensaje($resp->mensajesList ?? null);

            return [
                'success' => false,
                'message' => 'SIAT rechazó la transacción: ' . ($errorMsg ?: 'Error desconocido'),
                'count' => 0,
            ];
        }

        $err = $data->msg ?? $data->mensaje ?? 'Error de conexión con el WebService SIAT.';
        $msgFormatted = is_string($err) ? $err : json_encode($err, JSON_UNESCAPED_UNICODE);

        return [
            'success' => false,
            'message' => 'Error SIAT: ' . $msgFormatted,
            'count' => 0,
        ];
    }

    /**
     * Procesa Actividades Económicas (RespuestaListaActividades)
     *
     * @return array{success: bool, message: string, count: int}
     */
    protected function procesarActividades(string $respuestaJson, int $ambiente, ?int $userId): array
    {
        $data = json_decode($respuestaJson);
        $resp = $data->resultado->RespuestaListaActividades ?? null;

        if ($data && $data->estado === 'success' && $resp) {
            if ($resp->transaccion) {
                $lista = $resp->listaActividades ?? [];
                if (! is_array($lista)) {
                    $lista = [$lista];
                }
                $count = 0;
                foreach ($lista as $item) {
                    if (! is_object($item)) {
                        continue;
                    }
                    DependeActividad::updateOrCreate([
                        'codigo_caeb' => $item->codigoCaeb ?? null,
                        'codigo_ambiente' => (string) $ambiente,
                    ], [
                        'usuario_creador_id' => $userId,
                        'usuario_modificador_id' => $userId,
                        'descripcion' => $item->descripcion ?? null,
                        'tipo_actividad' => $item->tipoActividad ?? '1',
                        'estado' => 'ACTIVO',
                    ]);
                    $count++;
                }

                return [
                    'success' => true,
                    'message' => "Sincronizadas {$count} actividades económicas.",
                    'count' => $count,
                ];
            }

            $errorMsg = $this->extractSiatMensaje($resp->mensajesList ?? null);

            return ['success' => false, 'message' => 'SIAT rechazó actividades: ' . ($errorMsg ?: 'Transacción fallida'), 'count' => 0];
        }

        return ['success' => false, 'message' => 'Error al comunicarse con SIAT para actividades.', 'count' => 0];
    }

    /**
     * Procesa Productos y Servicios SIN (RespuestaListaProductos)
     *
     * @return array{success: bool, message: string, count: int}
     */
    protected function procesarProductosServicios(string $respuestaJson, int $ambiente, ?int $userId): array
    {
        $data = json_decode($respuestaJson);
        $resp = $data->resultado->RespuestaListaProductos ?? $data->resultado->RespuestaListaProductosServicios ?? null;

        if ($data && $data->estado === 'success' && $resp) {
            if ($resp->transaccion) {
                $lista = $resp->listaCodigos ?? [];
                if (! is_array($lista)) {
                    $lista = [$lista];
                }
                $count = 0;
                foreach ($lista as $item) {
                    if (! is_object($item)) {
                        continue;
                    }
                    $nandinaArr = $item->nandina ?? null;
                    $nandinaPri = is_array($nandinaArr) ? ($nandinaArr[0] ?? null) : $nandinaArr;
                    $nandinaSeg = is_array($nandinaArr) && count($nandinaArr) > 1 ? ($nandinaArr[1] ?? null) : null;

                    ProductoServicio::updateOrCreate([
                        'codigo_ambiente' => (string) $ambiente,
                        'codigo_actividad' => $item->codigoActividad ?? null,
                        'codigo_producto' => $item->codigoProducto ?? null,
                    ], [
                        'usuario_creador_id' => $userId,
                        'usuario_modificador_id' => $userId,
                        'descripcion_producto' => $item->descripcionProducto ?? null,
                        'nandina_pri' => $nandinaPri,
                        'nandina_seg' => $nandinaSeg,
                        'estado' => 'ACTIVO',
                    ]);
                    $count++;
                }

                return [
                    'success' => true,
                    'message' => "Sincronizados {$count} productos/servicios del SIN.",
                    'count' => $count,
                ];
            }

            $errorMsg = $this->extractSiatMensaje($resp->mensajesList ?? null);

            return ['success' => false, 'message' => 'SIAT rechazó productos/servicios: ' . ($errorMsg ?: 'Transacción fallida'), 'count' => 0];
        }

        return ['success' => false, 'message' => 'Error al comunicarse con SIAT para productos/servicios.', 'count' => 0];
    }

    /**
     * Procesa Leyendas de Facturas SIN (RespuestaListaParametricasLeyendas)
     *
     * @return array{success: bool, message: string, count: int}
     */
    protected function procesarLeyendasFactura(string $respuestaJson, int $ambiente, ?int $userId): array
    {
        $data = json_decode($respuestaJson);
        $resp = $data->resultado->RespuestaListaParametricasLeyendas ?? $data->resultado->RespuestaListaLeyendas ?? null;

        if ($data && $data->estado === 'success' && $resp) {
            if ($resp->transaccion) {
                $lista = $resp->listaLeyendas ?? [];
                if (! is_array($lista)) {
                    $lista = [$lista];
                }
                $count = 0;
                foreach ($lista as $item) {
                    if (! is_object($item)) {
                        continue;
                    }
                    $codigoActividad = $item->codigoActividad ?? null;
                    $descripcion = $item->descripcionLeyenda ?? null;
                    if (! $descripcion) {
                        continue;
                    }

                    LeyendaFactura::updateOrCreate([
                        'codigo_ambiente' => (string) $ambiente,
                        'codigo_actividad' => $codigoActividad,
                        'descripcion_leyenda' => $descripcion,
                    ], [
                        'usuario_creador_id' => $userId,
                        'usuario_modificador_id' => $userId,
                        'estado' => 'ACTIVO',
                    ]);
                    $count++;
                }

                return [
                    'success' => true,
                    'message' => "Sincronizadas {$count} leyendas oficiales de factura.",
                    'count' => $count,
                ];
            }

            $errorMsg = $this->extractSiatMensaje($resp->mensajesList ?? null);

            return ['success' => false, 'message' => 'SIAT rechazó leyendas: ' . ($errorMsg ?: 'Transacción fallida'), 'count' => 0];
        }

        return ['success' => false, 'message' => 'Error al comunicarse con SIAT para leyendas de factura.', 'count' => 0];
    }

    /**
     * Extrae texto legible de listas de mensajes SOAP del SIN
     */
    public function extractSiatMensaje(mixed $mensajesList): string
    {
        if (! $mensajesList) {
            return '';
        }
        if (is_string($mensajesList)) {
            return $mensajesList;
        }
        if (is_object($mensajesList) && isset($mensajesList->descripcion)) {
            return (string) $mensajesList->descripcion;
        }
        if (is_array($mensajesList)) {
            $msg = [];
            foreach ($mensajesList as $m) {
                if (is_object($m) && isset($m->descripcion)) {
                    $msg[] = (string) $m->descripcion;
                } elseif (is_string($m)) {
                    $msg[] = $m;
                }
            }

            return implode(' | ', $msg);
        }

        return json_encode($mensajesList, JSON_UNESCAPED_UNICODE);
    }
}
