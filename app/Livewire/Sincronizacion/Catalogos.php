<?php

namespace App\Livewire\Sincronizacion;

use App\Http\Controllers\SiatController;
use App\Models\Cuis;
use App\Models\MotivoAnulacion;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Catalogos extends Component
{
    protected $codigo_ambiente;

    protected $codigo_modalidad;

    protected $api_token;

    protected $codigo_sistema;

    protected $nit;

    public function boot(): void
    {
        $this->inicializarVariables();
    }

    public function mount()
    {
        $this->inicializarVariables();
    }

    private function inicializarVariables()
    {
        $this->codigo_ambiente = config('siat.codigo_ambiente', env('CODIGO_AMBIENTE', 2));
        $this->codigo_modalidad = config('siat.codigo_modalidad', env('CODIGO_MODALIDAD', 1));
        $this->api_token = config('siat.api_token', env('API_TOKEN'));
        $this->codigo_sistema = config('siat.codigo_sistema', env('CODIGO_SISTEMA'));
        $this->nit = config('siat.nit', env('NIT'));
    }

    private function extractSiatMensaje($mensajesList): string
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

    public function sincronizar(string $tipoCatalogo): void
    {
        abort_if(! Auth::user()?->tienePermiso('sincronizacion.catalogos.sincronizar'), 403, 'No tienes permiso para sincronizar catálogos del SIAT.');

        $this->inicializarVariables();

        $sucursal = Sucursal::orderBy('codigo_sucursal', 'asc')->first();
        if (! $sucursal) {
            $msg = 'No hay ninguna sucursal registrada en el sistema.';
            session()->flash('error', $msg);
            $this->dispatch('swal-alert', icon: 'error', title: 'Requiere Sucursal', message: $msg);

            return;
        }

        $puntoVenta0 = PuntoVenta::where('sucursal_id', $sucursal->id)->orderBy('codigo', 'asc')->first();
        if (! $puntoVenta0) {
            $msg = 'No hay ningún punto de venta registrado en la sucursal.';
            session()->flash('error', $msg);
            $this->dispatch('swal-alert', icon: 'error', title: 'Requiere Punto de Venta', message: $msg);

            return;
        }

        $cuis = (new Cuis)->cuisVigente($sucursal->id, $puntoVenta0->id, $this->codigo_ambiente);
        if (! $cuis || Carbon::parse($cuis->fecha_vigencia)->lessThan(now())) {
            $msg = 'No existe un CUIS vigente. Debe generar un CUIS para realizar la sincronización.';
            session()->flash('error', $msg);
            $this->dispatch('swal-alert', icon: 'error', title: 'Requiere CUIS Vigente', message: $msg);

            return;
        }

        $urlApi = (new UrlApiServicio)->getUrlSincronizacion($this->codigo_ambiente, $this->codigo_modalidad);
        if (! $urlApi || ! $urlApi->url_servicio) {
            $msg = 'Error SIAT: No se encontró la URL de servicio "url_facturacion_sincronizacion" en la base de datos.';
            session()->flash('error', $msg);
            $this->dispatch('swal-alert', icon: 'error', title: 'Error Configuración SIAT', message: $msg);

            return;
        }

        $siatController = new SiatController;
        $respuestaJson = '';

        try {
            switch ($tipoCatalogo) {
                case 'TipoDocumentoSector':
                    $respuestaJson = $siatController->sincronizarParametricaTipoDocumentoSector(
                        $this->api_token,
                        $urlApi->url_servicio,
                        $this->codigo_ambiente,
                        $puntoVenta0->codigo,
                        $this->codigo_sistema,
                        $sucursal->codigo_sucursal,
                        $cuis->codigo,
                        $this->nit
                    );
                    break;
                case 'TipoPuntoVenta':
                    $respuestaJson = $siatController->sincronizarParametricaTipoPuntoVenta(
                        $this->api_token,
                        $urlApi->url_servicio,
                        $this->codigo_ambiente,
                        $puntoVenta0->codigo,
                        $this->codigo_sistema,
                        $sucursal->codigo_sucursal,
                        $cuis->codigo,
                        $this->nit
                    );
                    break;
                case 'UnidadMedida':
                    $respuestaJson = $siatController->sincronizarParametricaUnidadMedida(
                        $this->api_token,
                        $urlApi->url_servicio,
                        $this->codigo_ambiente,
                        $puntoVenta0->codigo,
                        $this->codigo_sistema,
                        $sucursal->codigo_sucursal,
                        $cuis->codigo,
                        $this->nit
                    );
                    break;
                case 'TipoDocumentoIdentidad':
                    $respuestaJson = $siatController->sincronizarParametricaTipoDocumentoIdentidad(
                        $this->api_token,
                        $urlApi->url_servicio,
                        $this->codigo_ambiente,
                        $puntoVenta0->codigo,
                        $this->codigo_sistema,
                        $sucursal->codigo_sucursal,
                        $cuis->codigo,
                        $this->nit
                    );
                    break;
                case 'TipoMetodoPago':
                    $respuestaJson = $siatController->sincronizarParametricaTipoMetodoPago(
                        $this->api_token,
                        $urlApi->url_servicio,
                        $this->codigo_ambiente,
                        $puntoVenta0->codigo,
                        $this->codigo_sistema,
                        $sucursal->codigo_sucursal,
                        $cuis->codigo,
                        $this->nit
                    );
                    break;
                case 'TipoMoneda':
                    $respuestaJson = $siatController->sincronizarParametricaTipoMoneda(
                        $this->api_token,
                        $urlApi->url_servicio,
                        $this->codigo_ambiente,
                        $puntoVenta0->codigo,
                        $this->codigo_sistema,
                        $sucursal->codigo_sucursal,
                        $cuis->codigo,
                        $this->nit
                    );
                    break;
                case 'MotivoAnulacion':
                    $respuestaJson = $siatController->sincronizarParametricaMotivoAnulacion(
                        $this->api_token,
                        $urlApi->url_servicio,
                        $this->codigo_ambiente,
                        $puntoVenta0->codigo,
                        $this->codigo_sistema,
                        $sucursal->codigo_sucursal,
                        $cuis->codigo,
                        $this->nit
                    );
                    break;
                case 'TipoEventoSignificativo':
                    $respuestaJson = $siatController->sincronizarParametricaEventosSignificativos(
                        $this->api_token,
                        $urlApi->url_servicio,
                        $this->codigo_ambiente,
                        $puntoVenta0->codigo,
                        $this->codigo_sistema,
                        $sucursal->codigo_sucursal,
                        $cuis->codigo,
                        $this->nit
                    );
                    break;
                default:
                    $this->dispatch('swal-alert', icon: 'error', title: 'Catálogo No Válido', message: 'El tipo de catálogo enviado no es reconocido.');

                    return;
            }

            $data = json_decode($respuestaJson);
            $resp = $data->resultado->RespuestaListaParametricas ?? $data->resultado->RespuestaListaEventos ?? null;

            if ($data && $data->estado === 'success' && $resp) {
                if ($resp->transaccion) {
                    $lista = $resp->listaCodigos ?? [];
                    if (! is_array($lista)) {
                        $lista = $lista ? [$lista] : [];
                    }

                    $count = 0;
                    $modelClass = '\\App\\Models\\'.$tipoCatalogo;

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
                            'usuario_creador_id' => Auth::id(),
                            'usuario_modificador_id' => Auth::id(),
                        ];

                        if (in_array($tipoCatalogo, ['TipoMoneda', 'TipoMetodoPago'])) {
                            $modelClass::updateOrCreate([
                                'tipo_clasificador' => $codigo,
                            ], $insertData);
                        } else {
                            $modelClass::updateOrCreate([
                                'codigo_clasificador' => $codigo,
                            ], $insertData);
                        }
                        $count++;
                    }

                    $msgSuccess = "Sincronización exitosa: Se actualizaron {$count} registros desde SIAT para el catálogo {$tipoCatalogo}.";
                    session()->flash('message', $msgSuccess);
                    $this->dispatch('swal-alert', icon: 'success', title: '¡Catálogo Sincronizado!', message: $msgSuccess);
                } else {
                    $mensaje = $this->extractSiatMensaje($resp->mensajesList ?? null);
                    $msgError = 'SIAT rechazó la sincronización: '.($mensaje ?: 'Transacción fallida.');
                    session()->flash('error', $msgError);
                    $this->dispatch('swal-alert', icon: 'error', title: 'Rechazo SIAT', message: $msgError);
                }
            } else {
                $msg = $data->msg ?? $data->mensaje ?? 'Error de respuesta del servidor SIAT al sincronizar.';
                $msgFormatted = is_string($msg) ? $msg : json_encode($msg, JSON_UNESCAPED_UNICODE);
                session()->flash('error', 'Error SIAT: '.$msgFormatted);
                $this->dispatch('swal-alert', icon: 'error', title: 'Error SIAT', message: $msgFormatted);
            }
        } catch (\Exception $e) {
            $msgException = 'Excepción al sincronizar: '.$e->getMessage();
            session()->flash('error', $msgException);
            $this->dispatch('swal-alert', icon: 'error', title: 'Excepción SIAT', message: $msgException);
        }
    }

    public function render(): View
    {
        abort_if(! Auth::user()?->tienePermiso('sincronizacion.catalogos.ver'), 403, 'No tienes permiso para ver los catálogos.');

        return view('livewire.sincronizacion.catalogos', [
            'tipoDocumentoSector' => TipoDocumentoSector::all(),
            'tipoPuntoVenta' => TipoPuntoVenta::all(),
            'unidadMedida' => UnidadMedida::all(),
            'tipoDocumentoIdentidad' => TipoDocumentoIdentidad::all(),
            'tipoMetodoPago' => TipoMetodoPago::all(),
            'tipoMoneda' => TipoMoneda::all(),
            'motivoAnulacion' => MotivoAnulacion::all(),
            'tipoEventoSignificativo' => TipoEventoSignificativo::all(),
        ]);
    }
}
