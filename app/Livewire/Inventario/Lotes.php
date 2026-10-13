<?php

namespace App\Livewire\Inventario;

use App\Models\Lote;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Lotes extends Component
{
    use WithPagination;

    // Filtros de búsqueda
    public string $search = '';

    public int $perPage = 10;

    public ?int $filtroSucursalId = null;

    public string $filtroSemaforo = 'todos'; // 'todos', 'optimo', 'proximo', 'vencido'

    public string $filtroEstado = ''; // '', 'ACTIVO', 'AGOTADO', 'BLOQUEADO'

    // Estado Modal de Creación / Edición
    public bool $showModal = false;

    public ?int $loteId = null;

    public ?int $producto_id = null;

    public string $productoSearch = '';

    public ?int $sucursal_id = null;

    public string $numero_lote = '';

    public string $fecha_vencimiento = '';

    public float|string $stock_actual = 0;

    public float|string $stock_minimo_alerta = 10;

    public float|string $precio_compra = 0.00;

    public float|string $precio_venta = 0.00;

    public string $ubicacion_estante = '';

    public string $estado = 'ACTIVO';

    // Estado Modal de Ajuste de Inventario / Baja
    public bool $showAjusteModal = false;

    public ?int $ajusteLoteId = null;

    public ?Lote $selectedLote = null;

    public string $tipoAjuste = 'AJUSTE_POSITIVO'; // 'AJUSTE_POSITIVO', 'AJUSTE_NEGATIVO', 'BAJA_VENCIMIENTO'

    public float|string $cantidadAjuste = 1;

    public string $motivoAjuste = '';

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

    public function updatedFiltroSemaforo(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $primeraSucursal = Sucursal::orderBy('codigo_sucursal', 'asc')->first();
        if ($primeraSucursal) {
            $this->sucursal_id = $primeraSucursal->id;
        }
    }

    public function generarCodigoLote(): void
    {
        $ultimoId = (int) Lote::withTrashed()->max('id') + 1;
        $this->numero_lote = 'LOT-'.date('Ym').'-'.str_pad((string) $ultimoId, 5, '0', STR_PAD_LEFT);
    }

    public function create(): void
    {
        abort_if(! Auth::user()?->tienePermiso('inventario.lotes.crear'), 403, 'No tiene permiso para dar de alta lotes.');

        $this->resetForm();
        $primeraSucursal = Sucursal::orderBy('codigo_sucursal', 'asc')->first();
        if ($primeraSucursal) {
            $this->sucursal_id = $primeraSucursal->id;
        }

        $this->generarCodigoLote();

        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        abort_if(! Auth::user()?->tienePermiso('inventario.lotes.editar'), 403, 'No tiene permiso para modificar lotes.');

        $this->resetForm();
        $lote = Lote::findOrFail($id);

        $this->loteId = $lote->id;
        $this->producto_id = $lote->producto_id;
        $this->sucursal_id = $lote->sucursal_id;
        $this->numero_lote = (string) $lote->numero_lote;
        $this->fecha_vencimiento = $lote->fecha_vencimiento ? Carbon::parse($lote->fecha_vencimiento)->format('Y-m-d') : '';
        $this->stock_actual = (float) $lote->stock_actual;
        $this->stock_minimo_alerta = (float) ($lote->stock_minimo_alerta ?? 10);
        $this->precio_compra = (float) ($lote->precio_compra ?? 0);
        $this->precio_venta = (float) ($lote->precio_venta ?? 0);
        $this->ubicacion_estante = (string) ($lote->ubicacion_estante ?? '');
        $this->estado = (string) ($lote->estado ?? 'ACTIVO');

        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->loteId) {
            abort_if(! Auth::user()?->tienePermiso('inventario.lotes.editar'), 403, 'No tiene permiso para modificar lotes.');
        } else {
            abort_if(! Auth::user()?->tienePermiso('inventario.lotes.crear'), 403, 'No tiene permiso para registrar lotes.');
        }

        $rules = [
            'producto_id' => 'required|exists:productos,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'numero_lote' => 'required|string|max:100',
            'fecha_vencimiento' => 'required|date'.($this->loteId ? '' : '|after:today'),
            'stock_minimo_alerta' => 'required|numeric|min:0',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'ubicacion_estante' => 'nullable|string|max:100',
            'estado' => 'required|in:ACTIVO,AGOTADO,BLOQUEADO',
        ];

        if (! $this->loteId) {
            $rules['stock_actual'] = 'required|numeric|min:1';
        }

        $messages = [
            'producto_id.required' => 'Debe seleccionar el medicamento correspondiente.',
            'sucursal_id.required' => 'Debe seleccionar la sucursal de destino.',
            'numero_lote.required' => 'El número de lote es obligatorio.',
            'fecha_vencimiento.required' => 'La fecha de vencimiento es requerida.',
            'fecha_vencimiento.after' => 'La fecha de vencimiento debe ser posterior al día de hoy.',
            'stock_actual.min' => 'El stock inicial del lote debe ser mayor a 0.',
            'precio_compra.required' => 'El costo de compra es requerido.',
            'precio_venta.required' => 'El precio de venta es requerido.',
        ];

        $this->validate($rules, $messages);

        DB::transaction(function () {
            if ($this->loteId) {
                $lote = Lote::findOrFail($this->loteId);
                $lote->update([
                    'producto_id' => $this->producto_id,
                    'sucursal_id' => $this->sucursal_id,
                    'numero_lote' => trim($this->numero_lote),
                    'fecha_vencimiento' => $this->fecha_vencimiento,
                    'stock_minimo_alerta' => (float) $this->stock_minimo_alerta,
                    'precio_compra' => (float) $this->precio_compra,
                    'precio_venta' => (float) $this->precio_venta,
                    'ubicacion_estante' => $this->ubicacion_estante ? trim($this->ubicacion_estante) : null,
                    'estado' => $this->estado,
                    'usuario_modificador_id' => Auth::id(),
                ]);

                // Sincronizar número de lote en movimientos asociados para trazabilidad
                Movimiento::where('lote_id', $lote->id)->update(['numero_lote' => trim($this->numero_lote)]);

                $msg = 'Lote actualizado correctamente.';
            } else {
                $lote = Lote::create([
                    'producto_id' => $this->producto_id,
                    'sucursal_id' => $this->sucursal_id,
                    'numero_lote' => trim($this->numero_lote),
                    'fecha_vencimiento' => $this->fecha_vencimiento,
                    'stock_actual' => (float) $this->stock_actual,
                    'stock_minimo_alerta' => (float) $this->stock_minimo_alerta,
                    'precio_compra' => (float) $this->precio_compra,
                    'precio_venta' => (float) $this->precio_venta,
                    'ubicacion_estante' => $this->ubicacion_estante ? trim($this->ubicacion_estante) : null,
                    'estado' => $this->estado,
                    'usuario_creador_id' => Auth::id(),
                    'usuario_modificador_id' => Auth::id(),
                ]);

                // Registrar en Kardex de Movimientos
                Movimiento::create([
                    'producto_id' => $this->producto_id,
                    'sucursal_id' => $this->sucursal_id,
                    'lote_id' => $lote->id,
                    'tipo_movimiento' => 'INGRESO_INICIAL',
                    'ingreso' => (float) $this->stock_actual,
                    'salida' => 0,
                    'saldo_stock' => (float) $this->stock_actual,
                    'numero_lote' => $lote->numero_lote,
                    'fecha_caducidad' => $lote->fecha_vencimiento,
                    'fecha' => now(),
                    'descripcion' => 'Apertura de lote manual: '.$lote->numero_lote,
                    'precio_compra' => (float) $this->precio_compra,
                    'precio_venta' => (float) $this->precio_venta,
                    'estado' => 'ACTIVO',
                    'usuario_creador_id' => Auth::id(),
                ]);

                $msg = 'Lote registrado con éxito en el almacén.';
            }

            $this->closeModal();
            session()->flash('message', $msg);
            $this->dispatch('swal-alert', icon: 'success', title: '¡Operación Exitosa!', message: $msg);
        });
    }

    public function abrirAjuste(int $id): void
    {
        abort_if(! Auth::user()?->tienePermiso('inventario.lotes.ajustar'), 403, 'No tiene permiso para realizar ajustes de stock.');

        $this->selectedLote = Lote::with(['producto', 'sucursal'])->findOrFail($id);
        $this->ajusteLoteId = $this->selectedLote->id;
        $this->tipoAjuste = 'AJUSTE_POSITIVO';
        $this->cantidadAjuste = 1;
        $this->motivoAjuste = '';
        $this->showAjusteModal = true;
    }

    public function guardarAjuste(): void
    {
        abort_if(! Auth::user()?->tienePermiso('inventario.lotes.ajustar'), 403, 'No tiene permiso para realizar ajustes de stock.');

        $this->validate([
            'tipoAjuste' => 'required|in:AJUSTE_POSITIVO,AJUSTE_NEGATIVO,BAJA_VENCIMIENTO',
            'cantidadAjuste' => 'required|numeric|min:0.01',
            'motivoAjuste' => 'required|string|min:5|max:255',
        ], [
            'cantidadAjuste.min' => 'La cantidad a ajustar debe ser mayor a cero.',
            'motivoAjuste.required' => 'Debe justificar el motivo del ajuste o baja.',
            'motivoAjuste.min' => 'El motivo debe contener al menos 5 caracteres descriptivos.',
        ]);

        $lote = Lote::findOrFail($this->ajusteLoteId);
        $cantidad = (float) $this->cantidadAjuste;
        $stockActual = (float) $lote->stock_actual;

        if (in_array($this->tipoAjuste, ['AJUSTE_NEGATIVO', 'BAJA_VENCIMIENTO']) && $cantidad > $stockActual) {
            $this->addError('cantidadAjuste', "No puede descontar {$cantidad} unidades. El lote solo dispone de {$stockActual} unidades físicas.");

            return;
        }

        DB::transaction(function () use ($lote, $cantidad, $stockActual) {
            $ingreso = 0;
            $salida = 0;

            if ($this->tipoAjuste === 'AJUSTE_POSITIVO') {
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
                    $lote->estado = $this->tipoAjuste === 'BAJA_VENCIMIENTO' ? 'BLOQUEADO' : 'AGOTADO';
                }
            }

            $lote->usuario_modificador_id = Auth::id();
            $lote->save();

            // Registrar movimiento en Kardex
            Movimiento::create([
                'producto_id' => $lote->producto_id,
                'sucursal_id' => $lote->sucursal_id,
                'lote_id' => $lote->id,
                'tipo_movimiento' => $this->tipoAjuste,
                'ingreso' => $ingreso,
                'salida' => $salida,
                'saldo_stock' => $nuevoStock,
                'numero_lote' => $lote->numero_lote,
                'fecha_caducidad' => $lote->fecha_vencimiento,
                'fecha' => now(),
                'descripcion' => trim($this->motivoAjuste),
                'precio_compra' => (float) $lote->precio_compra,
                'precio_venta' => (float) $lote->precio_venta,
                'estado' => 'ACTIVO',
                'usuario_creador_id' => Auth::id(),
            ]);

            $this->closeAjusteModal();
            $msg = 'Ajuste de inventario aplicado y registrado en Kardex con éxito.';
            session()->flash('message', $msg);
            $this->dispatch('swal-alert', icon: 'success', title: '¡Ajuste Realizado!', message: $msg);
        });
    }

    public function toggleBloqueo(int $id): void
    {
        abort_if(! Auth::user()?->tienePermiso('inventario.lotes.bloquear'), 403, 'No tiene permiso para bloquear o desbloquear lotes.');

        $lote = Lote::findOrFail($id);
        $nuevoEstado = $lote->estado === 'BLOQUEADO' ? 'ACTIVO' : 'BLOQUEADO';
        $lote->update([
            'estado' => $nuevoEstado,
            'usuario_modificador_id' => Auth::id(),
        ]);

        $accion = $nuevoEstado === 'BLOQUEADO' ? 'bloqueado para dispensación' : 'reactivado';
        $msg = "El lote {$lote->numero_lote} ha sido {$accion}.";
        session()->flash('message', $msg);
        $this->dispatch('swal-alert', icon: 'info', title: 'Estado Actualizado', message: $msg);
    }

    public function delete(int $id): void
    {
        abort_if(! Auth::user()?->tienePermiso('inventario.lotes.eliminar'), 403, 'No tiene permiso para dar de baja definitiva a este lote.');

        $lote = Lote::findOrFail($id);
        $lote->update(['usuario_eliminador_id' => Auth::id()]);
        $lote->delete();

        $msg = 'El lote ha sido eliminado del registro.';
        session()->flash('message', $msg);
        $this->dispatch('swal-alert', icon: 'success', title: '¡Eliminado!', message: $msg);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeAjusteModal(): void
    {
        $this->showAjusteModal = false;
        $this->ajusteLoteId = null;
        $this->selectedLote = null;
        $this->resetValidation();
    }

    public function seleccionarProducto(int $id): void
    {
        $producto = Producto::find($id);
        if ($producto) {
            $this->producto_id = $producto->id;
            $this->productoSearch = '';

            // Sugerir precios y parámetros del medicamento si se está registrando un lote nuevo
            if (! $this->loteId) {
                if ((float) $this->precio_compra === 0.0) {
                    $this->precio_compra = (float) $producto->precio_compra;
                }
                if ((float) $this->precio_venta === 0.0) {
                    $this->precio_venta = (float) $producto->precio_venta;
                }
                if ($this->ubicacion_estante === '' && $producto->ubicacion_estante) {
                    $this->ubicacion_estante = (string) $producto->ubicacion_estante;
                }
            }
        }
    }

    public function limpiarProducto(): void
    {
        $this->producto_id = null;
        $this->productoSearch = '';
    }

    private function resetForm(): void
    {
        $this->reset([
            'loteId',
            'producto_id',
            'productoSearch',
            'numero_lote',
            'fecha_vencimiento',
            'stock_actual',
            'stock_minimo_alerta',
            'precio_compra',
            'precio_venta',
            'ubicacion_estante',
            'estado',
        ]);
        $this->resetValidation();
    }

    public function render(): View
    {
        $hoy = now()->toDateString();
        $limiteProximo = now()->addDays(60)->toDateString();

        $query = Lote::query()
            ->with(['producto.proveedor', 'sucursal'])
            ->when($this->search !== '', function ($q) {
                $q->where(function ($sub) {
                    $term = '%'.trim($this->search).'%';
                    $sub->where('numero_lote', 'like', $term)
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
            ->when($this->filtroEstado !== '', function ($q) {
                $q->where('estado', $this->filtroEstado);
            })
            ->when($this->filtroSemaforo !== 'todos', function ($q) use ($hoy, $limiteProximo) {
                if ($this->filtroSemaforo === 'vencido') {
                    $q->where('fecha_vencimiento', '<', $hoy);
                } elseif ($this->filtroSemaforo === 'proximo') {
                    $q->where('fecha_vencimiento', '>=', $hoy)
                        ->where('fecha_vencimiento', '<=', $limiteProximo);
                } elseif ($this->filtroSemaforo === 'optimo') {
                    $q->where('fecha_vencimiento', '>', $limiteProximo);
                }
            })
            ->fefo(); // Orden estricto FEFO: fecha_vencimiento ASC

        $lotes = $query->paginate($this->perPage);

        // Estadísticas para KPIs
        $baseKpi = Lote::query()->when($this->filtroSucursalId !== null, fn ($q) => $q->where('sucursal_id', $this->filtroSucursalId));
        $totalLotes = (clone $baseKpi)->count();
        $totalVencidos = (clone $baseKpi)->where('fecha_vencimiento', '<', $hoy)->count();
        $totalProximos = (clone $baseKpi)->where('fecha_vencimiento', '>=', $hoy)->where('fecha_vencimiento', '<=', $limiteProximo)->count();
        $totalOptimos = (clone $baseKpi)->where('fecha_vencimiento', '>', $limiteProximo)->count();

        // Búsqueda interactiva de medicamentos para el modal
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
        $productoSeleccionado = $this->producto_id ? Producto::with('proveedor')->find($this->producto_id) : null;

        $sucursales = Sucursal::orderBy('codigo_sucursal', 'asc')->get();

        return view('livewire.inventario.lotes', [
            'lotes' => $lotes,
            'productosBusqueda' => $productosBusqueda,
            'productoSeleccionado' => $productoSeleccionado,
            'sucursales' => $sucursales,
            'kpi' => [
                'total' => $totalLotes,
                'vencidos' => $totalVencidos,
                'proximos' => $totalProximos,
                'optimos' => $totalOptimos,
            ],
        ]);
    }
}
