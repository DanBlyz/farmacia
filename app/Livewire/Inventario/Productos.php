<?php

namespace App\Livewire\Inventario;

use App\Models\Lote;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Models\UnidadMedida;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Productos extends Component
{
    use WithPagination;

    // Control de Filtros y Búsqueda
    public string $search = '';

    public int $perPage = 10;

    public ?int $filtroProveedorId = null;

    public string $filtroSemaforo = '';

    public ?int $filtroSucursalId = null;

    // Estado Modal de Producto (Creación / Edición)
    public bool $showModal = false;

    public string $modalTab = 'ficha'; // 'ficha', 'siat', 'lote'

    public ?int $productoId = null;

    public string $nombre = '';

    public string $codigo = '';

    public string $codigo_barras = '';

    public string $principio_activo = '';

    public ?int $proveedor_id = null;

    public string $gramaje = '';

    public string $unidad = 'UNIDAD';

    public string $presentacion = '';

    public string $composicion = '';

    public string $accion_terapeutica = '';

    public float|string $minimo_stock = 10;

    public int $unidades_por_caja = 1;

    public string $ubicacion_estante = '';

    public float|string $precio_compra = 0.00;

    public float|string $precio_venta = 0.00;

    public string $codigo_producto_sin = '';

    public ?int $unidad_medida_sin = null;

    public bool $requiere_receta = false;

    public string $estado = 'ACTIVO';

    // Estado Lote Inicial (Opcional en Creación)
    public bool $crearLoteInicial = false;

    public string $numero_lote_inicial = '';

    public string $fecha_vencimiento_inicial = '';

    public float|string $stock_inicial = 0;

    public ?int $sucursal_lote_id = null;

    // Estado Modal de Lotes del Producto (Visualización FEFO)
    public bool $showLotesModal = false;

    public ?Producto $selectedProducto = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroProveedorId(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroSemaforo(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroSucursalId(): void
    {
        $this->resetPage();
    }

    public function updatedCrearLoteInicial(bool $value): void
    {
        if ($value && empty($this->numero_lote_inicial)) {
            $this->generarCodigoLoteInicial();
        }
    }

    public function generarCodigoLoteInicial(): void
    {
        $ultimoId = (int) Lote::withTrashed()->max('id') + 1;
        $this->numero_lote_inicial = 'LOT-'.date('Ym').'-'.str_pad((string) $ultimoId, 5, '0', STR_PAD_LEFT);
    }

    public function mount(): void
    {
        $primeraSucursal = Sucursal::orderBy('codigo_sucursal', 'asc')->first();
        if ($primeraSucursal) {
            $this->sucursal_lote_id = $primeraSucursal->id;
        }
    }

    public function create(): void
    {
        abort_if(! Auth::user()?->tienePermiso('inventario.productos.crear'), 403, 'No tiene permiso para registrar productos.');

        $this->resetForm();

        // Autogenerar código correlativo de sugerencia para producto y lote inicial
        $ultimoId = (int) Producto::withTrashed()->max('id') + 1;
        $this->codigo = 'MED-'.str_pad((string) $ultimoId, 5, '0', STR_PAD_LEFT);
        $this->generarCodigoLoteInicial();

        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        abort_if(! Auth::user()?->tienePermiso('inventario.productos.editar'), 403, 'No tiene permiso para editar productos.');

        $this->resetForm();
        $producto = Producto::findOrFail($id);

        $this->productoId = $producto->id;
        $this->nombre = (string) $producto->nombre;
        $this->codigo = (string) $producto->codigo;
        $this->codigo_barras = (string) ($producto->codigo_barras ?? '');
        $this->principio_activo = (string) ($producto->principio_activo ?? '');
        $this->proveedor_id = $producto->proveedor_id;
        $this->gramaje = (string) ($producto->gramaje ?? '');
        $this->unidad = (string) ($producto->unidad ?? 'UNIDAD');
        $this->presentacion = (string) ($producto->presentacion ?? '');
        $this->composicion = (string) ($producto->composicion ?? '');
        $this->accion_terapeutica = (string) ($producto->accion_terapeutica ?? '');
        $this->minimo_stock = (float) ($producto->minimo_stock ?? 10);
        $this->unidades_por_caja = (int) ($producto->unidades_por_caja ?? 1);
        $this->ubicacion_estante = (string) ($producto->ubicacion_estante ?? '');
        $this->precio_compra = (float) ($producto->precio_compra ?? 0);
        $this->precio_venta = (float) ($producto->precio_venta ?? 0);
        $this->codigo_producto_sin = (string) ($producto->codigo_producto_sin ?? '');
        $this->unidad_medida_sin = $producto->unidad_medida_sin;
        $this->requiere_receta = (bool) $producto->requiere_receta;
        $this->estado = (string) ($producto->estado ?? 'ACTIVO');

        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->productoId) {
            abort_if(! Auth::user()?->tienePermiso('inventario.productos.editar'), 403, 'No tiene permiso para modificar productos.');
        } else {
            abort_if(! Auth::user()?->tienePermiso('inventario.productos.crear'), 403, 'No tiene permiso para dar de alta productos.');
        }

        $this->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:50|unique:productos,codigo,'.($this->productoId ?? 'NULL').',id,deleted_at,NULL',
            'codigo_barras' => 'nullable|string|max:100',
            'principio_activo' => 'nullable|string|max:255',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'composicion' => 'nullable|string|max:500',
            'accion_terapeutica' => 'nullable|string|max:255',
            'gramaje' => 'nullable|string|max:100',
            'presentacion' => 'nullable|string|max:150',
            'unidad' => 'nullable|string|max:50',
            'ubicacion_estante' => 'nullable|string|max:100',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'minimo_stock' => 'required|numeric|min:0',
            'unidades_por_caja' => 'required|integer|min:1',
            'codigo_producto_sin' => 'nullable|string|max:50',
            'unidad_medida_sin' => 'nullable|integer',
            'numero_lote_inicial' => $this->crearLoteInicial && ! $this->productoId ? 'required|string|max:100' : 'nullable',
            'fecha_vencimiento_inicial' => $this->crearLoteInicial && ! $this->productoId ? 'required|date|after:today' : 'nullable',
            'stock_inicial' => $this->crearLoteInicial && ! $this->productoId ? 'required|numeric|min:1' : 'nullable',
            'sucursal_lote_id' => $this->crearLoteInicial && ! $this->productoId ? 'required|exists:sucursales,id' : 'nullable',
        ], [
            'nombre.required' => 'El nombre comercial del medicamento es obligatorio.',
            'codigo.required' => 'El código de identificación es obligatorio.',
            'codigo.unique' => 'Este código ya pertenece a otro producto.',
            'precio_compra.required' => 'El precio de compra es requerido.',
            'precio_venta.required' => 'El precio de venta es requerido.',
            'fecha_vencimiento_inicial.after' => 'La fecha de vencimiento inicial debe ser futura.',
            'stock_inicial.min' => 'El stock inicial debe ser mayor a 0.',
        ]);

        $datos = [
            'nombre' => trim($this->nombre),
            'codigo' => trim($this->codigo),
            'codigo_barras' => $this->codigo_barras ? trim($this->codigo_barras) : null,
            'principio_activo' => $this->principio_activo ? trim($this->principio_activo) : null,
            'proveedor_id' => $this->proveedor_id,
            'gramaje' => $this->gramaje ? trim($this->gramaje) : null,
            'unidad' => $this->unidad ? trim($this->unidad) : 'UNIDAD',
            'presentacion' => $this->presentacion ? trim($this->presentacion) : null,
            'composicion' => $this->composicion ? trim($this->composicion) : null,
            'accion_terapeutica' => $this->accion_terapeutica ? trim($this->accion_terapeutica) : null,
            'minimo_stock' => (float) $this->minimo_stock,
            'unidades_por_caja' => (int) $this->unidades_por_caja,
            'ubicacion_estante' => $this->ubicacion_estante ? trim($this->ubicacion_estante) : null,
            'precio_compra' => (float) $this->precio_compra,
            'precio_venta' => (float) $this->precio_venta,
            'codigo_producto_sin' => $this->codigo_producto_sin ? trim($this->codigo_producto_sin) : null,
            'unidad_medida_sin' => $this->unidad_medida_sin,
            'requiere_receta' => $this->requiere_receta,
            'estado' => $this->estado,
            'usuario_modificador_id' => Auth::id(),
        ];

        if ($this->productoId) {
            $producto = Producto::findOrFail($this->productoId);
            $producto->update($datos);
            $msg = 'Medicamento actualizado con éxito.';
        } else {
            $datos['usuario_creador_id'] = Auth::id();
            $producto = Producto::create($datos);

            // Crear lote inicial si se especificó
            if ($this->crearLoteInicial && $this->stock_inicial > 0) {
                $lote = Lote::create([
                    'producto_id' => $producto->id,
                    'sucursal_id' => $this->sucursal_lote_id,
                    'numero_lote' => trim($this->numero_lote_inicial),
                    'fecha_vencimiento' => $this->fecha_vencimiento_inicial,
                    'stock_actual' => (float) $this->stock_inicial,
                    'stock_minimo_alerta' => (float) $this->minimo_stock,
                    'precio_compra' => (float) $this->precio_compra,
                    'precio_venta' => (float) $this->precio_venta,
                    'ubicacion_estante' => $this->ubicacion_estante ? trim($this->ubicacion_estante) : null,
                    'estado' => 'ACTIVO',
                    'usuario_creador_id' => Auth::id(),
                    'usuario_modificador_id' => Auth::id(),
                ]);

                // Registrar en Kardex de Movimientos
                Movimiento::create([
                    'producto_id' => $producto->id,
                    'sucursal_id' => $this->sucursal_lote_id,
                    'lote_id' => $lote->id,
                    'tipo_movimiento' => 'INGRESO_INICIAL',
                    'ingreso' => (float) $this->stock_inicial,
                    'salida' => 0,
                    'saldo_stock' => (float) $this->stock_inicial,
                    'numero_lote' => $lote->numero_lote,
                    'fecha_caducidad' => $lote->fecha_vencimiento,
                    'fecha' => now(),
                    'descripcion' => 'Inventario inicial de apertura de medicamento',
                    'precio_compra' => (float) $this->precio_compra,
                    'precio_venta' => (float) $this->precio_venta,
                    'estado' => 'ACTIVO',
                    'usuario_creador_id' => Auth::id(),
                ]);
            }

            $msg = 'Medicamento registrado exitosamente en el catálogo.';
        }

        $this->closeModal();
        session()->flash('message', $msg);
        $this->dispatch('swal-alert', icon: 'success', title: '¡Operación Exitosa!', message: $msg);
    }

    public function delete(int $id): void
    {
        abort_if(! Auth::user()?->tienePermiso('inventario.productos.eliminar'), 403, 'No tiene permiso para eliminar productos.');

        $producto = Producto::findOrFail($id);
        $producto->update(['usuario_eliminador_id' => Auth::id()]);
        $producto->delete();

        $msg = 'El medicamento ha sido eliminado del catálogo.';
        session()->flash('message', $msg);
        $this->dispatch('swal-alert', icon: 'success', title: '¡Eliminado!', message: $msg);
    }

    public function verLotes(int $id): void
    {
        abort_if(! Auth::user()?->tienePermiso('inventario.productos.ver'), 403, 'No tiene permiso para consultar lotes.');

        $this->selectedProducto = Producto::with(['lotes.sucursal'])->findOrFail($id);
        $this->showLotesModal = true;
    }

    public function closeLotesModal(): void
    {
        $this->showLotesModal = false;
        $this->selectedProducto = null;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'productoId',
            'nombre',
            'codigo',
            'codigo_barras',
            'principio_activo',
            'proveedor_id',
            'gramaje',
            'unidad',
            'presentacion',
            'composicion',
            'accion_terapeutica',
            'minimo_stock',
            'unidades_por_caja',
            'ubicacion_estante',
            'precio_compra',
            'precio_venta',
            'codigo_producto_sin',
            'unidad_medida_sin',
            'requiere_receta',
            'estado',
            'crearLoteInicial',
            'numero_lote_inicial',
            'fecha_vencimiento_inicial',
            'stock_inicial',
            'modalTab',
        ]);
        $this->resetValidation();

        $primeraSucursal = Sucursal::orderBy('codigo_sucursal', 'asc')->first();
        if ($primeraSucursal) {
            $this->sucursal_lote_id = $primeraSucursal->id;
        }
    }

    public function render(): View
    {
        $productos = Producto::query()
            ->with(['proveedor', 'lotes'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $term = '%'.trim($this->search).'%';
                    $q->where('nombre', 'like', $term)
                        ->orWhere('codigo', 'like', $term)
                        ->orWhere('codigo_barras', 'like', $term)
                        ->orWhere('principio_activo', 'like', $term)
                        ->orWhere('composicion', 'like', $term)
                        ->orWhere('accion_terapeutica', 'like', $term)
                        ->orWhereHas('proveedor', function ($pq) use ($term) {
                            $pq->where('nombre', 'like', $term)
                                ->orWhere('razon_social', 'like', $term);
                        });
                });
            })
            ->when($this->filtroProveedorId !== null, function ($query) {
                $query->where('proveedor_id', $this->filtroProveedorId);
            })
            ->when($this->filtroSucursalId !== null, function ($query) {
                $query->whereHas('lotes', function ($q) {
                    $q->where('sucursal_id', $this->filtroSucursalId);
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        $proveedores = Proveedor::orderBy('nombre', 'asc')->get();
        $sucursales = Sucursal::orderBy('codigo_sucursal', 'asc')->get();
        $unidadesSin = UnidadMedida::orderBy('descripcion', 'asc')->get();

        return view('livewire.inventario.productos', [
            'productos' => $productos,
            'proveedores' => $proveedores,
            'sucursales' => $sucursales,
            'unidadesSin' => $unidadesSin,
        ]);
    }
}
