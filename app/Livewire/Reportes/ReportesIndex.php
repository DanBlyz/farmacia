<?php

namespace App\Livewire\Reportes;

use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Reportes Generales')]
class ReportesIndex extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $tab = 'inventario'; // 'inventario', 'utilidades', 'cajas', 'clientes'

    #[Url(history: true)]
    public string $fechaInicio = '';

    #[Url(history: true)]
    public string $fechaFin = '';

    #[Url(history: true)]
    public ?int $filtroSucursalId = null;

    #[Url(history: true)]
    public string $search = '';

    public int $perPage = 15;

    // Filtros específicos de Inventario
    public ?int $filtroProductoId = null;

    // Filtros específicos de Utilidades
    public ?int $filtroUsuarioUtilidadId = null;

    public ?int $filtroCajaUtilidadId = null;

    // Filtros específicos de Cajas
    public ?int $filtroUsuarioCajaId = null;

    public string $filtroEstadoCaja = '';

    public function mount(): void
    {
        if (empty($this->fechaInicio)) {
            $this->fechaInicio = Carbon::now()->startOfMonth()->toDateString();
        }

        if (empty($this->fechaFin)) {
            $this->fechaFin = Carbon::now()->toDateString();
        }

        // Auto-seleccionar sucursal del usuario si no es admin
        $user = Auth::user();
        if ($user && ! $user->esAdmin() && $user->puntoVenta?->sucursal_id && ! $this->filtroSucursalId) {
            $this->filtroSucursalId = $user->puntoVenta->sucursal_id;
        }

        // Verificar permisos y fijar tab inicial permitido
        $this->asegurarTabPermitido();
    }

    public function cambiarTab(string $nuevoTab): void
    {
        $this->tab = $nuevoTab;
        $this->asegurarTabPermitido();
        $this->resetPage();
        $this->search = '';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroSucursalId(): void
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

    public function updatedFiltroProductoId(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroUsuarioUtilidadId(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroCajaUtilidadId(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroUsuarioCajaId(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEstadoCaja(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->filtroProductoId = null;
        $this->filtroUsuarioUtilidadId = null;
        $this->filtroCajaUtilidadId = null;
        $this->filtroUsuarioCajaId = null;
        $this->filtroEstadoCaja = '';
        $this->fechaInicio = Carbon::now()->startOfMonth()->toDateString();
        $this->fechaFin = Carbon::now()->toDateString();
        $this->resetPage();
    }

    private function asegurarTabPermitido(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $tabsPermisos = [
            'inventario' => 'reportes.inventario.ver',
            'utilidades' => 'reportes.utilidades.ver',
            'cajas' => 'reportes.cajas.ver',
            'clientes' => 'reportes.clientes.ver',
        ];

        if (isset($tabsPermisos[$this->tab]) && ! $user->tienePermiso($tabsPermisos[$this->tab])) {
            foreach ($tabsPermisos as $nombreTab => $permiso) {
                if ($user->tienePermiso($permiso)) {
                    $this->tab = $nombreTab;

                    return;
                }
            }
        }
    }

    /**
     * Construye la URL de exportación PDF para el reporte activo con los filtros vigentes.
     */
    public function obtenerUrlPdf(): string
    {
        $params = [
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'sucursal_id' => $this->filtroSucursalId,
        ];

        return match ($this->tab) {
            'inventario' => route('reportes.inventario.pdf', array_merge($params, [
                'producto_id' => $this->filtroProductoId,
            ])),
            'utilidades' => route('reportes.utilidades.pdf', array_merge($params, [
                'usuario_id' => $this->filtroUsuarioUtilidadId,
                'caja_id' => $this->filtroCajaUtilidadId,
            ])),
            'cajas' => route('reportes.cajas.pdf', array_merge($params, [
                'usuario_id' => $this->filtroUsuarioCajaId,
                'estado' => $this->filtroEstadoCaja,
            ])),
            'clientes' => route('reportes.clientes.pdf', $params),
            default => route('reportes.inventario.pdf', $params),
        };
    }

    public function render(): View
    {
        $sucursales = Sucursal::orderBy('nombre')->get();
        $usuarios = User::orderBy('name')->get();
        $productosLista = Producto::where('estado', 'ACTIVO')->orderBy('nombre')->get(['id', 'nombre', 'codigo', 'codigo_barras']);

        // Data arrays and KPIs depending on the active tab
        $data = [];
        $kpis = [];

        if ($this->tab === 'inventario') {
            [$data, $kpis] = $this->obtenerDatosInventario();
        } elseif ($this->tab === 'utilidades') {
            [$data, $kpis] = $this->obtenerDatosUtilidades();
        } elseif ($this->tab === 'cajas') {
            [$data, $kpis] = $this->obtenerDatosCajas();
        } elseif ($this->tab === 'clientes') {
            [$data, $kpis] = $this->obtenerDatosClientes();
        }

        return view('livewire.reportes.reportes-index', [
            'sucursales' => $sucursales,
            'usuarios' => $usuarios,
            'productosLista' => $productosLista,
            'items' => $data,
            'kpis' => $kpis,
        ]);
    }

    /**
     * Reporte 1: Inventario (Ingresos, Salidas, Stock Inicial y Final, Valuación Compra y Venta).
     */
    private function obtenerDatosInventario(): array
    {
        $query = Producto::query()
            ->with(['proveedor'])
            ->where('estado', 'ACTIVO')
            ->when($this->filtroProductoId, fn ($q) => $q->where('id', $this->filtroProductoId))
            ->when($this->search, function ($q) {
                $termino = trim($this->search);
                $q->where(function ($sub) use ($termino) {
                    $sub->where('nombre', 'like', "%{$termino}%")
                        ->orWhere('codigo', 'like', "%{$termino}%")
                        ->orWhere('codigo_barras', 'like', "%{$termino}%")
                        ->orWhere('principio_activo', 'like', "%{$termino}%");
                });
            })
            ->orderBy('nombre');

        $paginator = $query->paginate($this->perPage);

        $items = [];
        foreach ($paginator->items() as $prod) {
            $movsPrevios = Movimiento::query()
                ->where('producto_id', $prod->id)
                ->when($this->filtroSucursalId, fn ($q) => $q->where('sucursal_id', $this->filtroSucursalId))
                ->where('fecha', '<', $this->fechaInicio.' 00:00:00');

            $stockInicial = max(0, (float) ($movsPrevios->sum('ingreso') - $movsPrevios->sum('salida')));

            $ingresos = (float) Movimiento::query()
                ->where('producto_id', $prod->id)
                ->when($this->filtroSucursalId, fn ($q) => $q->where('sucursal_id', $this->filtroSucursalId))
                ->whereBetween('fecha', [$this->fechaInicio.' 00:00:00', $this->fechaFin.' 23:59:59'])
                ->sum('ingreso');

            $salidas = (float) Movimiento::query()
                ->where('producto_id', $prod->id)
                ->when($this->filtroSucursalId, fn ($q) => $q->where('sucursal_id', $this->filtroSucursalId))
                ->whereBetween('fecha', [$this->fechaInicio.' 00:00:00', $this->fechaFin.' 23:59:59'])
                ->sum('salida');

            $stockFinal = $stockInicial + $ingresos - $salidas;
            $precioCompra = (float) ($prod->precio_compra ?? 0);
            $precioVenta = (float) ($prod->precio_venta ?? 0);

            $items[] = [
                'producto' => $prod,
                'stock_inicial' => $stockInicial,
                'ingresos' => $ingresos,
                'salidas' => $salidas,
                'stock_final' => $stockFinal,
                'precio_compra' => $precioCompra,
                'precio_venta' => $precioVenta,
                'valor_compra' => $stockFinal * $precioCompra,
                'valor_venta' => $stockFinal * $precioVenta,
            ];
        }

        // Totales globales del periodo
        $movimientosPeriodo = Movimiento::query()
            ->when($this->filtroSucursalId, fn ($q) => $q->where('sucursal_id', $this->filtroSucursalId))
            ->when($this->filtroProductoId, fn ($q) => $q->where('producto_id', $this->filtroProductoId))
            ->whereBetween('fecha', [$this->fechaInicio.' 00:00:00', $this->fechaFin.' 23:59:59']);

        $totalIngresos = (float) (clone $movimientosPeriodo)->sum('ingreso');
        $totalSalidas = (float) (clone $movimientosPeriodo)->sum('salida');

        // Suma de valuaciones en página actual
        $valCompraPagina = collect($items)->sum('valor_compra');
        $valVentaPagina = collect($items)->sum('valor_venta');

        $paginatorItems = new LengthAwarePaginator(
            $items,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $kpis = [
            'totalIngresos' => $totalIngresos,
            'totalSalidas' => $totalSalidas,
            'valCompraPagina' => $valCompraPagina,
            'valVentaPagina' => $valVentaPagina,
            'totalProductos' => $paginator->total(),
        ];

        return [$paginatorItems, $kpis];
    }

    /**
     * Reporte 2: Utilidades (Facturas validadas, Cajero, Caja, Costos de Lote, Ganancia y Desglose por Pago).
     */
    private function obtenerDatosUtilidades(): array
    {
        $baseQuery = Factura::query()
            ->with(['usuarioCreador', 'caja', 'pagos', 'detalles.lote', 'detalles.producto'])
            ->where('estado', 'VALIDADA')
            ->whereBetween('fecha', [$this->fechaInicio.' 00:00:00', $this->fechaFin.' 23:59:59'])
            ->when($this->filtroSucursalId, fn ($q) => $q->where('sucursal_id', $this->filtroSucursalId))
            ->when($this->filtroUsuarioUtilidadId, fn ($q) => $q->where('usuario_creador_id', $this->filtroUsuarioUtilidadId))
            ->when($this->filtroCajaUtilidadId, fn ($q) => $q->where('caja_id', $this->filtroCajaUtilidadId))
            ->when($this->search, function ($q) {
                $termino = trim($this->search);
                $q->where(function ($sub) use ($termino) {
                    $sub->where('numero_factura', 'like', "%{$termino}%")
                        ->orWhere('razon_social', 'like', "%{$termino}%")
                        ->orWhere('nit', 'like', "%{$termino}%");
                });
            })
            ->orderByDesc('fecha');

        $paginator = $baseQuery->paginate($this->perPage);

        $facturasData = [];
        foreach ($paginator->items() as $factura) {
            $montoVenta = (float) $factura->total;
            $costoFactura = 0;

            foreach ($factura->detalles as $det) {
                $costoUnitario = (float) ($det->lote->precio_compra ?? $det->producto->precio_compra ?? 0);
                $costoFactura += (float) $det->cantidad * $costoUnitario;
            }

            $utilidad = $montoVenta - $costoFactura;
            $efectivo = (float) $factura->pagos->where('tipo_pago', 'EFECTIVO')->sum(fn ($p) => $p->monto - $p->cambio);
            $qr = (float) $factura->pagos->where('tipo_pago', 'QR')->sum('monto');
            $transf = (float) $factura->pagos->where('tipo_pago', 'TRANSFERENCIA')->sum('monto');

            $facturasData[] = [
                'factura' => $factura,
                'costo' => $costoFactura,
                'utilidad' => $utilidad,
                'margen' => $montoVenta > 0 ? ($utilidad / $montoVenta) * 100 : 0,
                'efectivo' => $efectivo,
                'qr' => $qr,
                'transferencia' => $transf,
            ];
        }

        // Totales globales acumulados del periodo filtrado para las tarjetas KPI
        $allPeriodFacturas = Factura::query()
            ->with(['pagos', 'detalles.lote', 'detalles.producto'])
            ->where('estado', 'VALIDADA')
            ->whereBetween('fecha', [$this->fechaInicio.' 00:00:00', $this->fechaFin.' 23:59:59'])
            ->when($this->filtroSucursalId, fn ($q) => $q->where('sucursal_id', $this->filtroSucursalId))
            ->when($this->filtroUsuarioUtilidadId, fn ($q) => $q->where('usuario_creador_id', $this->filtroUsuarioUtilidadId))
            ->when($this->filtroCajaUtilidadId, fn ($q) => $q->where('caja_id', $this->filtroCajaUtilidadId))
            ->get();

        $totVentas = 0;
        $totCosto = 0;
        $totEfectivo = 0;
        $totQr = 0;
        $totTransf = 0;

        foreach ($allPeriodFacturas as $f) {
            $totVentas += (float) $f->total;
            foreach ($f->detalles as $det) {
                $costoUnit = (float) ($det->lote->precio_compra ?? $det->producto->precio_compra ?? 0);
                $totCosto += (float) $det->cantidad * $costoUnit;
            }
            $totEfectivo += (float) $f->pagos->where('tipo_pago', 'EFECTIVO')->sum(fn ($p) => $p->monto - $p->cambio);
            $totQr += (float) $f->pagos->where('tipo_pago', 'QR')->sum('monto');
            $totTransf += (float) $f->pagos->where('tipo_pago', 'TRANSFERENCIA')->sum('monto');
        }

        $totUtilidad = $totVentas - $totCosto;
        $margenGlobal = $totVentas > 0 ? ($totUtilidad / $totVentas) * 100 : 0;

        $paginatorItems = new LengthAwarePaginator(
            $facturasData,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $kpis = [
            'totalVentas' => $totVentas,
            'totalCosto' => $totCosto,
            'totalUtilidad' => $totUtilidad,
            'margenGlobal' => $margenGlobal,
            'totalEfectivo' => $totEfectivo,
            'totalQr' => $totQr,
            'totalTransferencia' => $totTransf,
            'cantidadFacturas' => $paginator->total(),
        ];

        return [$paginatorItems, $kpis];
    }

    /**
     * Reporte 3: Control de Cajas (Turnos, Aperturas, Cierres, Medios de Pago y Cuadre de Arqueo).
     */
    private function obtenerDatosCajas(): array
    {
        $baseQuery = Caja::query()
            ->with(['usuario', 'sucursal', 'puntoVenta', 'pagos.factura'])
            ->when($this->filtroUsuarioCajaId, fn ($q) => $q->where('usuario_id', $this->filtroUsuarioCajaId))
            ->when($this->filtroSucursalId, fn ($q) => $q->where('sucursal_id', $this->filtroSucursalId))
            ->when($this->filtroEstadoCaja, fn ($q) => $q->where('estado', $this->filtroEstadoCaja))
            ->where(function ($q) {
                $q->whereBetween('fecha_apertura', [$this->fechaInicio.' 00:00:00', $this->fechaFin.' 23:59:59'])
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('fecha_cierre')
                            ->whereBetween('fecha_cierre', [$this->fechaInicio.' 00:00:00', $this->fechaFin.' 23:59:59']);
                    });
            })
            ->orderByDesc('fecha_apertura');

        $paginator = $baseQuery->paginate($this->perPage);

        $cajasData = [];
        foreach ($paginator->items() as $caja) {
            $montoAp = (float) $caja->monto_apertura;
            $montoCi = $caja->monto_cierre !== null ? (float) $caja->monto_cierre : null;

            $pagosValidos = $caja->pagos->filter(fn ($p) => ! $p->factura || $p->factura->estado !== 'ANULADA');

            $efectivo = (float) $pagosValidos->where('tipo_pago', 'EFECTIVO')->sum(fn ($p) => $p->monto - $p->cambio);
            $qr = (float) $pagosValidos->where('tipo_pago', 'QR')->sum('monto');
            $transf = (float) $pagosValidos->where('tipo_pago', 'TRANSFERENCIA')->sum('monto');
            $ventas = $efectivo + $qr + $transf;

            $saldoEsperado = $montoAp + $efectivo;
            $diferencia = $montoCi !== null ? ($montoCi - $saldoEsperado) : null;

            $cajasData[] = [
                'caja' => $caja,
                'monto_apertura' => $montoAp,
                'monto_cierre' => $montoCi,
                'efectivo' => $efectivo,
                'qr' => $qr,
                'transferencia' => $transf,
                'total_ventas' => $ventas,
                'saldo_esperado' => $saldoEsperado,
                'diferencia' => $diferencia,
            ];
        }

        // Totales globales para KPIs
        $todasCajas = Caja::query()
            ->with(['pagos.factura'])
            ->when($this->filtroUsuarioCajaId, fn ($q) => $q->where('usuario_id', $this->filtroUsuarioCajaId))
            ->when($this->filtroSucursalId, fn ($q) => $q->where('sucursal_id', $this->filtroSucursalId))
            ->when($this->filtroEstadoCaja, fn ($q) => $q->where('estado', $this->filtroEstadoCaja))
            ->where(function ($q) {
                $q->whereBetween('fecha_apertura', [$this->fechaInicio.' 00:00:00', $this->fechaFin.' 23:59:59'])
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('fecha_cierre')
                            ->whereBetween('fecha_cierre', [$this->fechaInicio.' 00:00:00', $this->fechaFin.' 23:59:59']);
                    });
            })
            ->get();

        $totAp = 0;
        $totEf = 0;
        $totQr = 0;
        $totTr = 0;

        foreach ($todasCajas as $c) {
            $totAp += (float) $c->monto_apertura;
            $pv = $c->pagos->filter(fn ($p) => ! $p->factura || $p->factura->estado !== 'ANULADA');
            $totEf += (float) $pv->where('tipo_pago', 'EFECTIVO')->sum(fn ($p) => $p->monto - $p->cambio);
            $totQr += (float) $pv->where('tipo_pago', 'QR')->sum('monto');
            $totTr += (float) $pv->where('tipo_pago', 'TRANSFERENCIA')->sum('monto');
        }

        $paginatorItems = new LengthAwarePaginator(
            $cajasData,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $kpis = [
            'totalAperturas' => $totAp,
            'totalEfectivo' => $totEf,
            'totalQr' => $totQr,
            'totalTransferencia' => $totTr,
            'totalVentas' => $totEf + $totQr + $totTr,
            'cantidadCajas' => $paginator->total(),
        ];

        return [$paginatorItems, $kpis];
    }

    /**
     * Reporte 4: Clientes Frecuentes (Ranking por Frecuencia, Monto Acumulado y Ticket Promedio).
     */
    private function obtenerDatosClientes(): array
    {
        $baseQuery = Factura::query()
            ->select('cliente_id')
            ->selectRaw('MAX(razon_social) as nombre_cliente')
            ->selectRaw('MAX(nit) as nit_cliente')
            ->selectRaw('COUNT(id) as total_facturas')
            ->selectRaw('SUM(total) as total_comprado')
            ->selectRaw('AVG(total) as ticket_promedio')
            ->selectRaw('MAX(fecha) as ultima_compra')
            ->where('estado', 'VALIDADA')
            ->whereNotNull('cliente_id')
            ->whereBetween('fecha', [$this->fechaInicio.' 00:00:00', $this->fechaFin.' 23:59:59'])
            ->when($this->filtroSucursalId, fn ($q) => $q->where('sucursal_id', $this->filtroSucursalId))
            ->when($this->search, function ($q) {
                $termino = trim($this->search);
                $q->where(function ($sub) use ($termino) {
                    $sub->where('razon_social', 'like', "%{$termino}%")
                        ->orWhere('nit', 'like', "%{$termino}%");
                });
            })
            ->groupBy('cliente_id')
            ->orderByDesc('total_facturas')
            ->orderByDesc('total_comprado');

        $paginator = $baseQuery->paginate($this->perPage);

        $clienteIds = collect($paginator->items())->pluck('cliente_id');
        $clientesModels = Cliente::whereIn('id', $clienteIds)->get()->keyBy('id');

        $rows = [];
        foreach ($paginator->items() as $item) {
            $rows[] = [
                'cliente_id' => $item->cliente_id,
                'nombre_cliente' => $item->nombre_cliente,
                'nit_cliente' => $item->nit_cliente,
                'total_facturas' => (int) $item->total_facturas,
                'total_comprado' => (float) $item->total_comprado,
                'ticket_promedio' => (float) $item->ticket_promedio,
                'ultima_compra' => $item->ultima_compra,
                'modelo' => $clientesModels->get($item->cliente_id),
            ];
        }

        // Totales globales de clientes
        $totalesGlobales = Factura::query()
            ->where('estado', 'VALIDADA')
            ->whereNotNull('cliente_id')
            ->whereBetween('fecha', [$this->fechaInicio.' 00:00:00', $this->fechaFin.' 23:59:59'])
            ->when($this->filtroSucursalId, fn ($q) => $q->where('sucursal_id', $this->filtroSucursalId))
            ->selectRaw('COUNT(id) as total_facturas, SUM(total) as total_comprado')
            ->first();

        $granTotalFacturas = (int) ($totalesGlobales->total_facturas ?? 0);
        $granTotalComprado = (float) ($totalesGlobales->total_comprado ?? 0);
        $ticketPromedioGlobal = $granTotalFacturas > 0 ? ($granTotalComprado / $granTotalFacturas) : 0;

        $paginatorItems = new LengthAwarePaginator(
            $rows,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $kpis = [
            'totalClientes' => $paginator->total(),
            'totalFacturas' => $granTotalFacturas,
            'totalComprado' => $granTotalComprado,
            'ticketPromedio' => $ticketPromedioGlobal,
        ];

        return [$paginatorItems, $kpis];
    }
}
