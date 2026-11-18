<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\Detalle;
use App\Models\Factura;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    use WithPagination;

    public ?int $filtroSucursalId = null;

    // Filtros para Tabla 1: Productos con Stock Bajo
    public string $searchStockBajo = '';

    public int $perPageStockBajo = 5;

    // Filtros para Tabla 2: Productos Más Vendidos
    public string $searchMasVendidos = '';

    public string $filtroPeriodo = 'todos'; // 'hoy', 'semana', 'mes', 'todos'

    public int $perPageMasVendidos = 5;

    public function updatingSearchStockBajo(): void
    {
        $this->resetPage('pageStockBajo');
    }

    public function updatingPerPageStockBajo(): void
    {
        $this->resetPage('pageStockBajo');
    }

    public function updatingSearchMasVendidos(): void
    {
        $this->resetPage('pageMasVendidos');
    }

    public function updatingPerPageMasVendidos(): void
    {
        $this->resetPage('pageMasVendidos');
    }

    public function updatingFiltroPeriodo(): void
    {
        $this->resetPage('pageMasVendidos');
    }

    public function updatingFiltroSucursalId(): void
    {
        $this->resetPage('pageStockBajo');
        $this->resetPage('pageMasVendidos');
    }

    public function limpiarFiltrosStockBajo(): void
    {
        $this->reset(['searchStockBajo']);
        $this->resetPage('pageStockBajo');
    }

    public function limpiarFiltrosMasVendidos(): void
    {
        $this->reset(['searchMasVendidos', 'filtroPeriodo']);
        $this->resetPage('pageMasVendidos');
    }

    /**
     * Obtiene el listado paginado de productos cuyo stock vigente total (o en la sucursal seleccionada)
     * se encuentra en o por debajo del stock mínimo registrado.
     */
    protected function getProductosStockBajo(): LengthAwarePaginator
    {
        $lotesSub = Lote::query()
            ->selectRaw('COALESCE(SUM(lotes.stock_actual), 0)')
            ->whereColumn('lotes.producto_id', 'productos.id')
            ->where('lotes.estado', 'ACTIVO')
            ->where('lotes.fecha_vencimiento', '>=', now()->toDateString())
            ->whereNull('lotes.deleted_at')
            ->when($this->filtroSucursalId !== null, function ($q) {
                $q->where('lotes.sucursal_id', $this->filtroSucursalId);
            });

        return Producto::query()
            ->select('productos.*')
            ->selectSub($lotesSub, 'stock_actual_vigente')
            ->where('productos.estado', 'ACTIVO')
            ->whereRaw('('.$lotesSub->toSql().') <= productos.minimo_stock', $lotesSub->getBindings())
            ->when($this->searchStockBajo !== '', function ($query) {
                $term = '%'.trim($this->searchStockBajo).'%';
                $query->where(function ($q) use ($term) {
                    $q->where('productos.nombre', 'like', $term)
                        ->orWhere('productos.codigo', 'like', $term)
                        ->orWhere('productos.codigo_barras', 'like', $term)
                        ->orWhere('productos.principio_activo', 'like', $term);
                });
            })
            ->orderBy('productos.nombre', 'asc')
            ->paginate($this->perPageStockBajo, ['*'], 'pageStockBajo');
    }

    /**
     * Obtiene el listado paginado de productos más vendidos ordenados por cantidad de unidades dispensadas.
     */
    protected function getProductosMasVendidos(): LengthAwarePaginator
    {
        return Detalle::query()
            ->select('detalles.producto_id')
            ->selectRaw('MAX(detalles.nombre_producto) as nombre_producto_historico')
            ->selectRaw('SUM(detalles.cantidad) as total_unidades_vendidas')
            ->selectRaw('SUM(detalles.total) as total_monto_recaudado')
            ->selectRaw('COUNT(DISTINCT detalles.factura_id) as total_transacciones')
            ->whereHas('factura', function ($query) {
                $query->where('estado', '!=', 'ANULADA')
                    ->when($this->filtroSucursalId !== null, function ($q) {
                        $q->where('sucursal_id', $this->filtroSucursalId);
                    })
                    ->when($this->filtroPeriodo === 'hoy', function ($q) {
                        $q->whereDate('fecha', Carbon::today());
                    })
                    ->when($this->filtroPeriodo === 'semana', function ($q) {
                        $q->whereBetween('fecha', [now()->startOfWeek(), now()->endOfWeek()]);
                    })
                    ->when($this->filtroPeriodo === 'mes', function ($q) {
                        $q->whereMonth('fecha', now()->month)->whereYear('fecha', now()->year);
                    });
            })
            ->when($this->searchMasVendidos !== '', function ($query) {
                $term = '%'.trim($this->searchMasVendidos).'%';
                $query->where(function ($q) use ($term) {
                    $q->where('detalles.nombre_producto', 'like', $term)
                        ->orWhereHas('producto', function ($p) use ($term) {
                            $p->where('nombre', 'like', $term)
                                ->orWhere('codigo', 'like', $term)
                                ->orWhere('principio_activo', 'like', $term);
                        });
                });
            })
            ->with('producto')
            ->groupBy('detalles.producto_id')
            ->orderByDesc('total_unidades_vendidas')
            ->paginate($this->perPageMasVendidos, ['*'], 'pageMasVendidos');
    }

    public function render(): View
    {
        // 1. KPI: Ventas del día de hoy (no anuladas)
        $ventasHoyQuery = Factura::query()
            ->where('estado', '!=', 'ANULADA')
            ->whereDate('fecha', Carbon::today())
            ->when($this->filtroSucursalId !== null, function ($q) {
                $q->where('sucursal_id', $this->filtroSucursalId);
            });

        $totalVentasHoy = (float) $ventasHoyQuery->sum('total');
        $cantidadVentasHoy = $ventasHoyQuery->count();

        // Comparativa con las ventas de ayer
        $ventasAyer = (float) Factura::query()
            ->where('estado', '!=', 'ANULADA')
            ->whereDate('fecha', Carbon::yesterday())
            ->when($this->filtroSucursalId !== null, function ($q) {
                $q->where('sucursal_id', $this->filtroSucursalId);
            })
            ->sum('total');

        $porcentajeVsAyer = null;
        if ($ventasAyer > 0) {
            $porcentajeVsAyer = round((($totalVentasHoy - $ventasAyer) / $ventasAyer) * 100, 1);
        }

        // 2. KPI: Catálogo de Productos y alerta de Stock Bajo
        $totalProductos = Producto::query()->where('estado', 'ACTIVO')->count();

        $lotesSubCount = Lote::query()
            ->selectRaw('COALESCE(SUM(lotes.stock_actual), 0)')
            ->whereColumn('lotes.producto_id', 'productos.id')
            ->where('lotes.estado', 'ACTIVO')
            ->where('lotes.fecha_vencimiento', '>=', now()->toDateString())
            ->whereNull('lotes.deleted_at')
            ->when($this->filtroSucursalId !== null, function ($q) {
                $q->where('lotes.sucursal_id', $this->filtroSucursalId);
            });

        $totalProductosStockBajo = Producto::query()
            ->where('productos.estado', 'ACTIVO')
            ->whereRaw('('.$lotesSubCount->toSql().') <= productos.minimo_stock', $lotesSubCount->getBindings())
            ->count();

        // 3. KPI: Lotes por vencer (próximos 60 días) y vencidos
        $lotesPorVencer = Lote::query()
            ->where('estado', 'ACTIVO')
            ->proximosAVencer(60)
            ->when($this->filtroSucursalId !== null, function ($q) {
                $q->where('sucursal_id', $this->filtroSucursalId);
            })
            ->count();

        $lotesVencidos = Lote::query()
            ->where('estado', 'ACTIVO')
            ->vencidos()
            ->when($this->filtroSucursalId !== null, function ($q) {
                $q->where('sucursal_id', $this->filtroSucursalId);
            })
            ->count();

        // 4. KPI: Clientes atendidos hoy y total clientes registrados
        $clientesAtendidosHoy = Factura::query()
            ->where('estado', '!=', 'ANULADA')
            ->whereDate('fecha', Carbon::today())
            ->when($this->filtroSucursalId !== null, function ($q) {
                $q->where('sucursal_id', $this->filtroSucursalId);
            })
            ->whereNotNull('cliente_id')
            ->distinct('cliente_id')
            ->count('cliente_id');

        $totalClientes = Cliente::query()->where('estado', 'ACTIVO')->count();

        // Listado de sucursales para el filtro superior
        $sucursales = Sucursal::query()->orderBy('codigo_sucursal', 'asc')->get();

        // Consultas paginadas de las dos tablas
        $productosStockBajo = $this->getProductosStockBajo();
        $productosMasVendidos = $this->getProductosMasVendidos();

        return view('livewire.dashboard', [
            'totalVentasHoy' => $totalVentasHoy,
            'cantidadVentasHoy' => $cantidadVentasHoy,
            'porcentajeVsAyer' => $porcentajeVsAyer,
            'totalProductos' => $totalProductos,
            'totalProductosStockBajo' => $totalProductosStockBajo,
            'lotesPorVencer' => $lotesPorVencer,
            'lotesVencidos' => $lotesVencidos,
            'clientesAtendidosHoy' => $clientesAtendidosHoy,
            'totalClientes' => $totalClientes,
            'sucursales' => $sucursales,
            'productosStockBajo' => $productosStockBajo,
            'productosMasVendidos' => $productosMasVendidos,
        ]);
    }
}
