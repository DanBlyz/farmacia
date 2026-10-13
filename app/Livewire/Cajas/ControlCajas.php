<?php

namespace App\Livewire\Cajas;

use App\Models\Caja;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ControlCajas extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public int $perPage = 10;

    public ?int $filtroSucursalId = null;

    public string $filtroEstado = '';

    public ?string $fechaInicio = null;

    public ?string $fechaFin = null;

    // Modal de Apertura de Caja
    public bool $showAperturaModal = false;

    public ?int $aperturaSucursalId = null;

    public ?int $aperturaPuntoVentaId = null;

    public ?string $aperturaSucursalNombre = '';

    public ?string $aperturaPuntoVentaNombre = '';

    public float|string $montoApertura = 0;

    public string $descripcionApertura = '';

    // Modal de Cierre de Caja
    public bool $showCierreModal = false;

    public ?int $cajaIdACerrar = null;

    public float|string $montoCierreEfectivo = 0;

    public string $descripcionCierre = '';

    // Modal de Movimiento Menor (Ingreso / Egreso)
    public bool $showMovimientoModal = false;

    public string $tipoMovimientoCaja = 'INGRESO'; // 'INGRESO' o 'SALIDA'

    public float|string $montoMovimientoCaja = 0;

    public string $motivoMovimientoCaja = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroSucursalId(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatedFechaInicio(): void
    {
        $this->resetPage();
    }

    public function updatedFechaFin(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->sincronizarAsignacionUsuario();
    }

    public function sincronizarAsignacionUsuario(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $puntoVenta = $user->puntoVenta ?? ($user->punto_venta_id ? PuntoVenta::with('sucursal')->find($user->punto_venta_id) : null);

        // Fallback asistido para Administradores sin punto de venta previo
        if (! $puntoVenta && $user->esAdmin()) {
            $puntoVenta = PuntoVenta::with('sucursal')->where('estado', 'ACTIVO')->first() ?? PuntoVenta::with('sucursal')->first();
            if ($puntoVenta) {
                $user->punto_venta_id = $puntoVenta->id;
                $user->save();
            }
        }

        if ($puntoVenta && $puntoVenta->sucursal_id) {
            $this->aperturaPuntoVentaId = $puntoVenta->id;
            $this->aperturaSucursalId = $puntoVenta->sucursal_id;
            $this->aperturaSucursalNombre = $puntoVenta->sucursal?->nombre ?? ('Sucursal '.$puntoVenta->sucursal_id);
            $this->aperturaPuntoVentaNombre = $puntoVenta->nombre ?? ('Punto Venta '.$puntoVenta->codigo);
        }
    }

    public function abrirModalApertura(): void
    {
        abort_if(! Auth::user()?->tienePermiso('cajas.control.abrir'), 403, 'No tiene permiso para abrir caja.');

        $cajaAbierta = Caja::obtenerCajaAbierta(Auth::id());
        if ($cajaAbierta) {
            $this->dispatch('swal-alert', icon: 'warning', title: 'Caja ya Abierta', message: 'Usted ya tiene una caja abierta actualmente. Debe cerrarla antes de aperturar una nueva.');

            return;
        }

        $this->sincronizarAsignacionUsuario();

        if (! $this->aperturaPuntoVentaId || ! $this->aperturaSucursalId) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Sin Punto de Venta Asignado', message: 'Su usuario no tiene un Punto de Venta ni Sucursal asignados en su perfil. Contacte al administrador para configurar su asignación.');

            return;
        }

        $this->montoApertura = 0;
        $this->descripcionApertura = 'Apertura de turno';
        $this->showAperturaModal = true;
    }

    public function guardarApertura(): void
    {
        abort_if(! Auth::user()?->tienePermiso('cajas.control.abrir'), 403, 'No tiene permiso para abrir caja.');

        $this->sincronizarAsignacionUsuario();

        if (! $this->aperturaPuntoVentaId || ! $this->aperturaSucursalId) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Sin Punto de Venta', message: 'No se puede aperturar caja sin un Punto de Venta y Sucursal asignados a su usuario.');

            return;
        }

        $this->validate([
            'montoApertura' => 'required|numeric|min:0',
            'descripcionApertura' => 'nullable|string|max:255',
        ], [
            'montoApertura.required' => 'El monto inicial es obligatorio.',
            'montoApertura.min' => 'El monto inicial no puede ser negativo.',
        ]);

        $cajaExistente = Caja::obtenerCajaAbierta(Auth::id());
        if ($cajaExistente) {
            $this->addError('montoApertura', 'Ya cuenta con una caja abierta activa.');

            return;
        }

        Caja::create([
            'usuario_creador_id' => Auth::id(),
            'usuario_id' => Auth::id(),
            'sucursal_id' => $this->aperturaSucursalId,
            'punto_venta_id' => $this->aperturaPuntoVentaId,
            'fecha_apertura' => now(),
            'monto_apertura' => (float) $this->montoApertura,
            'descripcion' => trim($this->descripcionApertura) ?: 'Apertura de turno',
            'total_venta' => 0,
            'venta_contado' => 0,
            'venta_credito' => 0,
            'otro_ingreso' => 0,
            'total_ingreso' => (float) $this->montoApertura,
            'total_qr' => 0,
            'total_transferencia' => 0,
            'total_salida' => 0,
            'saldo' => (float) $this->montoApertura,
            'estado' => 'ABIERTA',
        ]);

        $this->showAperturaModal = false;
        $msg = 'Caja aperturada exitosamente con Bs. '.number_format((float) $this->montoApertura, 2);
        session()->flash('message', $msg);
        $this->dispatch('swal-alert', icon: 'success', title: '¡Caja Aperturada!', message: $msg);
    }

    public function abrirModalCierre(?int $id = null): void
    {
        abort_if(! Auth::user()?->tienePermiso('cajas.control.cerrar'), 403, 'No tiene permiso para cerrar caja.');

        $caja = $id ? Caja::find($id) : Caja::obtenerCajaAbierta(Auth::id());

        if (! $caja || ! in_array($caja->estado, ['ABIERTA', 'Abierta'])) {
            $this->dispatch('swal-alert', icon: 'warning', title: 'Sin Caja Abierta', message: 'No se encontró una caja abierta para cerrar.');

            return;
        }

        $this->cajaIdACerrar = $caja->id;
        $esperadoEfectivo = (float) $caja->monto_apertura + (float) $caja->venta_contado + (float) $caja->otro_ingreso - (float) $caja->total_salida;
        $this->montoCierreEfectivo = $esperadoEfectivo;
        $this->descripcionCierre = 'Cierre regular de turno';
        $this->showCierreModal = true;
    }

    public function guardarCierre(): void
    {
        abort_if(! Auth::user()?->tienePermiso('cajas.control.cerrar'), 403, 'No tiene permiso para cerrar caja.');

        $this->validate([
            'cajaIdACerrar' => 'required|exists:cajas,id',
            'montoCierreEfectivo' => 'required|numeric|min:0',
            'descripcionCierre' => 'nullable|string|max:255',
        ]);

        $caja = Caja::findOrFail($this->cajaIdACerrar);

        if (! in_array($caja->estado, ['ABIERTA', 'Abierta'])) {
            $this->addError('cajaIdACerrar', 'Esta caja ya se encuentra cerrada.');

            return;
        }

        $esperadoEfectivo = (float) $caja->monto_apertura + (float) $caja->venta_contado + (float) $caja->otro_ingreso - (float) $caja->total_salida;
        $montoFisico = (float) $this->montoCierreEfectivo;
        $diferencia = $montoFisico - $esperadoEfectivo;

        $desc = trim($this->descripcionCierre);
        if ($diferencia != 0) {
            $tipoDif = $diferencia > 0 ? 'Sobrante' : 'Faltante';
            $desc .= " [Arqueo: {$tipoDif} de Bs. ".number_format(abs($diferencia), 2).']';
        }

        $caja->update([
            'usuario_modificador_id' => Auth::id(),
            'fecha_cierre' => now(),
            'monto_cierre' => $montoFisico,
            'descripcion_cierre' => $desc,
            'saldo' => $montoFisico,
            'estado' => 'CERRADA',
        ]);

        $this->showCierreModal = false;
        $msg = 'Caja cerrada exitosamente. Monto entregado: Bs. '.number_format($montoFisico, 2);
        session()->flash('message', $msg);
        $this->dispatch('swal-alert', icon: 'success', title: '¡Caja Cerrada!', message: $msg);
    }

    public function abrirModalMovimiento(): void
    {
        abort_if(! Auth::user()?->tienePermiso('cajas.control.movimientos'), 403, 'No tiene permiso para registrar movimientos de caja.');

        $caja = Caja::obtenerCajaAbierta(Auth::id());
        if (! $caja) {
            $this->dispatch('swal-alert', icon: 'warning', title: 'Caja no Aperturada', message: 'Debe aperturar su caja antes de asentar ingresos o egresos.');

            return;
        }

        $this->tipoMovimientoCaja = 'INGRESO';
        $this->montoMovimientoCaja = 10;
        $this->motivoMovimientoCaja = '';
        $this->showMovimientoModal = true;
    }

    public function guardarMovimiento(): void
    {
        abort_if(! Auth::user()?->tienePermiso('cajas.control.movimientos'), 403, 'No tiene permiso para registrar movimientos de caja.');

        $this->validate([
            'tipoMovimientoCaja' => 'required|in:INGRESO,SALIDA',
            'montoMovimientoCaja' => 'required|numeric|min:0.5',
            'motivoMovimientoCaja' => 'required|string|min:4|max:255',
        ], [
            'montoMovimientoCaja.min' => 'El monto debe ser al menos de 0.50 Bs.',
            'motivoMovimientoCaja.required' => 'Debe detallar el motivo del movimiento.',
            'motivoMovimientoCaja.min' => 'El motivo debe ser más explicativo.',
        ]);

        $caja = Caja::obtenerCajaAbierta(Auth::id());
        if (! $caja) {
            $this->addError('tipoMovimientoCaja', 'No tiene caja abierta activa.');

            return;
        }

        $monto = (float) $this->montoMovimientoCaja;

        DB::transaction(function () use ($caja, $monto) {
            if ($this->tipoMovimientoCaja === 'INGRESO') {
                $caja->otro_ingreso = (float) $caja->otro_ingreso + $monto;
                $caja->total_ingreso = (float) $caja->total_ingreso + $monto;
            } else {
                $caja->total_salida = (float) $caja->total_salida + $monto;
            }

            $caja->saldo = (float) $caja->monto_apertura + (float) $caja->venta_contado + (float) $caja->otro_ingreso - (float) $caja->total_salida;
            $caja->usuario_modificador_id = Auth::id();
            $caja->save();
        });

        $this->showMovimientoModal = false;
        $msg = 'Movimiento registrado con éxito: Bs. '.number_format($monto, 2);
        session()->flash('message', $msg);
        $this->dispatch('swal-alert', icon: 'success', title: '¡Movimiento Registrado!', message: $msg);
    }

    public function render(): View
    {
        $cajaActiva = Caja::with(['sucursal', 'puntoVenta', 'usuario'])
            ->where('usuario_id', Auth::id())
            ->whereIn('estado', ['ABIERTA', 'Abierta'])
            ->latest('fecha_apertura')
            ->first();

        $query = Caja::with(['sucursal', 'puntoVenta', 'usuario'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('descripcion', 'like', "%{$this->search}%")
                        ->orWhere('descripcion_cierre', 'like', "%{$this->search}%")
                        ->orWhereHas('usuario', function ($u) {
                            $u->where('name', 'like', "%{$this->search}%")
                                ->orWhere('usuario', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->filtroSucursalId, fn ($q) => $q->where('sucursal_id', $this->filtroSucursalId))
            ->when($this->filtroEstado, fn ($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->fechaInicio, fn ($q) => $q->whereDate('fecha_apertura', '>=', $this->fechaInicio))
            ->when($this->fechaFin, fn ($q) => $q->whereDate('fecha_apertura', '<=', $this->fechaFin))
            ->orderBy('id', 'desc');

        $cajas = $query->paginate($this->perPage);

        $sucursales = Sucursal::orderBy('codigo_sucursal', 'asc')->get();
        $puntosVenta = PuntoVenta::when($this->aperturaSucursalId, fn ($q) => $q->where('sucursal_id', $this->aperturaSucursalId))
            ->orderBy('codigo', 'asc')
            ->get();

        return view('livewire.cajas.control-cajas', [
            'cajas' => $cajas,
            'cajaActiva' => $cajaActiva,
            'sucursales' => $sucursales,
            'puntosVenta' => $puntosVenta,
        ]);
    }
}
