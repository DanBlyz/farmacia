<?php

namespace App\Livewire\Sincronizacion;

use App\Models\EventoSignificativo;
use App\Models\Factura;
use App\Models\PaqueteFactura;
use App\Models\PuntoVenta;
use App\Models\TipoEventoSignificativo;
use App\Services\SiatFacturacionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class EventosSignificativos extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public int $perPage = 10;

    public ?int $filtroPuntoVentaId = null;

    public ?int $filtroMotivoId = null;

    public string $filtroEstado = '';

    // Modal de Nuevo Evento Significativo
    public bool $showCreateModal = false;

    public ?int $punto_venta_id = null;

    public ?int $tipo_evento_significativo_id = null;

    public string $fecha_ini = '';

    public string $fecha_fin = '';

    public string $descripcion = '';

    public ?int $cufd_evento_id = null;

    // Modal de Detalle
    public bool $showDetalleModal = false;

    public ?int $detalleEventoId = null;

    // Modal de Empaquetado y Emisión en Contingencia (Etapa VI SIAT)
    public bool $showPaqueteModal = false;

    public ?int $paqueteEventoId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroPuntoVentaId(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroMotivoId(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatedTipoEventoSignificativoId(?int $val): void
    {
        if ($val && empty($this->descripcion)) {
            $tipo = TipoEventoSignificativo::find($val);
            if ($tipo) {
                $this->descripcion = $tipo->descripcion;
            }
        }
    }

    public function mount(): void
    {
        $userPv = Auth::user()?->punto_venta_id;
        $this->punto_venta_id = $userPv ?: (PuntoVenta::orderBy('codigo', 'asc')->first()?->id);
    }

    public function abrirModalCrear(): void
    {
        abort_if(! Auth::user()?->tienePermiso('sincronizacion.eventos.crear'), 403, 'No tiene permiso para registrar eventos significativos.');

        $this->resetErrorBag();
        $userPv = Auth::user()?->punto_venta_id;
        $this->punto_venta_id = $userPv ?: (PuntoVenta::orderBy('codigo', 'asc')->first()?->id);

        $primerMotivo = TipoEventoSignificativo::orderBy('codigo_clasificador', 'asc')->first();
        $this->tipo_evento_significativo_id = $primerMotivo?->id;
        $this->descripcion = $primerMotivo?->descripcion ?? '';

        // Fechas por defecto sugeridas: evento concluido recientemente
        $this->fecha_ini = now()->subMinutes(30)->format('Y-m-d\TH:i');
        $this->fecha_fin = now()->subMinutes(5)->format('Y-m-d\TH:i');

        $this->showCreateModal = true;
    }

    public function cerrarModalCrear(): void
    {
        $this->showCreateModal = false;
        $this->reset(['descripcion', 'fecha_ini', 'fecha_fin', 'cufd_evento_id']);
        $this->resetErrorBag();
    }

    public function guardarEvento(SiatFacturacionService $siatService): void
    {
        abort_if(! Auth::user()?->tienePermiso('sincronizacion.eventos.crear'), 403, 'No tiene permiso para registrar eventos significativos.');

        $this->validate([
            'punto_venta_id' => 'required|exists:punto_ventas,id',
            'tipo_evento_significativo_id' => 'required|exists:tipo_evento_significativos,id',
            'fecha_ini' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_ini',
            'descripcion' => 'required|string|min:4|max:500',
        ], [
            'punto_venta_id.required' => 'Debe seleccionar un Punto de Venta.',
            'tipo_evento_significativo_id.required' => 'Debe clasificar el motivo del evento.',
            'fecha_ini.required' => 'La fecha de inicio es requerida.',
            'fecha_fin.required' => 'La fecha de fin es requerida.',
            'fecha_fin.after' => 'La fecha de fin del evento debe ser posterior a la fecha de inicio.',
            'descripcion.required' => 'La descripción o justificación es obligatoria.',
        ]);

        $res = $siatService->registrarEventoSignificativo(
            $this->tipo_evento_significativo_id,
            $this->punto_venta_id,
            $this->fecha_ini,
            $this->fecha_fin,
            $this->descripcion,
            $this->cufd_evento_id
        );

        if ($res['success']) {
            $this->cerrarModalCrear();
            $msg = "Evento Significativo registrado y homologado ante el SIAT con Código de Recepción N° {$res['codigo_recepcion']}.";
            session()->flash('message', $msg);
            $this->dispatch('swal-alert', icon: 'success', title: '¡Evento Transmitido!', message: $msg);
        } else {
            $this->dispatch('swal-alert', icon: 'error', title: 'Rechazo SIAT', message: $res['mensaje']);
        }
    }

    public function verDetalle(int $id): void
    {
        abort_if(! Auth::user()?->tienePermiso('sincronizacion.eventos.ver'), 403, 'No tiene permiso para consultar eventos significativos.');

        $this->detalleEventoId = $id;
        $this->showDetalleModal = true;
    }

    public function cerrarModalDetalle(): void
    {
        $this->showDetalleModal = false;
        $this->detalleEventoId = null;
    }

    public function abrirModalPaquete(int $eventoId): void
    {
        abort_if(! Auth::user()?->tienePermiso('sincronizacion.paquetes.enviar'), 403, 'No tiene permiso para gestionar paquetes de contingencia.');

        $this->paqueteEventoId = $eventoId;
        $this->showPaqueteModal = true;
    }

    public function cerrarModalPaquete(): void
    {
        $this->showPaqueteModal = false;
        $this->paqueteEventoId = null;
    }

    public function enviarPaquete(SiatFacturacionService $siatService): void
    {
        abort_if(! Auth::user()?->tienePermiso('sincronizacion.paquetes.enviar'), 403, 'No tiene permiso para enviar paquetes al SIAT.');

        if (! $this->paqueteEventoId) {
            $this->dispatch('swal-alert', icon: 'warning', title: 'Evento no seleccionado', message: 'Debe seleccionar un evento significativo para empaquetar.');

            return;
        }

        try {
            $res = $siatService->enviarPaqueteContingencia($this->paqueteEventoId);

            if ($res['success']) {
                $msg = $res['mensaje'] ?? 'Paquete de facturas transmitido con éxito al SIAT.';
                $titulo = ($res['validado_inmediato'] ?? false) ? '¡Paquete Transmitido y Validado!' : '¡Paquete Transmitido!';
                session()->flash('message', $msg);
                $this->dispatch('swal-alert', icon: 'success', title: $titulo, message: $msg);
            } else {
                $this->dispatch('swal-alert', icon: 'error', title: 'Error al Transmitir Paquete', message: $res['mensaje'] ?? 'Rechazo del SIAT');
            }
        } catch (\Throwable $e) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Excepción al Transmitir', message: $e->getMessage());
        }
    }

    public function validarPaquete(int $paqueteId, SiatFacturacionService $siatService): void
    {
        abort_if(! Auth::user()?->tienePermiso('sincronizacion.paquetes.validar'), 403, 'No tiene permiso para validar paquetes en el SIAT.');

        try {
            $res = $siatService->validarPaqueteFactura($paqueteId);

            if ($res['success']) {
                $msg = $res['mensaje'] ?? 'Paquete validado y aceptado por el SIAT.';
                session()->flash('message', $msg);
                $this->dispatch('swal-alert', icon: 'success', title: '¡Paquete Validado!', message: $msg);
            } else {
                $esProcesando = ($res['codigo_estado'] ?? null) == 901;
                $icono = $esProcesando ? 'info' : 'warning';
                $titulo = $esProcesando ? 'Paquete en Proceso (901 PENDIENTE)' : 'Respuesta del SIAT';
                $this->dispatch('swal-alert', icon: $icono, title: $titulo, message: $res['mensaje'] ?? 'El SIAT no pudo validar el paquete.');
            }
        } catch (\Throwable $e) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Error al Validar Paquete', message: 'Falla de comunicación o respuesta: '.$e->getMessage());
        }
    }

    public function validarTodosLosPaquetes(SiatFacturacionService $siatService): void
    {
        abort_if(! Auth::user()?->tienePermiso('sincronizacion.paquetes.validar'), 403, 'No tiene permiso para validar paquetes en el SIAT.');

        $paquetesPendientes = PaqueteFactura::whereIn('codigo_estado', [901, null])
            ->orWhere('estado', 'PENDIENTE')
            ->get();

        if ($paquetesPendientes->isEmpty()) {
            $this->dispatch('swal-alert', icon: 'info', title: 'Sin paquetes pendientes', message: 'No existen paquetes con estado PENDIENTE de validación en la base de datos.');

            return;
        }

        $validados = 0;
        $observados = 0;
        $erroresDetalle = [];

        try {
            foreach ($paquetesPendientes as $paq) {
                $res = $siatService->validarPaqueteFactura($paq->id);
                if ($res['success']) {
                    $validados++;
                } else {
                    $observados++;
                    if (! empty($res['mensaje'])) {
                        $erroresDetalle[] = "Paquete #{$paq->id}: ".$res['mensaje'];
                    }
                }
                usleep(100000); // 100ms pausa anti-saturación
            }

            if ($validados > 0 && $observados === 0) {
                $msg = "Se validaron exitosamente los {$validados} paquetes en el SIAT.";
                $this->dispatch('swal-alert', icon: 'success', title: '¡Validación Completa!', message: $msg);
            } else {
                $msg = "Procesados: {$paquetesPendientes->count()} paquetes.\n• Validados: {$validados}\n• Pendientes/Observados: {$observados}";
                if (! empty($erroresDetalle)) {
                    $msg .= "\n\nDetalle recibido del SIAT:\n".implode("\n", array_slice($erroresDetalle, 0, 3));
                }
                $this->dispatch('swal-alert', icon: $validados > 0 ? 'warning' : 'info', title: 'Resultado de Validación', message: $msg);
            }
        } catch (\Throwable $e) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Error en Validación Masiva', message: 'Ocurrió un error inesperado: '.$e->getMessage());
        }
    }

    public function render(): View
    {
        abort_if(! Auth::user()?->tienePermiso('sincronizacion.eventos.ver'), 403, 'No tienes permiso para ver contingencias y eventos significativos.');

        $query = EventoSignificativo::query()
            ->with(['tipoEvento', 'puntoVenta.sucursal', 'usuarioCreador', 'cufdActivo', 'cufdEvento', 'paquetes', 'facturas'])
            ->when($this->search !== '', function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('codigo_recepcion', 'like', $term)
                        ->orWhere('descripcion', 'like', $term)
                        ->orWhereHas('tipoEvento', fn ($tq) => $tq->where('descripcion', 'like', $term))
                        ->orWhereHas('puntoVenta', fn ($pq) => $pq->where('nombre', 'like', $term));
                });
            })
            ->when($this->filtroPuntoVentaId !== null, fn ($q) => $q->where('punto_venta_id', $this->filtroPuntoVentaId))
            ->when($this->filtroMotivoId !== null, fn ($q) => $q->where('tipo_evento_significativo_id', $this->filtroMotivoId))
            ->when($this->filtroEstado !== '', fn ($q) => $q->where('estado', $this->filtroEstado))
            ->orderBy('id', 'desc');

        $eventos = $query->paginate($this->perPage);

        $puntosVenta = PuntoVenta::with('sucursal')->orderBy('codigo', 'asc')->get();
        $tiposEvento = TipoEventoSignificativo::orderBy('codigo_clasificador', 'asc')->get();

        // KPIs de eventos
        $totalEventos = EventoSignificativo::count();
        $eventosHoy = EventoSignificativo::whereDate('created_at', today())->count();
        $eventosPv1 = EventoSignificativo::whereHas('puntoVenta', fn ($q) => $q->where('codigo', 1))->count();
        $eventosPv0 = EventoSignificativo::whereHas('puntoVenta', fn ($q) => $q->where('codigo', 0))->count();

        $eventoDetalle = $this->detalleEventoId
            ? EventoSignificativo::with(['tipoEvento', 'puntoVenta.sucursal', 'usuarioCreador', 'cufdActivo', 'cufdEvento'])->find($this->detalleEventoId)
            : null;

        $eventoPaquete = $this->paqueteEventoId
            ? EventoSignificativo::with(['tipoEvento', 'puntoVenta.sucursal', 'paquetes.cufd', 'paquetes.usuarioCreador', 'paquetes.facturas.cliente'])->find($this->paqueteEventoId)
            : null;

        $facturasContingencia = $eventoPaquete
            ? Factura::with('cliente')
                ->where('punto_venta_id', $eventoPaquete->punto_venta_id)
                ->where('estado', 'PENDIENTE_ENVIO')
                ->whereNull('paquete_id')
                ->where(function ($q) use ($eventoPaquete) {
                    $q->where('evento_significativo_id', $eventoPaquete->id)
                        ->orWhere(function ($sub) use ($eventoPaquete) {
                            $sub->whereNull('evento_significativo_id');
                            if ($eventoPaquete->cufd_evento_id) {
                                $sub->where(function ($cufdQ) use ($eventoPaquete) {
                                    $cufdQ->where('cufd_id', $eventoPaquete->cufd_evento_id)
                                        ->orWhere(function ($dateQ) use ($eventoPaquete) {
                                            $dateQ->where('fecha', '>=', $eventoPaquete->fecha_ini)
                                                ->where('fecha', '<=', $eventoPaquete->fecha_fin);
                                        });
                                });
                            } else {
                                $sub->where('fecha', '>=', $eventoPaquete->fecha_ini)
                                    ->where('fecha', '<=', $eventoPaquete->fecha_fin);
                            }
                        });
                })
                ->orderBy('id', 'asc')
                ->get()
            : collect();

        return view('livewire.sincronizacion.eventos-significativos', [
            'eventos' => $eventos,
            'puntosVenta' => $puntosVenta,
            'tiposEvento' => $tiposEvento,
            'eventoDetalle' => $eventoDetalle,
            'eventoPaquete' => $eventoPaquete,
            'facturasContingencia' => $facturasContingencia,
            'kpis' => [
                'total' => $totalEventos,
                'hoy' => $eventosHoy,
                'pv1' => $eventosPv1,
                'pv0' => $eventosPv0,
            ],
        ]);
    }
}
