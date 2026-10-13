<?php

namespace App\Livewire\Inventario;

use App\Models\Lote;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Kardex extends Component
{
    use WithPagination;

    // Filtros de búsqueda
    public string $search = '';

    public int $perPage = 15;

    public ?int $filtroSucursalId = null;

    public ?int $filtroProductoId = null;

    public string $filtroTipoMovimiento = '';

    public ?string $fechaInicio = null;

    public ?string $fechaFin = null;

    // Modal de Registro Manual de Ajuste en Kardex
    public bool $showModal = false;

    public ?int $nuevoSucursalId = null;

    public ?int $nuevoProductoId = null;

    public string $productoSearch = '';

    public ?int $nuevoLoteId = null;

    public bool $esNuevoLote = false;

    public string $nuevoNumeroLote = '';

    public string $nuevaFechaVencimiento = '';

    public float|string $nuevoPrecioCompra = 0;

    public float|string $nuevoPrecioVenta = 0;

    public string $nuevoTipoMovimiento = 'AJUSTE_POSITIVO'; // 'AJUSTE_POSITIVO', 'AJUSTE_NEGATIVO', 'BAJA_VENCIMIENTO'

    public float|string $nuevaCantidad = 1;

    public string $nuevoMotivo = '';

    public function generarCodigoNuevoLote(): void
    {
        $ultimoId = (int) Lote::withTrashed()->max('id') + 1;
        $this->nuevoNumeroLote = 'LOT-'.date('Ym').'-'.str_pad((string) $ultimoId, 5, '0', STR_PAD_LEFT);
    }

    public function cambiarModoLote(bool $esNuevo): void
    {
        $this->esNuevoLote = $esNuevo;
        if ($esNuevo && empty($this->nuevoNumeroLote)) {
            $this->generarCodigoNuevoLote();
        }
    }

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

    public function updatedFiltroProductoId(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroTipoMovimiento(): void
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

    public function updatedNuevoProductoId(): void
    {
        $this->nuevoLoteId = null;
    }

    public function updatedNuevoSucursalId(): void
    {
        $this->nuevoLoteId = null;
    }

    public function mount(): void
    {
        $primeraSucursal = Sucursal::orderBy('codigo_sucursal', 'asc')->first();
        if ($primeraSucursal) {
            $this->nuevoSucursalId = $primeraSucursal->id;
        }
    }

    public function abrirModalAjuste(): void
    {
        abort_if(! Auth::user()?->tienePermiso('inventario.kardex.crear'), 403, 'No tiene permiso para registrar ajustes de Kardex.');

        $this->resetAjusteForm();
        $primeraSucursal = Sucursal::orderBy('codigo_sucursal', 'asc')->first();
        if ($primeraSucursal) {
            $this->nuevoSucursalId = $primeraSucursal->id;
        }

        $this->showModal = true;
    }

    public function guardarAjusteKardex(): void
    {
        abort_if(! Auth::user()?->tienePermiso('inventario.kardex.crear'), 403, 'No tiene permiso para asentar ajustes en Kardex.');

        if ($this->esNuevoLote) {
            $this->validate([
                'nuevoSucursalId' => 'required|exists:sucursales,id',
                'nuevoProductoId' => 'required|exists:productos,id',
                'nuevoNumeroLote' => 'required|string|max:100',
                'nuevaFechaVencimiento' => 'required|date|after:today',
                'nuevoTipoMovimiento' => 'required|in:AJUSTE_POSITIVO,AJUSTE_NEGATIVO,BAJA_VENCIMIENTO',
                'nuevaCantidad' => 'required|numeric|min:0.01',
                'nuevoMotivo' => 'required|string|min:5|max:255',
            ], [
                'nuevoSucursalId.required' => 'Debe seleccionar la sucursal.',
                'nuevoProductoId.required' => 'Debe seleccionar el medicamento.',
                'nuevoNumeroLote.required' => 'Debe ingresar o autogenerar el número de lote.',
                'nuevaFechaVencimiento.required' => 'La fecha de vencimiento es requerida para el nuevo lote.',
                'nuevaFechaVencimiento.after' => 'La fecha de vencimiento debe ser posterior a hoy.',
                'nuevaCantidad.min' => 'La cantidad debe ser mayor a cero.',
                'nuevoMotivo.required' => 'Debe fundamentar el motivo del movimiento.',
                'nuevoMotivo.min' => 'El motivo debe ser más detallado (mínimo 5 caracteres).',
            ]);

            $prod = Producto::findOrFail($this->nuevoProductoId);
            $cantidad = (float) $this->nuevaCantidad;
            $precioCompra = (float) ($this->nuevoPrecioCompra > 0 ? $this->nuevoPrecioCompra : ($prod->precio_compra ?? 0));
            $precioVenta = (float) ($this->nuevoPrecioVenta > 0 ? $this->nuevoPrecioVenta : ($prod->precio_venta ?? 0));

            DB::transaction(function () use ($prod, $cantidad, $precioCompra, $precioVenta) {
                $lote = Lote::create([
                    'producto_id' => $prod->id,
                    'sucursal_id' => $this->nuevoSucursalId,
                    'numero_lote' => trim($this->nuevoNumeroLote),
                    'fecha_vencimiento' => $this->nuevaFechaVencimiento,
                    'stock_actual' => $cantidad,
                    'stock_minimo_alerta' => (float) ($prod->minimo_stock ?? 10),
                    'precio_compra' => $precioCompra,
                    'precio_venta' => $precioVenta,
                    'ubicacion_estante' => $prod->ubicacion_estante ? trim($prod->ubicacion_estante) : null,
                    'estado' => 'ACTIVO',
                    'usuario_creador_id' => Auth::id(),
                    'usuario_modificador_id' => Auth::id(),
                ]);

                // Actualizar el Producto maestro con los últimos precios de compra y venta del nuevo lote
                $prod->update([
                    'precio_compra' => $precioCompra,
                    'precio_venta' => $precioVenta,
                    'usuario_modificador_id' => Auth::id(),
                ]);

                Movimiento::create([
                    'producto_id' => $prod->id,
                    'sucursal_id' => $this->nuevoSucursalId,
                    'lote_id' => $lote->id,
                    'tipo_movimiento' => $this->nuevoTipoMovimiento,
                    'ingreso' => $cantidad,
                    'salida' => 0,
                    'saldo_stock' => $cantidad,
                    'numero_lote' => $lote->numero_lote,
                    'fecha_caducidad' => $lote->fecha_vencimiento,
                    'fecha' => now(),
                    'descripcion' => trim($this->nuevoMotivo),
                    'precio_compra' => $precioCompra,
                    'precio_venta' => $precioVenta,
                    'estado' => 'ACTIVO',
                    'usuario_creador_id' => Auth::id(),
                ]);

                $this->closeModal();
                $msg = 'Nuevo lote registrado e ingreso asentado en Kardex con éxito.';
                session()->flash('message', $msg);
                $this->dispatch('swal-alert', icon: 'success', title: '¡Kardex Asentado!', message: $msg);
            });

            return;
        }

        $this->validate([
            'nuevoSucursalId' => 'required|exists:sucursales,id',
            'nuevoProductoId' => 'required|exists:productos,id',
            'nuevoLoteId' => 'required|exists:lotes,id',
            'nuevoTipoMovimiento' => 'required|in:AJUSTE_POSITIVO,AJUSTE_NEGATIVO,BAJA_VENCIMIENTO',
            'nuevaCantidad' => 'required|numeric|min:0.01',
            'nuevoMotivo' => 'required|string|min:5|max:255',
        ], [
            'nuevoSucursalId.required' => 'Debe seleccionar la sucursal.',
            'nuevoProductoId.required' => 'Debe seleccionar el medicamento.',
            'nuevoLoteId.required' => 'Debe seleccionar el lote a intervenir.',
            'nuevaCantidad.min' => 'La cantidad debe ser mayor a cero.',
            'nuevoMotivo.required' => 'Debe fundamentar el motivo del movimiento.',
            'nuevoMotivo.min' => 'El motivo debe ser más detallado (mínimo 5 caracteres).',
        ]);

        $lote = Lote::where('id', $this->nuevoLoteId)
            ->where('producto_id', $this->nuevoProductoId)
            ->where('sucursal_id', $this->nuevoSucursalId)
            ->firstOrFail();

        $cantidad = (float) $this->nuevaCantidad;
        $stockActual = (float) $lote->stock_actual;

        if (in_array($this->nuevoTipoMovimiento, ['AJUSTE_NEGATIVO', 'BAJA_VENCIMIENTO']) && $cantidad > $stockActual) {
            $this->addError('nuevaCantidad', "Existencia insuficiente. El lote dispone de {$stockActual} unidades.");

            return;
        }

        DB::transaction(function () use ($lote, $cantidad, $stockActual) {
            $ingreso = 0;
            $salida = 0;

            if ($this->nuevoTipoMovimiento === 'AJUSTE_POSITIVO') {
                $nuevoStock = $stockActual + $cantidad;
                $ingreso = $cantidad;
                $lote->stock_actual = $nuevoStock;
                if ($lote->estado === 'AGOTADO' && $nuevoStock > 0) {
                    $lote->estado = 'ACTIVO';
                }
            } else {
                $nuevoStock = $stockActual - $cantidad;
                $salida = $cantidad;
                $lote->stock_actual = $nuevoStock;
                if ($nuevoStock <= 0) {
                    $lote->estado = $this->nuevoTipoMovimiento === 'BAJA_VENCIMIENTO' ? 'BLOQUEADO' : 'AGOTADO';
                }
            }

            $lote->usuario_modificador_id = Auth::id();
            $lote->save();

            // Registrar movimiento en Kardex
            Movimiento::create([
                'producto_id' => $lote->producto_id,
                'sucursal_id' => $lote->sucursal_id,
                'lote_id' => $lote->id,
                'tipo_movimiento' => $this->nuevoTipoMovimiento,
                'ingreso' => $ingreso,
                'salida' => $salida,
                'saldo_stock' => $nuevoStock,
                'numero_lote' => $lote->numero_lote,
                'fecha_caducidad' => $lote->fecha_vencimiento,
                'fecha' => now(),
                'descripcion' => trim($this->nuevoMotivo),
                'precio_compra' => (float) $lote->precio_compra,
                'precio_venta' => (float) $lote->precio_venta,
                'estado' => 'ACTIVO',
                'usuario_creador_id' => Auth::id(),
            ]);

            $this->closeModal();
            $msg = 'Movimiento registrado en Kardex y saldo actualizado con éxito.';
            session()->flash('message', $msg);
            $this->dispatch('swal-alert', icon: 'success', title: '¡Kardex Asentado!', message: $msg);
        });
    }

    public function seleccionarProducto(int $id): void
    {
        $producto = Producto::find($id);
        if ($producto) {
            $this->nuevoProductoId = $producto->id;
            $this->nuevoLoteId = null;
            $this->productoSearch = '';
            $this->nuevoPrecioCompra = (float) $producto->precio_compra;
            $this->nuevoPrecioVenta = (float) $producto->precio_venta;

            // Verificar si hay lotes en la sucursal seleccionada
            $tieneLotes = Lote::where('producto_id', $producto->id)
                ->where('sucursal_id', $this->nuevoSucursalId)
                ->exists();

            if (! $tieneLotes) {
                $this->esNuevoLote = true;
                $this->generarCodigoNuevoLote();
            } else {
                $this->esNuevoLote = false;
            }
        }
    }

    public function limpiarProducto(): void
    {
        $this->nuevoProductoId = null;
        $this->nuevoLoteId = null;
        $this->esNuevoLote = false;
        $this->productoSearch = '';
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetAjusteForm();
    }

    private function resetAjusteForm(): void
    {
        $this->reset([
            'nuevoProductoId',
            'productoSearch',
            'nuevoLoteId',
            'esNuevoLote',
            'nuevoNumeroLote',
            'nuevaFechaVencimiento',
            'nuevoTipoMovimiento',
            'nuevaCantidad',
            'nuevoMotivo',
            'nuevoPrecioCompra',
            'nuevoPrecioVenta',
        ]);
        $this->resetValidation();
    }

    public function render(): View
    {
        $query = Movimiento::query()
            ->with(['producto.proveedor', 'sucursal', 'lote', 'usuarioCreador'])
            ->when($this->search !== '', function ($q) {
                $q->where(function ($sub) {
                    $term = '%'.trim($this->search).'%';
                    $sub->where('numero_lote', 'like', $term)
                        ->orWhere('descripcion', 'like', $term)
                        ->orWhereHas('producto', function ($prodQuery) use ($term) {
                            $prodQuery->where('nombre', 'like', $term)
                                ->orWhere('principio_activo', 'like', $term)
                                ->orWhere('codigo', 'like', $term)
                                ->orWhere('codigo_barras', 'like', $term)
                                ->orWhereHas('proveedor', function ($provQ) use ($term) {
                                    $provQ->where('nombre', 'like', $term)
                                        ->orWhere('razon_social', 'like', $term);
                                });
                        });
                });
            })
            ->when($this->filtroSucursalId !== null, function ($q) {
                $q->where('sucursal_id', $this->filtroSucursalId);
            })
            ->when($this->filtroProductoId !== null, function ($q) {
                $q->where('producto_id', $this->filtroProductoId);
            })
            ->when($this->filtroTipoMovimiento !== '', function ($q) {
                $q->where('tipo_movimiento', $this->filtroTipoMovimiento);
            })
            ->when($this->fechaInicio, function ($q) {
                $q->whereDate('fecha', '>=', $this->fechaInicio);
            })
            ->when($this->fechaFin, function ($q) {
                $q->whereDate('fecha', '<=', $this->fechaFin);
            })
            ->orderBy('id', 'desc');

        $movimientos = $query->paginate($this->perPage);

        // Estadísticas para KPIs
        $baseKpi = Movimiento::query()
            ->when($this->filtroSucursalId !== null, fn ($q) => $q->where('sucursal_id', $this->filtroSucursalId));

        $totalMovimientos = (clone $baseKpi)->count();
        $totalIngresos = (clone $baseKpi)->sum('ingreso');
        $totalSalidas = (clone $baseKpi)->sum('salida');
        $movimientosHoy = (clone $baseKpi)->whereDate('fecha', now()->toDateString())->count();

        // Búsqueda interactiva de medicamentos para el modal de ajuste
        $productosBusqueda = collect();
        if (strlen(trim($this->productoSearch)) >= 1) {
            $term = '%'.trim($this->productoSearch).'%';
            $productosBusqueda = Producto::with('proveedor')
                ->where(function ($q) use ($term) {
                    $q->where('nombre', 'like', $term)
                        ->orWhere('codigo', 'like', $term)
                        ->orWhere('codigo_barras', 'like', $term)
                        ->orWhere('principio_activo', 'like', $term);
                })
                ->where('estado', 'ACTIVO')
                ->take(10)
                ->get();
        }
        $productoSeleccionado = $this->nuevoProductoId ? Producto::with('proveedor')->find($this->nuevoProductoId) : null;

        $productos = Producto::orderBy('nombre', 'asc')->get();
        $sucursales = Sucursal::orderBy('codigo_sucursal', 'asc')->get();

        // Lotes disponibles para el modal de ajuste según sucursal y producto
        $lotesDisponibles = collect();
        if ($this->nuevoProductoId && $this->nuevoSucursalId) {
            $lotesDisponibles = Lote::where('producto_id', $this->nuevoProductoId)
                ->where('sucursal_id', $this->nuevoSucursalId)
                ->orderBy('fecha_vencimiento', 'asc')
                ->get();
        }

        return view('livewire.inventario.kardex', [
            'movimientos' => $movimientos,
            'productos' => $productos,
            'productosBusqueda' => $productosBusqueda,
            'productoSeleccionado' => $productoSeleccionado,
            'sucursales' => $sucursales,
            'lotesDisponibles' => $lotesDisponibles,
            'kpi' => [
                'total' => $totalMovimientos,
                'ingresos' => $totalIngresos,
                'salidas' => $totalSalidas,
                'hoy' => $movimientosHoy,
            ],
        ]);
    }
}
