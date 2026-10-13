<?php

namespace App\Livewire\Ventas;

use App\Models\Caja;
use App\Models\Factura;
use App\Models\Lote;
use App\Models\MotivoAnulacion;
use App\Models\Movimiento;
use App\Models\Sucursal;
use App\Services\SiatFacturacionService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class HistorialVentas extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public int $perPage = 15;

    public ?int $filtroSucursalId = null;

    public string $filtroEstado = '';

    public ?string $fechaInicio = null;

    public ?string $fechaFin = null;

    // Modal de Detalle de Factura
    public bool $showDetalleModal = false;

    public ?int $facturaDetalleId = null;

    // Modal de Reimpresión de Ticket
    public bool $showImpresionModal = false;

    public ?int $facturaImpresionId = null;

    // Modal de Anulación
    public bool $showAnulacionModal = false;

    public ?int $facturaAnularId = null;

    public int $motivoAnulacionId = 1; // 1 = Factura mal emitida, 2 = Datos incorrectos, 3 = Factura devuelta

    public string $motivoAnulacionTexto = '';

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

    public function verDetalle(int $id): void
    {
        abort_if(! Auth::user()?->tienePermiso('ventas.facturas.ver'), 403, 'No tiene permiso para ver detalles de facturas.');
        $this->facturaDetalleId = $id;
        $this->showDetalleModal = true;
    }

    public function reimprimirFactura(int $id): void
    {
        abort_if(! Auth::user()?->tienePermiso('ventas.facturas.imprimir'), 403, 'No tiene permiso para imprimir facturas.');
        $this->facturaImpresionId = $id;
        $this->showImpresionModal = true;
        $this->dispatch('abrir-ticket-flotante', url: route('ventas.factura.imprimir', $id));
    }

    public function abrirModalAnulacion(int $id): void
    {
        abort_if(! Auth::user()?->tienePermiso('ventas.facturas.anular'), 403, 'No tiene permiso para anular facturas.');

        $factura = Factura::findOrFail($id);
        if ($factura->estado === 'ANULADA') {
            $this->dispatch('swal-alert', icon: 'info', title: 'Factura Ya Anulada', message: 'Esta factura ya se encuentra en estado anulada.');

            return;
        }

        $this->facturaAnularId = $id;
        $this->motivoAnulacionId = 1;
        $this->motivoAnulacionTexto = 'Anulación solicitada por el cajero / devolución';
        $this->showAnulacionModal = true;
    }

    public function confirmarAnulacion(SiatFacturacionService $siatService): void
    {
        abort_if(! Auth::user()?->tienePermiso('ventas.facturas.anular'), 403, 'No tiene permiso para anular facturas.');

        $this->validate([
            'facturaAnularId' => 'required|exists:facturas,id',
            'motivoAnulacionId' => 'required|integer',
            'motivoAnulacionTexto' => 'required|string|min:4|max:255',
        ]);

        $factura = Factura::with(['detalles', 'caja', 'pagos'])->findOrFail($this->facturaAnularId);

        if ($factura->estado === 'ANULADA') {
            $this->showAnulacionModal = false;
            $this->dispatch('swal-alert', icon: 'info', title: 'Factura Ya Anulada', message: 'La factura ya fue anulada previamente.');

            return;
        }

        // 1. Invocar anulación ante el WebService del SIAT si aplica
        $resSiat = $siatService->anularFacturaSiat($factura, $this->motivoAnulacionId);
        if (! $resSiat['success']) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Rechazo SIAT', message: $resSiat['mensaje']);

            return;
        }

        // 2. Transacción de Base de Datos: Regularización de Stock y Kardex
        DB::beginTransaction();
        try {
            $fechaAnulacion = now();

            // Actualizar factura
            $factura->estado = 'ANULADA';
            $factura->fecha_anulacion = $fechaAnulacion;
            $factura->motivo_anulacion_id = $this->motivoAnulacionId;
            $factura->motivo_anulacion_descripcion = trim($this->motivoAnulacionTexto);
            $factura->usuario_modificador_id = Auth::id();
            $factura->save();

            // Devolución inmediata a inventario de cada lote intervenido
            foreach ($factura->detalles as $det) {
                if ($det->lote_id) {
                    $lote = Lote::where('id', $det->lote_id)->lockForUpdate()->first();
                    if ($lote) {
                        $cantDevuelta = (float) $det->cantidad;
                        $nuevoStock = (float) $lote->stock_actual + $cantDevuelta;
                        $lote->stock_actual = $nuevoStock;
                        if ($lote->estado === 'AGOTADO' && $nuevoStock > 0) {
                            $lote->estado = 'ACTIVO';
                        }
                        $lote->usuario_modificador_id = Auth::id();
                        $lote->save();

                        // Asentar movimiento de regularización en Kardex
                        Movimiento::create([
                            'producto_id' => $det->producto_id,
                            'sucursal_id' => $factura->sucursal_id,
                            'lote_id' => $lote->id,
                            'tipo_movimiento' => 'ANULACION_VENTA',
                            'ingreso' => $cantDevuelta,
                            'salida' => 0,
                            'saldo_stock' => $nuevoStock,
                            'numero_lote' => $lote->numero_lote,
                            'fecha_caducidad' => $lote->fecha_vencimiento,
                            'fecha' => $fechaAnulacion,
                            'descripcion' => "Devolución a inventario por anulación de Factura N° {$factura->numero_factura} [Motivo: {$this->motivoAnulacionTexto}]",
                            'precio_compra' => (float) $lote->precio_compra,
                            'precio_venta' => (float) $det->precio,
                            'estado' => 'ACTIVO',
                            'usuario_creador_id' => Auth::id(),
                        ]);
                    }
                }
            }

            // Ajustar la caja asociada si sigue abierta
            if ($factura->caja_id) {
                $caja = Caja::find($factura->caja_id);
                if ($caja && in_array($caja->estado, ['ABIERTA', 'Abierta'])) {
                    // Descontar del total de ventas y saldo
                    $totalFactura = (float) $factura->total;
                    $caja->total_venta = max(0, (float) $caja->total_venta - $totalFactura);

                    // Ajustar por tipo de pago registrado
                    foreach ($factura->pagos as $p) {
                        $montoPagoNeto = (float) $p->monto - (float) ($p->cambio ?? 0);
                        if ($p->tipo_pago === 'EFECTIVO') {
                            $caja->venta_contado = max(0, (float) $caja->venta_contado - $montoPagoNeto);
                        } elseif ($p->tipo_pago === 'QR') {
                            $caja->total_qr = max(0, (float) $caja->total_qr - $montoPagoNeto);
                        } elseif ($p->tipo_pago === 'TRANSFERENCIA') {
                            $caja->total_transferencia = max(0, (float) $caja->total_transferencia - $montoPagoNeto);
                        }
                    }
                    $caja->saldo = (float) $caja->monto_apertura + (float) $caja->venta_contado + (float) $caja->otro_ingreso - (float) $caja->total_salida;
                    $caja->save();
                }
            }

            DB::commit();

            $this->showAnulacionModal = false;
            $msg = "Factura N° {$factura->numero_factura} anulada exitosamente. Stock devuelto a los lotes físicos.";
            session()->flash('message', $msg);
            $this->dispatch('swal-alert', icon: 'success', title: 'Factura Anulada', message: $msg);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al anular factura: '.$e->getMessage());
            $this->dispatch('swal-alert', icon: 'error', title: 'Error en Anulación', message: $e->getMessage());
        }
    }

    /**
     * Reintenta el envío de una factura en contingencia / pendiente de envío ante el SIAT.
     */
    public function reintentarEnvioSiat(int $id, SiatFacturacionService $siatService): void
    {
        abort_if(! Auth::user()?->tienePermiso('ventas.facturas.ver'), 403, 'No tiene permiso para gestionar facturas.');

        $factura = Factura::with(['detalles', 'cufd', 'sucursal', 'puntoVenta'])->findOrFail($id);

        if ($factura->estado === 'VALIDADA') {
            $this->dispatch('swal-alert', icon: 'info', title: 'Factura Validada', message: 'Esta factura ya fue validada por el SIAT.');

            return;
        }

        // Si la diferencia con la fecha original supera 4 minutos, actualizamos fecha y CUF para permitir recepción en línea
        $diferenciaSegundos = abs(now()->diffInSeconds($factura->fecha));
        if ($diferenciaSegundos > 240) {
            $fechaActual = now();
            $factura->fecha = $fechaActual;
            $fechaCadena = $fechaActual->format('YmdHis').'000';
            $factura->cuf = $siatService->calcularCuf(
                config('siat.nit'),
                $fechaCadena,
                (int) $factura->sucursal->codigo_sucursal,
                (int) config('siat.codigo_modalidad', 2),
                1,
                1,
                1,
                (int) $factura->numero_factura,
                (int) $factura->puntoVenta->codigo,
                $factura->cufd->codigo_control
            );
        }

        $xml = $siatService->generarXmlFactura($factura, $factura->detalles, $factura->cufd, $factura->sucursal, $factura->puntoVenta);
        $res = $siatService->enviarFacturaSiat($factura, $xml);

        if ($res['success']) {
            $factura->productos_xml = $xml;
            $factura->estado = 'VALIDADA';
            $factura->codigo_recepcion = $res['codigo_recepcion'];
            $factura->codigo_descripcion = 'VALIDADA';
            $factura->save();

            $this->dispatch('swal-alert', icon: 'success', title: 'Envío Exitoso', message: "Factura N° {$factura->numero_factura} validada correctamente ante el SIAT.");
        } else {
            $factura->codigo_descripcion = $res['mensaje'] ?? 'Error al enviar a SIAT';
            $factura->save();

            $this->dispatch('swal-alert', icon: 'error', title: 'Rechazo SIAT', message: $res['mensaje'] ?? 'No se pudo validar la factura.');
        }
    }

    public function render(): View
    {
        $query = Factura::with(['cliente', 'sucursal', 'puntoVenta', 'usuarioCreador'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('numero_factura', 'like', "%{$this->search}%")
                        ->orWhere('nit', 'like', "%{$this->search}%")
                        ->orWhere('razon_social', 'like', "%{$this->search}%")
                        ->orWhere('cuf', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filtroSucursalId, fn ($q) => $q->where('sucursal_id', $this->filtroSucursalId))
            ->when($this->filtroEstado, fn ($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->fechaInicio, fn ($q) => $q->whereDate('fecha', '>=', $this->fechaInicio))
            ->when($this->fechaFin, fn ($q) => $q->whereDate('fecha', '<=', $this->fechaFin))
            ->orderBy('id', 'desc');

        $facturas = $query->paginate($this->perPage);

        $sucursales = Sucursal::orderBy('codigo_sucursal', 'asc')->get();
        $facturaSeleccionada = $this->facturaDetalleId ? Factura::with(['detalles.lote', 'pagos', 'cliente', 'sucursal', 'usuarioCreador'])->find($this->facturaDetalleId) : null;
        $facturaImpresion = $this->facturaImpresionId ? Factura::with(['detalles', 'pagos', 'cliente', 'sucursal'])->find($this->facturaImpresionId) : null;
        $motivosAnulacion = MotivoAnulacion::all();

        return view('livewire.ventas.historial-ventas', [
            'facturas' => $facturas,
            'sucursales' => $sucursales,
            'facturaSeleccionada' => $facturaSeleccionada,
            'facturaImpresion' => $facturaImpresion,
            'motivosAnulacion' => $motivosAnulacion,
        ]);
    }
}
