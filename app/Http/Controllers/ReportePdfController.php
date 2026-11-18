<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportePdfController extends Controller
{
    /**
     * Reporte de Inventario (Ingresos, Salidas, Stock Inicial y Final, Valuación Compra y Venta).
     */
    public function inventarioPdf(Request $request): Response
    {
        abort_if(! $request->user()?->tienePermiso('reportes.inventario.ver'), 403, 'No tiene autorización para exportar este reporte.');

        $fechaInicio = $request->query('fechaInicio', Carbon::now()->startOfMonth()->toDateString());
        $fechaFin = $request->query('fechaFin', Carbon::now()->toDateString());
        $sucursalId = $request->filled('sucursal_id') ? (int) $request->query('sucursal_id') : null;
        $productoId = $request->filled('producto_id') ? (int) $request->query('producto_id') : null;

        $sucursal = $sucursalId ? Sucursal::find($sucursalId) : null;
        $productoFiltro = $productoId ? Producto::find($productoId) : null;

        $productos = Producto::query()
            ->when($productoId, fn ($q) => $q->where('id', $productoId))
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre', 'asc')
            ->get();

        $items = [];
        $totalValuacionCompra = 0;
        $totalValuacionVenta = 0;
        $totalIngresosPeriodo = 0;
        $totalSalidasPeriodo = 0;

        foreach ($productos as $prod) {
            $movsPrevios = Movimiento::query()
                ->where('producto_id', $prod->id)
                ->when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId))
                ->where('fecha', '<', $fechaInicio.' 00:00:00');

            $stockInicial = (float) ($movsPrevios->sum('ingreso') - $movsPrevios->sum('salida'));
            $stockInicial = max(0, $stockInicial);

            $ingresos = (float) Movimiento::query()
                ->where('producto_id', $prod->id)
                ->when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId))
                ->whereBetween('fecha', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
                ->sum('ingreso');

            $salidas = (float) Movimiento::query()
                ->where('producto_id', $prod->id)
                ->when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId))
                ->whereBetween('fecha', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
                ->sum('salida');

            $stockFinal = $stockInicial + $ingresos - $salidas;

            $precioCompra = (float) ($prod->precio_compra ?? 0);
            $precioVenta = (float) ($prod->precio_venta ?? 0);

            $valCompra = $stockFinal * $precioCompra;
            $valVenta = $stockFinal * $precioVenta;

            $totalValuacionCompra += $valCompra;
            $totalValuacionVenta += $valVenta;
            $totalIngresosPeriodo += $ingresos;
            $totalSalidasPeriodo += $salidas;

            $items[] = [
                'producto' => $prod,
                'stock_inicial' => $stockInicial,
                'ingresos' => $ingresos,
                'salidas' => $salidas,
                'stock_final' => $stockFinal,
                'precio_compra' => $precioCompra,
                'precio_venta' => $precioVenta,
                'valor_compra' => $valCompra,
                'valor_venta' => $valVenta,
            ];
        }

        $pdf = Pdf::loadView('reportes.pdf.inventario', [
            'items' => $items,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'sucursal' => $sucursal,
            'productoFiltro' => $productoFiltro,
            'totalValuacionCompra' => $totalValuacionCompra,
            'totalValuacionVenta' => $totalValuacionVenta,
            'totalIngresosPeriodo' => $totalIngresosPeriodo,
            'totalSalidasPeriodo' => $totalSalidasPeriodo,
            'fechaImpresion' => Carbon::now()->format('d/m/Y H:i:s'),
            'usuarioImpresion' => $request->user()->name,
        ])->setPaper('letter', 'landscape');

        return $pdf->stream('reporte_inventario_'.Carbon::now()->format('Ymd_His').'.pdf');
    }

    /**
     * Reporte de Utilidades (Ganancias por Factura, Costos de Lote y Arqueo por Método de Pago).
     */
    public function utilidadesPdf(Request $request): Response
    {
        abort_if(! $request->user()?->tienePermiso('reportes.utilidades.ver'), 403, 'No tiene autorización para exportar este reporte.');

        $fechaInicio = $request->query('fechaInicio', Carbon::now()->startOfMonth()->toDateString());
        $fechaFin = $request->query('fechaFin', Carbon::now()->toDateString());
        $sucursalId = $request->filled('sucursal_id') ? (int) $request->query('sucursal_id') : null;
        $usuarioId = $request->filled('usuario_id') ? (int) $request->query('usuario_id') : null;
        $cajaId = $request->filled('caja_id') ? (int) $request->query('caja_id') : null;

        $sucursal = $sucursalId ? Sucursal::find($sucursalId) : null;
        $usuarioFiltro = $usuarioId ? User::find($usuarioId) : null;
        $cajaFiltro = $cajaId ? Caja::find($cajaId) : null;

        $facturas = Factura::query()
            ->with(['usuarioCreador', 'caja', 'pagos', 'detalles.lote', 'detalles.producto'])
            ->where('estado', 'VALIDADA')
            ->whereBetween('fecha', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
            ->when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId))
            ->when($usuarioId, fn ($q) => $q->where('usuario_creador_id', $usuarioId))
            ->when($cajaId, fn ($q) => $q->where('caja_id', $cajaId))
            ->orderBy('fecha', 'asc')
            ->get();

        $facturasData = [];
        $totalVentas = 0;
        $totalCosto = 0;
        $totalUtilidad = 0;
        $totalEfectivo = 0;
        $totalQr = 0;
        $totalTransferencia = 0;

        foreach ($facturas as $factura) {
            $montoVenta = (float) $factura->total;
            $costoFactura = 0;

            foreach ($factura->detalles as $det) {
                $costoUnitario = (float) ($det->lote->precio_compra ?? $det->producto->precio_compra ?? 0);
                $costoFactura += (float) $det->cantidad * $costoUnitario;
            }

            $utilidadFactura = $montoVenta - $costoFactura;

            $efectivo = (float) $factura->pagos->where('tipo_pago', 'EFECTIVO')->sum(fn ($p) => $p->monto - $p->cambio);
            $qr = (float) $factura->pagos->where('tipo_pago', 'QR')->sum('monto');
            $transf = (float) $factura->pagos->where('tipo_pago', 'TRANSFERENCIA')->sum('monto');

            $totalVentas += $montoVenta;
            $totalCosto += $costoFactura;
            $totalUtilidad += $utilidadFactura;
            $totalEfectivo += $efectivo;
            $totalQr += $qr;
            $totalTransferencia += $transf;

            $facturasData[] = [
                'factura' => $factura,
                'costo' => $costoFactura,
                'utilidad' => $utilidadFactura,
                'margen' => $montoVenta > 0 ? ($utilidadFactura / $montoVenta) * 100 : 0,
                'efectivo' => $efectivo,
                'qr' => $qr,
                'transferencia' => $transf,
            ];
        }

        $pdf = Pdf::loadView('reportes.pdf.utilidades', [
            'facturasData' => $facturasData,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'sucursal' => $sucursal,
            'usuarioFiltro' => $usuarioFiltro,
            'cajaFiltro' => $cajaFiltro,
            'totalVentas' => $totalVentas,
            'totalCosto' => $totalCosto,
            'totalUtilidad' => $totalUtilidad,
            'totalEfectivo' => $totalEfectivo,
            'totalQr' => $totalQr,
            'totalTransferencia' => $totalTransferencia,
            'margenGlobal' => $totalVentas > 0 ? ($totalUtilidad / $totalVentas) * 100 : 0,
            'fechaImpresion' => Carbon::now()->format('d/m/Y H:i:s'),
            'usuarioImpresion' => $request->user()->name,
        ])->setPaper('letter', 'landscape');

        return $pdf->stream('reporte_utilidades_'.Carbon::now()->format('Ymd_His').'.pdf');
    }

    /**
     * Reporte de Control de Cajas (Turnos, Usuarios, Medios de Pago y Cuadre de Arqueo).
     */
    public function cajasPdf(Request $request): Response
    {
        abort_if(! $request->user()?->tienePermiso('reportes.cajas.ver'), 403, 'No tiene autorización para exportar este reporte.');

        $fechaInicio = $request->query('fechaInicio', Carbon::now()->startOfMonth()->toDateString());
        $fechaFin = $request->query('fechaFin', Carbon::now()->toDateString());
        $sucursalId = $request->filled('sucursal_id') ? (int) $request->query('sucursal_id') : null;
        $usuarioId = $request->filled('usuario_id') ? (int) $request->query('usuario_id') : null;
        $estadoCaja = $request->filled('estado') ? trim($request->query('estado')) : null;

        $sucursal = $sucursalId ? Sucursal::find($sucursalId) : null;
        $usuarioFiltro = $usuarioId ? User::find($usuarioId) : null;

        $cajas = Caja::query()
            ->with(['usuario', 'sucursal', 'puntoVenta', 'pagos.factura'])
            ->when($usuarioId, fn ($q) => $q->where('usuario_id', $usuarioId))
            ->when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId))
            ->when($estadoCaja, fn ($q) => $q->where('estado', $estadoCaja))
            ->where(function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha_apertura', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
                    ->orWhere(function ($sub) use ($fechaInicio, $fechaFin) {
                        $sub->whereNotNull('fecha_cierre')
                            ->whereBetween('fecha_cierre', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59']);
                    });
            })
            ->orderBy('fecha_apertura', 'asc')
            ->get();

        $cajasData = [];
        $totalAperturas = 0;
        $totalEfectivo = 0;
        $totalQr = 0;
        $totalTransferencia = 0;
        $totalVentas = 0;

        foreach ($cajas as $caja) {
            $montoAp = (float) $caja->monto_apertura;
            $montoCi = $caja->monto_cierre !== null ? (float) $caja->monto_cierre : null;

            $pagosValidos = $caja->pagos->filter(fn ($p) => ! $p->factura || $p->factura->estado !== 'ANULADA');

            $efectivo = (float) $pagosValidos->where('tipo_pago', 'EFECTIVO')->sum(fn ($p) => $p->monto - $p->cambio);
            $qr = (float) $pagosValidos->where('tipo_pago', 'QR')->sum('monto');
            $transf = (float) $pagosValidos->where('tipo_pago', 'TRANSFERENCIA')->sum('monto');
            $ventas = $efectivo + $qr + $transf;

            $saldoEsperado = $montoAp + $efectivo;
            $diferencia = $montoCi !== null ? ($montoCi - $saldoEsperado) : null;

            $totalAperturas += $montoAp;
            $totalEfectivo += $efectivo;
            $totalQr += $qr;
            $totalTransferencia += $transf;
            $totalVentas += $ventas;

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

        $pdf = Pdf::loadView('reportes.pdf.cajas', [
            'cajasData' => $cajasData,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'sucursal' => $sucursal,
            'usuarioFiltro' => $usuarioFiltro,
            'estadoCaja' => $estadoCaja,
            'totalAperturas' => $totalAperturas,
            'totalEfectivo' => $totalEfectivo,
            'totalQr' => $totalQr,
            'totalTransferencia' => $totalTransferencia,
            'totalVentas' => $totalVentas,
            'fechaImpresion' => Carbon::now()->format('d/m/Y H:i:s'),
            'usuarioImpresion' => $request->user()->name,
        ])->setPaper('letter', 'landscape');

        return $pdf->stream('reporte_cajas_'.Carbon::now()->format('Ymd_His').'.pdf');
    }

    /**
     * Reporte de Clientes Frecuentes (Ranking por Frecuencia de Compra y Facturación).
     */
    public function clientesPdf(Request $request): Response
    {
        abort_if(! $request->user()?->tienePermiso('reportes.clientes.ver'), 403, 'No tiene autorización para exportar este reporte.');

        $fechaInicio = $request->query('fechaInicio', Carbon::now()->startOfMonth()->toDateString());
        $fechaFin = $request->query('fechaFin', Carbon::now()->toDateString());
        $sucursalId = $request->filled('sucursal_id') ? (int) $request->query('sucursal_id') : null;

        $sucursal = $sucursalId ? Sucursal::find($sucursalId) : null;

        $clientesData = Factura::query()
            ->select('cliente_id')
            ->selectRaw('MAX(razon_social) as nombre_cliente')
            ->selectRaw('MAX(nit) as nit_cliente')
            ->selectRaw('COUNT(id) as total_facturas')
            ->selectRaw('SUM(total) as total_comprado')
            ->selectRaw('AVG(total) as ticket_promedio')
            ->selectRaw('MAX(fecha) as ultima_compra')
            ->where('estado', 'VALIDADA')
            ->whereNotNull('cliente_id')
            ->whereBetween('fecha', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
            ->when($sucursalId, fn ($q) => $q->where('sucursal_id', $sucursalId))
            ->groupBy('cliente_id')
            ->orderByDesc('total_facturas')
            ->orderByDesc('total_comprado')
            ->get();

        $clienteIds = $clientesData->pluck('cliente_id');
        $clientesModels = Cliente::whereIn('id', $clienteIds)->get()->keyBy('id');

        $granTotalFacturas = $clientesData->sum('total_facturas');
        $granTotalComprado = $clientesData->sum('total_comprado');

        $pdf = Pdf::loadView('reportes.pdf.clientes', [
            'clientesData' => $clientesData,
            'clientesModels' => $clientesModels,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'sucursal' => $sucursal,
            'granTotalFacturas' => $granTotalFacturas,
            'granTotalComprado' => $granTotalComprado,
            'ticketPromedioGlobal' => $granTotalFacturas > 0 ? ($granTotalComprado / $granTotalFacturas) : 0,
            'fechaImpresion' => Carbon::now()->format('d/m/Y H:i:s'),
            'usuarioImpresion' => $request->user()->name,
        ])->setPaper('letter', 'portrait');

        return $pdf->stream('reporte_clientes_frecuentes_'.Carbon::now()->format('Ymd_His').'.pdf');
    }
}
