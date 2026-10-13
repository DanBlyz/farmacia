<?php

namespace App\Livewire\Sincronizacion;

use App\Models\EventoSignificativo;
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

    public function render(): View
    {
        abort_if(! Auth::user()?->tienePermiso('sincronizacion.eventos.ver'), 403, 'No tienes permiso para ver contingencias y eventos significativos.');

        $query = EventoSignificativo::query()
            ->with(['tipoEvento', 'puntoVenta.sucursal', 'usuarioCreador', 'cufdActivo', 'cufdEvento'])
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

        return view('livewire.sincronizacion.eventos-significativos', [
            'eventos' => $eventos,
            'puntosVenta' => $puntosVenta,
            'tiposEvento' => $tiposEvento,
            'eventoDetalle' => $eventoDetalle,
            'kpis' => [
                'total' => $totalEventos,
                'hoy' => $eventosHoy,
                'pv1' => $eventosPv1,
                'pv0' => $eventosPv0,
            ],
        ]);
    }
}
