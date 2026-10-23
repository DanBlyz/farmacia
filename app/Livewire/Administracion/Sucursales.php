<?php

namespace App\Livewire\Administracion;

use App\Http\Controllers\SiatController;
use App\Models\Cuis;
use App\Models\DependeActividad;
use App\Models\ProductoServicio;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Models\TipoPuntoVenta;
use App\Models\UrlApiServicio;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Sucursales extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public int $perPage = 10;

    // Estado del Formulario / Modal
    public bool $showModal = false;

    public ?int $sucursalId = null;

    public string $nombre = '';

    public string $direccion = '';

    public string $codigo_sucursal = '';

    // Estado Modal Puntos de Venta (SIAT)
    public bool $showPuntosVentaModal = false;

    public ?int $selectedSucursalId = null;

    public string $selectedSucursalNombre = '';

    // Estado Modal Registro de Punto de Venta (SIAT)
    public bool $showRegistroPuntoVentaModal = false;

    public string $newPuntoVentaNombre = '';

    public string $newPuntoVentaDescripcion = '';

    public ?int $newPuntoVentaTipoId = null;

    // Facturacion
    protected $codigo_ambiente;

    protected $codigo_modalidad;

    protected $api_token;

    protected $codigo_sistema;

    protected $nit;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        abort_if(! Auth::user()->tienePermiso('administracion.sucursales.crear'), 403, 'No tiene permiso para crear sucursales.');
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        abort_if(! Auth::user()->tienePermiso('administracion.sucursales.editar'), 403, 'No tiene permiso para editar sucursales.');
        $this->resetForm();
        $sucursal = Sucursal::findOrFail($id);
        $this->sucursalId = $sucursal->id;
        $this->nombre = (string) $sucursal->nombre;
        $this->direccion = (string) $sucursal->direccion;
        $this->codigo_sucursal = (string) $sucursal->codigo_sucursal;
        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->sucursalId) {
            abort_if(! Auth::user()->tienePermiso('administracion.sucursales.editar'), 403, 'No tiene permiso para editar sucursales.');
        } else {
            abort_if(! Auth::user()->tienePermiso('administracion.sucursales.crear'), 403, 'No tiene permiso para crear sucursales.');
        }

        $this->validate([
            'nombre' => 'required|string|max:150|unique:sucursales,nombre,'.($this->sucursalId ?? 'NULL').',id,deleted_at,NULL',
            'codigo_sucursal' => 'required|string|max:50|unique:sucursales,codigo_sucursal,'.($this->sucursalId ?? 'NULL').',id,deleted_at,NULL',
            'direccion' => 'nullable|string|max:255',
        ], [
            'nombre.required' => 'El nombre de la sucursal es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder los 150 caracteres.',
            'nombre.unique' => 'Ya existe una sucursal registrada con este nombre.',
            'codigo_sucursal.required' => 'El código de sucursal es obligatorio.',
            'codigo_sucursal.max' => 'El código no debe exceder los 50 caracteres.',
            'codigo_sucursal.unique' => 'Ya existe una sucursal con este código en el sistema.',
            'direccion.max' => 'La dirección no debe exceder los 255 caracteres.',
        ]);

        if ($this->sucursalId) {
            $sucursal = Sucursal::findOrFail($this->sucursalId);
            $sucursal->update([
                'nombre' => trim($this->nombre),
                'direccion' => trim($this->direccion),
                'codigo_sucursal' => trim($this->codigo_sucursal),
                'usuario_modificador_id' => Auth::id(),
            ]);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Operación Exitosa!',
                'text' => 'Sucursal actualizada exitosamente.',
            ]);
        } else {
            Sucursal::create([
                'nombre' => trim($this->nombre),
                'direccion' => trim($this->direccion),
                'codigo_sucursal' => trim($this->codigo_sucursal),
                'usuario_creador_id' => Auth::id(),
            ]);

            $suc = Sucursal::all();
            if ($suc->count() == 1) {
                $sucursal = $suc->first();
                PuntoVenta::create([
                    'usuario_creador_id' => Auth::id(),
                    'sucursal_id' => $sucursal->id,
                    'codigo' => 0,
                    'nombre' => 'PRIMER PUNTO VENTA POR DEFECTO',
                    'tipo' => 'VENTANILLA INICIAL POR DEFECTO',
                    'codigo_ambiente' => 2,
                ]);
            }

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Operación Exitosa!',
                'text' => 'Sucursal registrada exitosamente.',
            ]);
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        abort_if(! Auth::user()->tienePermiso('administracion.sucursales.eliminar'), 403, 'No tiene permiso para eliminar sucursales.');

        $sucursal = Sucursal::findOrFail($id);
        $sucursal->update([
            'usuario_eliminador_id' => Auth::id(),
        ]);
        $sucursal->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => '¡Eliminado!',
            'text' => 'Sucursal eliminada del catálogo exitosamente.',
        ]);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['sucursalId', 'nombre', 'direccion', 'codigo_sucursal']);
        $this->resetErrorBag();
    }

    public function boot(): void
    {
        $this->inicializarVariables();
    }

    public function mount()
    {
        $this->inicializarVariables();
    }

    public function openPuntosVentaModal(int $sucursalId): void
    {
        $sucursal = Sucursal::findOrFail($sucursalId);
        $this->selectedSucursalId = $sucursal->id;
        $this->selectedSucursalNombre = (string) $sucursal->nombre;
        $this->showPuntosVentaModal = true;
    }

    public function closePuntosVentaModal(): void
    {
        $this->showPuntosVentaModal = false;
        $this->selectedSucursalId = null;
        $this->selectedSucursalNombre = '';
    }

    public function crearCuis(int $puntoVentaId): void
    {
        $this->inicializarVariables();
        $puntoVenta = PuntoVenta::findOrFail($puntoVentaId);
        $sucursal = Sucursal::findOrFail($puntoVenta->sucursal_id);

        $urlApi = (new UrlApiServicio)->getUrlCodigos($this->codigo_ambiente, $this->codigo_modalidad);
        if (! $urlApi || ! $urlApi->url_servicio) {
            $msg = 'Error SIAT: No se encontró la URL de servicio "url_facturacionCodigos" para el ambiente '.$this->codigo_ambiente.' y modalidad '.$this->codigo_modalidad;
            session()->flash('error', $msg);
            $this->dispatch('swal-alert', icon: 'error', title: 'Error Configuración SIAT', message: $msg);

            return;
        }

        try {
            $siatController = new SiatController;
            $respuestaJson = $siatController->cuis(
                $this->api_token,
                $urlApi->url_servicio,
                $this->codigo_ambiente,
                $this->codigo_modalidad,
                $puntoVenta->codigo,
                $this->codigo_sistema,
                $sucursal->codigo_sucursal,
                $this->nit
            );

            $data = json_decode($respuestaJson);

            if ($data && $data->estado === 'success' && isset($data->resultado->RespuestaCuis)) {
                $respCuis = $data->resultado->RespuestaCuis;
                $codigoObtenido = $respCuis->codigo ?? null;
                $esTransaccionExitosa = ! empty($respCuis->transaccion);
                $esCuisVigenteExistente = ! empty($codigoObtenido) && isset($respCuis->mensajesList) && (
                    (is_object($respCuis->mensajesList) && ($respCuis->mensajesList->codigo ?? 0) == 980) ||
                    (is_array($respCuis->mensajesList) && collect($respCuis->mensajesList)->contains(fn ($m) => ($m->codigo ?? 0) == 980))
                );

                if ($esTransaccionExitosa || $esCuisVigenteExistente) {
                    Cuis::where('punto_venta_id', $puntoVenta->id)
                        ->where('estado', 'ACTIVO')
                        ->update(['estado' => 'INACTIVO']);

                    Cuis::create([
                        'usuario_creador_id' => Auth::id(),
                        'punto_venta_id' => $puntoVenta->id,
                        'sucursal_id' => $sucursal->id,
                        'codigo' => $codigoObtenido,
                        'fecha_vigencia' => Carbon::parse($respCuis->fechaVigencia)->format('Y-m-d H:i:s'),
                        'codigo_ambiente' => $this->codigo_ambiente,
                        'estado' => 'ACTIVO',
                    ]);

                    $msgSuccess = $esTransaccionExitosa
                        ? 'CUIS obtenido y registrado exitosamente para el Punto de Venta: '.$puntoVenta->nombre
                        : 'Se sincronizó el CUIS vigente existente desde SIAT ('.$codigoObtenido.') para el Punto de Venta: '.$puntoVenta->nombre;

                    session()->flash('message', $msgSuccess);
                    $this->dispatch('swal-alert', icon: 'success', title: '¡CUIS Registrado!', message: $msgSuccess);
                } else {
                    $mensaje = $this->extractSiatMensaje($respCuis->mensajesList ?? null);
                    $msgError = 'SIAT rechazó la solicitud de CUIS: '.($mensaje ?: 'Transacción no autorizada por SIAT.');
                    session()->flash('error', $msgError);
                    $this->dispatch('swal-alert', icon: 'error', title: 'Rechazo SIAT', message: $msgError);
                }
            } else {
                $msg = $data->msg ?? $data->error ?? 'Error de comunicación o credenciales inválidas';
                $msgFormatted = is_string($msg) ? $msg : json_encode($msg, JSON_UNESCAPED_UNICODE);
                session()->flash('error', 'Error en petición al SIAT: '.$msgFormatted);
                $this->dispatch('swal-alert', icon: 'error', title: 'Error SIAT', message: $msgFormatted);
            }
        } catch (\Exception $e) {
            $msgException = 'Excepción al solicitar CUIS: '.$e->getMessage();
            session()->flash('error', $msgException);
            $this->dispatch('swal-alert', icon: 'error', title: 'Excepción SIAT', message: $msgException);
        }
    }

    public function sincronizarActividades(int $puntoVentaId): void
    {
        $this->inicializarVariables();
        $puntoVenta = PuntoVenta::findOrFail($puntoVentaId);
        $sucursal = Sucursal::findOrFail($puntoVenta->sucursal_id);

        $cuis = (new Cuis)->cuisVigente($sucursal->id, $puntoVenta->id, $this->codigo_ambiente);
        if (! $cuis || Carbon::parse($cuis->fecha_vigencia)->lessThan(now())) {
            $msg = 'No existe un CUIS vigente para este punto de venta. Debe generar un CUIS antes de sincronizar actividades.';
            session()->flash('error', $msg);
            $this->dispatch('swal-alert', icon: 'error', title: 'Requiere CUIS Vigente', message: $msg);

            return;
        }

        $urlApi = (new UrlApiServicio)->getUrlSincronizacion($this->codigo_ambiente, $this->codigo_modalidad);
        if (! $urlApi || ! $urlApi->url_servicio) {
            $msg = 'Error SIAT: No se encontró la URL de servicio "url_facturacionSincronizacion" en la base de datos.';
            session()->flash('error', $msg);
            $this->dispatch('swal-alert', icon: 'error', title: 'Error Configuración SIAT', message: $msg);

            return;
        }

        try {
            $siatController = new SiatController;
            $respuestaJson = $siatController->sincronizarActividades(
                $this->api_token,
                $urlApi->url_servicio,
                $this->codigo_ambiente,
                $puntoVenta->codigo,
                $this->codigo_sistema,
                $sucursal->codigo_sucursal,
                $cuis->codigo,
                $this->nit
            );

            $data = json_decode($respuestaJson);

            if ($data && $data->estado === 'success' && isset($data->resultado->RespuestaListaActividades)) {
                $resp = $data->resultado->RespuestaListaActividades;
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
                            'codigo_ambiente' => $this->codigo_ambiente,
                        ], [
                            'usuario_modificador_id' => Auth::id(),
                            'usuario_creador_id' => Auth::id(),
                            'descripcion' => $item->descripcion ?? null,
                            'tipo_actividad' => $item->tipoActividad ?? '1',
                            'estado' => 'ACTIVO',
                        ]);
                        $count++;
                    }
                    $msgSuccess = "Sincronización exitosa: Se registraron/actualizaron {$count} actividades para el Punto de Venta.";
                    session()->flash('message', $msgSuccess);
                    $this->dispatch('swal-alert', icon: 'success', title: '¡Actividades Sincronizadas!', message: $msgSuccess);
                } else {
                    $mensaje = $this->extractSiatMensaje($resp->mensajesList ?? null);
                    $msgError = 'SIAT rechazó la sincronización de actividades: '.($mensaje ?: 'Transacción fallida.');
                    session()->flash('error', $msgError);
                    $this->dispatch('swal-alert', icon: 'error', title: 'Rechazo SIAT', message: $msgError);
                }
            } else {
                $msg = $data->msg ?? $data->mensaje ?? 'Error al comunicarse con SIAT';
                $msgFormatted = is_string($msg) ? $msg : json_encode($msg, JSON_UNESCAPED_UNICODE);
                session()->flash('error', 'Error al sincronizar actividades: '.$msgFormatted);
                $this->dispatch('swal-alert', icon: 'error', title: 'Error SIAT', message: $msgFormatted);
            }
        } catch (\Exception $e) {
            $msgException = 'Excepción al sincronizar actividades: '.$e->getMessage();
            session()->flash('error', $msgException);
            $this->dispatch('swal-alert', icon: 'error', title: 'Excepción SIAT', message: $msgException);
        }
    }

    public function sincronizarProductosServicios(int $puntoVentaId): void
    {
        $this->inicializarVariables();
        $puntoVenta = PuntoVenta::findOrFail($puntoVentaId);
        $sucursal = Sucursal::findOrFail($puntoVenta->sucursal_id);

        $cuis = (new Cuis)->cuisVigente($sucursal->id, $puntoVenta->id, $this->codigo_ambiente);
        if (! $cuis || Carbon::parse($cuis->fecha_vigencia)->lessThan(now())) {
            $msg = 'No existe un CUIS vigente para este punto de venta. Debe generar un CUIS antes de sincronizar productos y servicios.';
            session()->flash('error', $msg);
            $this->dispatch('swal-alert', icon: 'error', title: 'Requiere CUIS Vigente', message: $msg);

            return;
        }

        $urlApi = (new UrlApiServicio)->getUrlSincronizacion($this->codigo_ambiente, $this->codigo_modalidad);
        if (! $urlApi || ! $urlApi->url_servicio) {
            $msg = 'Error SIAT: No se encontró la URL de servicio "url_facturacionSincronizacion" en la base de datos.';
            session()->flash('error', $msg);
            $this->dispatch('swal-alert', icon: 'error', title: 'Error Configuración SIAT', message: $msg);

            return;
        }

        try {
            $siatController = new SiatController;
            $respuestaJson = $siatController->sincronizarListaProductosServicios(
                $this->api_token,
                $urlApi->url_servicio,
                $this->codigo_ambiente,
                $puntoVenta->codigo,
                $this->codigo_sistema,
                $sucursal->codigo_sucursal,
                $cuis->codigo,
                $this->nit
            );

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
                            'codigo_ambiente' => $this->codigo_ambiente,
                            'codigo_actividad' => $item->codigoActividad ?? null,
                            'codigo_producto' => $item->codigoProducto ?? null,
                        ], [
                            'usuario_modificador_id' => Auth::id(),
                            'usuario_creador_id' => Auth::id(),
                            'descripcion_producto' => $item->descripcionProducto ?? null,
                            'nandina_pri' => $nandinaPri,
                            'nandina_seg' => $nandinaSeg,
                            'estado' => 'ACTIVO',
                        ]);
                        $count++;
                    }
                    $msgSuccess = "Sincronización exitosa: Se registraron/actualizaron {$count} productos/servicios.";
                    session()->flash('message', $msgSuccess);
                    $this->dispatch('swal-alert', icon: 'success', title: '¡Catálogo Sincronizado!', message: $msgSuccess);
                } else {
                    $mensaje = $this->extractSiatMensaje($resp->mensajesList ?? null);
                    $msgError = 'SIAT rechazó la sincronización de productos/servicios: '.($mensaje ?: 'Transacción fallida.');
                    session()->flash('error', $msgError);
                    $this->dispatch('swal-alert', icon: 'error', title: 'Rechazo SIAT', message: $msgError);
                }
            } else {
                $msg = $data->msg ?? $data->mensaje ?? 'Error de respuesta del SIAT';
                $msgFormatted = is_string($msg) ? $msg : json_encode($msg, JSON_UNESCAPED_UNICODE);
                session()->flash('error', 'Error al sincronizar productos/servicios: '.$msgFormatted);
                $this->dispatch('swal-alert', icon: 'error', title: 'Error SIAT', message: $msgFormatted);
            }
        } catch (\Exception $e) {
            $msgException = 'Excepción al sincronizar productos/servicios: '.$e->getMessage();
            session()->flash('error', $msgException);
            $this->dispatch('swal-alert', icon: 'error', title: 'Excepción SIAT', message: $msgException);
        }
    }

    public function sincronizarPuntosVenta(?int $sucursalId = null): void
    {
        $this->inicializarVariables();
        $targetSucursalId = $sucursalId ?: $this->selectedSucursalId;
        if (! $targetSucursalId) {
            return;
        }

        $sucursal = Sucursal::findOrFail($targetSucursalId);

        $puntoVenta0 = PuntoVenta::where('sucursal_id', $sucursal->id)->orderBy('codigo', 'asc')->first();
        if (! $puntoVenta0) {
            $puntoVenta0 = PuntoVenta::create([
                'sucursal_id' => $sucursal->id,
                'codigo' => 0,
                'nombre' => 'Punto de Venta 0',
                'tipo' => 'VENTANILLA',
                'codigo_ambiente' => $this->codigo_ambiente,
            ]);
        }

        $cuis = (new Cuis)->cuisVigente($sucursal->id, $puntoVenta0->id, $this->codigo_ambiente);
        if (! $cuis || Carbon::parse($cuis->fecha_vigencia)->lessThan(now())) {
            $msg = 'No existe un CUIS vigente para realizar la consulta. Debe generar un CUIS para el Punto de Venta 0 primero.';
            session()->flash('error', $msg);
            $this->dispatch('swal-alert', icon: 'error', title: 'Requiere CUIS Vigente', message: $msg);

            return;
        }

        $urlApi = (new UrlApiServicio)->getUrlOperaciones($this->codigo_ambiente, $this->codigo_modalidad);
        if (! $urlApi || ! $urlApi->url_servicio) {
            $msg = 'Error SIAT: No se encontró la URL de servicio "url_facturacion_operaciones" en la base de datos.';
            session()->flash('error', $msg);
            $this->dispatch('swal-alert', icon: 'error', title: 'Error Configuración SIAT', message: $msg);

            return;
        }

        try {
            $siatController = new SiatController;
            $respuestaJson = $siatController->consultaPuntoVenta(
                $this->api_token,
                $urlApi->url_servicio,
                $this->codigo_ambiente,
                $this->codigo_sistema,
                $sucursal->codigo_sucursal,
                $cuis->codigo,
                $this->nit
            );

            $data = json_decode($respuestaJson);

            $resp = $data->resultado->RespuestaConsultaPuntoVenta ?? null;
            if ($data && $data->estado === 'success' && $resp) {
                if ($resp->transaccion) {
                    $lista = $resp->listaPuntosVentas ?? [];
                    if (! is_array($lista)) {
                        $lista = $lista ? [$lista] : [];
                    }
                    $count = 0;
                    foreach ($lista as $item) {
                        if (! is_object($item)) {
                            continue;
                        }
                        $codigoPv = (int) ($item->codigoPuntoVenta ?? $item->codigo ?? 0);
                        PuntoVenta::updateOrCreate([
                            'sucursal_id' => $sucursal->id,
                            'codigo' => $codigoPv,
                        ], [
                            'nombre' => $item->nombrePuntoVenta ?? $item->nombre ?? ('Punto de Venta '.$codigoPv),
                            'tipo' => $item->tipoPuntoVenta ?? $item->tipo ?? 'VENTANILLA',
                            'codigo_ambiente' => $this->codigo_ambiente,
                        ]);
                        $count++;
                    }
                    $msgSuccess = "Sincronización exitosa: Se verificaron y actualizaron {$count} punto(s) de venta desde SIAT para la sucursal {$sucursal->nombre}.";
                    session()->flash('message', $msgSuccess);
                    $this->dispatch('swal-alert', icon: 'success', title: '¡Puntos de Venta Sincronizados!', message: $msgSuccess);
                } else {
                    $mensaje = $this->extractSiatMensaje($resp->mensajesList ?? null);
                    $msgError = 'SIAT rechazó la consulta de puntos de venta: '.($mensaje ?: 'Transacción fallida.');
                    session()->flash('error', $msgError);
                    $this->dispatch('swal-alert', icon: 'error', title: 'Rechazo SIAT', message: $msgError);
                }
            } else {
                $msg = $data->msg ?? $data->mensaje ?? 'Error de comunicación al consultar los puntos de venta con SIAT.';
                $msgFormatted = is_string($msg) ? $msg : json_encode($msg, JSON_UNESCAPED_UNICODE);
                session()->flash('error', 'Error al consultar puntos de venta: '.$msgFormatted);
                $this->dispatch('swal-alert', icon: 'error', title: 'Error SIAT', message: $msgFormatted);
            }
        } catch (\Exception $e) {
            $msgException = 'Excepción al consultar puntos de venta: '.$e->getMessage();
            session()->flash('error', $msgException);
            $this->dispatch('swal-alert', icon: 'error', title: 'Excepción SIAT', message: $msgException);
        }
    }

    public function cierreOperacionesSistema(?int $sucursalId = null): void
    {
        $this->inicializarVariables();
        $targetSucursalId = $sucursalId ?: $this->selectedSucursalId;
        if (! $targetSucursalId) {
            return;
        }

        $sucursal = Sucursal::findOrFail($targetSucursalId);

        $puntoVenta0 = PuntoVenta::where('sucursal_id', $sucursal->id)->orderBy('codigo', 'asc')->first();
        if (! $puntoVenta0) {
            $msg = 'No existe ningún punto de venta en esta sucursal para autorizar el cierre de operaciones.';
            session()->flash('error', $msg);
            $this->dispatch('swal-alert', icon: 'error', title: 'Atención', message: $msg);

            return;
        }

        $cuis = (new Cuis)->cuisVigente($sucursal->id, $puntoVenta0->id, $this->codigo_ambiente);
        if (! $cuis || Carbon::parse($cuis->fecha_vigencia)->lessThan(now())) {
            $msg = 'No existe un CUIS vigente para la sucursal. Debe generar un CUIS antes de solicitar el Cierre de Operaciones del Sistema.';
            session()->flash('error', $msg);
            $this->dispatch('swal-alert', icon: 'error', title: 'Requiere CUIS Vigente', message: $msg);

            return;
        }

        $urlApi = (new UrlApiServicio)->getUrlOperaciones($this->codigo_ambiente, $this->codigo_modalidad);
        if (! $urlApi || ! $urlApi->url_servicio) {
            $msg = 'Error SIAT: No se encontró la URL de servicio "url_facturacion_operaciones" en la base de datos.';
            session()->flash('error', $msg);
            $this->dispatch('swal-alert', icon: 'error', title: 'Error Configuración SIAT', message: $msg);

            return;
        }

        try {
            $siatController = new SiatController;
            $respuestaJson = $siatController->cierreOperacionesSistema(
                $this->api_token,
                $urlApi->url_servicio,
                $this->codigo_ambiente,
                $this->codigo_modalidad,
                $puntoVenta0->codigo,
                $this->codigo_sistema,
                $sucursal->codigo_sucursal,
                $cuis->codigo,
                $this->nit
            );

            $data = json_decode($respuestaJson);

            $resp = $data->resultado->RespuestaCierreSistemas
                ?? $data->resultado->RespuestaCierreOperacionesSistema
                ?? $data->resultado->RespuestaOperaciones
                ?? null;

            if ($data && $data->estado === 'success' && $resp) {
                if ($resp->transaccion) {
                    // Inactivar todos los CUIS locales de la sucursal para permitir su renovación inmediata
                    Cuis::where('sucursal_id', $sucursal->id)
                        ->where('estado', 'ACTIVO')
                        ->update([
                            'estado' => 'INACTIVO',
                            'fecha_vigencia' => now()->subMinute(),
                        ]);

                    $msgSuccess = "El Cierre de Operaciones del Sistema fue registrado y autorizado exitosamente en SIAT para la sucursal \"{$sucursal->nombre}\". Ya puede obtener un nuevo CUIS.";
                    session()->flash('message', $msgSuccess);
                    $this->dispatch('swal-alert', icon: 'success', title: '¡Cierre de Operaciones Exitoso!', message: $msgSuccess);
                } else {
                    $codMensaje = 0;
                    if (isset($resp->mensajesList)) {
                        if (is_object($resp->mensajesList) && isset($resp->mensajesList->codigo)) {
                            $codMensaje = (int) $resp->mensajesList->codigo;
                        } elseif (is_array($resp->mensajesList) && isset($resp->mensajesList[0]->codigo)) {
                            $codMensaje = (int) $resp->mensajesList[0]->codigo;
                        }
                    }

                    if ($codMensaje === 913) {
                        // El CUIS ya no era válido o ya estaba cerrado en SIAT
                        Cuis::where('sucursal_id', $sucursal->id)
                            ->where('estado', 'ACTIVO')
                            ->update([
                                'estado' => 'INACTIVO',
                                'fecha_vigencia' => now()->subMinute(),
                            ]);

                        $msgAviso = 'SIAT informó que el CUIS enviado ya se encuentra cerrado o inválido (Código 913). Se ha actualizado el estado local para que pueda generar o renovar su CUIS de inmediato.';
                        session()->flash('message', $msgAviso);
                        $this->dispatch('swal-alert', icon: 'info', title: 'CUIS Ya Cerrado en SIAT', message: $msgAviso);
                    } else {
                        $mensaje = $this->extractSiatMensaje($resp->mensajesList ?? null);
                        $msgError = 'SIAT rechazó el Cierre de Operaciones: '.($mensaje ?: 'Transacción rechazada.');
                        session()->flash('error', $msgError);
                        $this->dispatch('swal-alert', icon: 'error', title: 'Rechazo SIAT', message: $msgError);
                    }
                }
            } else {
                $msg = $data->msg ?? $data->mensaje ?? 'Error al comunicarse con SIAT para el Cierre de Operaciones de Sistema.';
                $msgFormatted = is_string($msg) ? $msg : json_encode($msg, JSON_UNESCAPED_UNICODE);
                session()->flash('error', 'Error en Cierre de Operaciones: '.$msgFormatted);
                $this->dispatch('swal-alert', icon: 'error', title: 'Error SIAT', message: $msgFormatted);
            }
        } catch (\Exception $e) {
            $msgException = 'Excepción al solicitar Cierre de Operaciones: '.$e->getMessage();
            session()->flash('error', $msgException);
            $this->dispatch('swal-alert', icon: 'error', title: 'Excepción SIAT', message: $msgException);
        }
    }

    public function openRegistroPuntoVentaModal(?int $sucursalId = null): void
    {
        $targetSucursalId = $sucursalId ?: $this->selectedSucursalId;
        if (! $targetSucursalId) {
            return;
        }

        $sucursal = Sucursal::findOrFail($targetSucursalId);
        $this->selectedSucursalId = $sucursal->id;
        $this->selectedSucursalNombre = (string) $sucursal->nombre;
        $this->reset(['newPuntoVentaNombre', 'newPuntoVentaDescripcion', 'newPuntoVentaTipoId']);
        $this->resetErrorBag();

        $this->showPuntosVentaModal = false;
        $this->showRegistroPuntoVentaModal = true;
    }

    public function closeRegistroPuntoVentaModal(): void
    {
        $this->showRegistroPuntoVentaModal = false;
        $this->reset(['newPuntoVentaNombre', 'newPuntoVentaDescripcion', 'newPuntoVentaTipoId']);
        $this->resetErrorBag();
        if ($this->selectedSucursalId) {
            $this->showPuntosVentaModal = true;
        }
    }

    public function guardarRegistroPuntoVenta(): void
    {
        $this->inicializarVariables();
        $this->validate([
            'newPuntoVentaNombre' => 'required|string|max:150',
            'newPuntoVentaDescripcion' => 'required|string|max:255',
            'newPuntoVentaTipoId' => 'required|integer',
        ], [
            'newPuntoVentaNombre.required' => 'El nombre del punto de venta es obligatorio.',
            'newPuntoVentaNombre.max' => 'El nombre no debe exceder los 150 caracteres.',
            'newPuntoVentaDescripcion.required' => 'La descripción del punto de venta es obligatoria.',
            'newPuntoVentaDescripcion.max' => 'La descripción no debe exceder los 255 caracteres.',
            'newPuntoVentaTipoId.required' => 'Debe seleccionar el tipo de punto de venta SIAT.',
        ]);

        $sucursal = Sucursal::findOrFail($this->selectedSucursalId);

        $puntoVenta0 = PuntoVenta::where('sucursal_id', $sucursal->id)->orderBy('codigo', 'asc')->first();
        if (! $puntoVenta0) {
            $puntoVenta0 = PuntoVenta::create([
                'sucursal_id' => $sucursal->id,
                'codigo' => 0,
                'nombre' => 'Punto de Venta 0',
                'tipo' => 'VENTANILLA',
                'codigo_ambiente' => $this->codigo_ambiente,
            ]);
        }

        $cuis = (new Cuis)->cuisVigente($sucursal->id, $puntoVenta0->id, $this->codigo_ambiente);
        if (! $cuis || Carbon::parse($cuis->fecha_vigencia)->lessThan(now())) {
            $msg = 'No existe un CUIS vigente en la sucursal. Debe generar un CUIS para el Punto de Venta 0 antes de registrar un nuevo Punto de Venta.';
            session()->flash('error', $msg);
            $this->dispatch('swal-alert', icon: 'error', title: 'Requiere CUIS Vigente', message: $msg);

            return;
        }

        $urlApi = (new UrlApiServicio)->getUrlOperaciones($this->codigo_ambiente, $this->codigo_modalidad);
        if (! $urlApi || ! $urlApi->url_servicio) {
            $msg = 'Error SIAT: No se encontró la URL de servicio "url_facturacion_operaciones" en la base de datos.';
            session()->flash('error', $msg);
            $this->dispatch('swal-alert', icon: 'error', title: 'Error Configuración SIAT', message: $msg);

            return;
        }

        try {
            $siatController = new SiatController;
            $respuestaJson = $siatController->registroPuntoVenta(
                trim($this->newPuntoVentaDescripcion),
                trim($this->newPuntoVentaNombre),
                $this->api_token,
                $urlApi->url_servicio,
                $this->codigo_ambiente,
                $this->codigo_modalidad,
                $this->codigo_sistema,
                $sucursal->codigo_sucursal,
                (int) $this->newPuntoVentaTipoId,
                $cuis->codigo,
                $this->nit
            );

            $data = json_decode($respuestaJson);

            $resp = $data->resultado->RespuestaRegistroPuntoVenta ?? $data->resultado->RespuestaRegistro ?? null;
            if ($data && $data->estado === 'success' && $resp) {
                if ($resp->transaccion) {
                    $codigoPv = (int) ($resp->codigoPuntoVenta ?? $resp->codigo ?? 0);

                    $tipoDesc = TipoPuntoVenta::where('codigo_clasificador', $this->newPuntoVentaTipoId)->value('descripcion') ?? 'VENTANILLA';

                    PuntoVenta::updateOrCreate([
                        'sucursal_id' => $sucursal->id,
                        'codigo' => $codigoPv,
                    ], [
                        'usuario_modificador_id' => Auth::id(),
                        'usuario_creador_id' => Auth::id(),
                        'nombre' => trim($this->newPuntoVentaNombre),
                        'tipo' => $tipoDesc,
                        'codigo_ambiente' => $this->codigo_ambiente,
                    ]);

                    $msgSuccess = "¡Punto de Venta registrado exitosamente en SIAT! Se ha asignado el código de Punto de Venta: {$codigoPv}.";
                    session()->flash('message', $msgSuccess);
                    $this->dispatch('swal-alert', icon: 'success', title: '¡Punto de Venta Registrado!', message: $msgSuccess);

                    $this->closeRegistroPuntoVentaModal();
                } else {
                    $mensaje = $this->extractSiatMensaje($resp->mensajesList ?? null);
                    $msgError = 'SIAT rechazó el registro de Punto de Venta: '.($mensaje ?: 'Transacción rechazada.');
                    session()->flash('error', $msgError);
                    $this->dispatch('swal-alert', icon: 'error', title: 'Rechazo SIAT', message: $msgError);
                }
            } else {
                $msg = $data->msg ?? $data->mensaje ?? 'Error de respuesta del servidor SIAT al registrar el punto de venta.';
                $msgFormatted = is_string($msg) ? $msg : json_encode($msg, JSON_UNESCAPED_UNICODE);
                session()->flash('error', 'Error en registro de Punto de Venta: '.$msgFormatted);
                $this->dispatch('swal-alert', icon: 'error', title: 'Error SIAT', message: $msgFormatted);
            }
        } catch (\Exception $e) {
            $msgException = 'Excepción al registrar el Punto de Venta: '.$e->getMessage();
            session()->flash('error', $msgException);
            $this->dispatch('swal-alert', icon: 'error', title: 'Excepción SIAT', message: $msgException);
        }
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

    public function render(): View
    {
        $sucursales = Sucursal::query()
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('nombre', 'like', '%'.trim($this->search).'%')
                        ->orWhere('codigo_sucursal', 'like', '%'.trim($this->search).'%')
                        ->orWhere('direccion', 'like', '%'.trim($this->search).'%');
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        $puntosVenta = collect();
        $cuisMap = [];
        if ($this->showPuntosVentaModal && $this->selectedSucursalId) {
            $puntosVenta = PuntoVenta::where('sucursal_id', $this->selectedSucursalId)
                ->orderBy('codigo', 'asc')
                ->get();
            foreach ($puntosVenta as $pv) {
                $cuisMap[$pv->id] = (new Cuis)->cuisVigente($pv->sucursal_id, $pv->id, $this->codigo_ambiente);
            }
        }

        $tiposPuntoVenta = collect();
        if ($this->showRegistroPuntoVentaModal) {
            $tiposPuntoVenta = TipoPuntoVenta::where('estado', 'ACTIVO')->get();
            if ($tiposPuntoVenta->isEmpty()) {
                $tiposPuntoVenta = TipoPuntoVenta::all();
            }
        }

        return view('livewire.administracion.sucursales', [
            'sucursales' => $sucursales,
            'puntosVenta' => $puntosVenta,
            'cuisMap' => $cuisMap,
            'tiposPuntoVenta' => $tiposPuntoVenta,
        ]);
    }

    private function inicializarVariables()
    {
        $this->codigo_ambiente = config('siat.codigo_ambiente');
        $this->codigo_modalidad = config('siat.codigo_modalidad');
        $this->api_token = config('siat.api_token');
        $this->codigo_sistema = config('siat.codigo_sistema');
        $this->nit = config('siat.nit');
    }
}
