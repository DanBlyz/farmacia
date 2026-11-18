<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario</title>
    <style>
        @page {
            size: letter landscape;
            margin: 12mm 10mm 15mm 10mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            color: #1e293b;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header table {
            width: 100%;
        }
        .title {
            font-size: 15pt;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .subtitle {
            font-size: 8.5pt;
            color: #475569;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 7.5pt;
            font-weight: bold;
            border-radius: 4px;
            background-color: #f1f5f9;
            color: #334155;
        }
        .filters-summary {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 10px;
            margin-bottom: 12px;
            font-size: 8pt;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data-table th {
            background-color: #0f766e;
            color: #ffffff;
            font-weight: bold;
            font-size: 7.5pt;
            text-transform: uppercase;
            padding: 6px 4px;
            border: 1px solid #0d9488;
        }
        table.data-table td {
            padding: 4px 5px;
            border: 1px solid #e2e8f0;
            font-size: 7.5pt;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .total-card {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }
        .total-box {
            background-color: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 6px;
            padding: 8px 12px;
            text-align: right;
        }
        .total-box-label {
            font-size: 7.5pt;
            color: #166534;
            font-weight: bold;
            text-transform: uppercase;
        }
        .total-box-val {
            font-size: 11pt;
            color: #14532d;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            font-size: 7pt;
            color: #64748b;
            border-top: 1px solid #cbd5e1;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td style="width: 60%; vertical-align: middle;">
                    <div class="title">Reporte de Inventario (Kardex Físico-Valorado)</div>
                    <div class="subtitle">Movimientos de Ingresos, Salidas, Stock Inicial / Final y Valuación</div>
                </td>
                <td style="width: 40%; text-align: right; vertical-align: middle;">
                    <div style="font-weight: bold; font-size: 10pt; color: #0f766e;">FARMA ERP - FARMACIA KERIGMA</div>
                    <div style="font-size: 7.5pt; color: #64748b;">Generado: {{ $fechaImpresion }} | Por: {{ $usuarioImpresion }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="filters-summary">
        <table style="width: 100%;">
            <tr>
                <td style="width: 25%;"><strong>Periodo:</strong> {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</td>
                <td style="width: 25%;"><strong>Sucursal:</strong> {{ $sucursal ? $sucursal->nombre : 'Todas las sucursales' }}</td>
                <td style="width: 30%;"><strong>Filtro Producto:</strong> {{ $productoFiltro ? $productoFiltro->nombre : 'Todos los productos activos' }}</td>
                <td style="width: 20%; text-align: right;"><strong>Total Ítems:</strong> {{ count($items) }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">#</th>
                <th style="width: 10%;">Código</th>
                <th style="width: 27%;" class="text-left">Producto</th>
                <th style="width: 7%;" class="text-right">Stock Inicial</th>
                <th style="width: 7%;" class="text-right">Ingresos</th>
                <th style="width: 7%;" class="text-right">Salidas</th>
                <th style="width: 7%;" class="text-right">Stock Final</th>
                <th style="width: 8%;" class="text-right">P. Compra</th>
                <th style="width: 8%;" class="text-right">P. Venta</th>
                <th style="width: 8%;" class="text-right">Val. Compra</th>
                <th style="width: 8%;" class="text-right">Val. Venta</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td class="font-mono text-center">{{ $item['producto']->codigo_barras ?? $item['producto']->codigo ?? 'S/C' }}</td>
                    <td class="text-left">
                        <strong>{{ $item['producto']->nombre }}</strong>
                        @if($item['producto']->presentacion)
                            <span style="color: #64748b; font-size: 6.5pt;">({{ $item['producto']->presentacion }})</span>
                        @endif
                    </td>
                    <td class="text-right font-mono">{{ number_format($item['stock_inicial'], 0) }}</td>
                    <td class="text-right font-mono" style="color: #166534;">+{{ number_format($item['ingresos'], 0) }}</td>
                    <td class="text-right font-mono" style="color: #b91c1c;">-{{ number_format($item['salidas'], 0) }}</td>
                    <td class="text-right font-mono font-bold" style="background-color: #f1f5f9;">{{ number_format($item['stock_final'], 0) }}</td>
                    <td class="text-right font-mono">Bs. {{ number_format($item['precio_compra'], 2) }}</td>
                    <td class="text-right font-mono">Bs. {{ number_format($item['precio_venta'], 2) }}</td>
                    <td class="text-right font-mono font-bold">Bs. {{ number_format($item['valor_compra'], 2) }}</td>
                    <td class="text-right font-mono font-bold" style="color: #0f766e;">Bs. {{ number_format($item['valor_venta'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 15px; color: #64748b;">
                        No se encontraron registros de inventario para los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="total-card">
        <tr>
            <td style="width: 25%; padding-right: 6px;">
                <div class="total-box" style="background-color: #f8fafc; border-color: #cbd5e1;">
                    <div class="total-box-label" style="color: #475569;">Total Unidades Ingresadas</div>
                    <div class="total-box-val" style="color: #166534;">+{{ number_format($totalIngresosPeriodo, 0) }} u.</div>
                </div>
            </td>
            <td style="width: 25%; padding: 0 4px;">
                <div class="total-box" style="background-color: #f8fafc; border-color: #cbd5e1;">
                    <div class="total-box-label" style="color: #475569;">Total Unidades Salientes</div>
                    <div class="total-box-val" style="color: #b91c1c;">-{{ number_format($totalSalidasPeriodo, 0) }} u.</div>
                </div>
            </td>
            <td style="width: 25%; padding: 0 4px;">
                <div class="total-box" style="background-color: #eff6ff; border-color: #93c5fd;">
                    <div class="total-box-label" style="color: #1e40af;">Valoración al Costo (Compra)</div>
                    <div class="total-box-val" style="color: #1e3a8a;">Bs. {{ number_format($totalValuacionCompra, 2) }}</div>
                </div>
            </td>
            <td style="width: 25%; padding-left: 6px;">
                <div class="total-box" style="background-color: #f0fdf4; border-color: #86efac;">
                    <div class="total-box-label" style="color: #166534;">Valoración Proyectada (Venta)</div>
                    <div class="total-box-val" style="color: #14532d;">Bs. {{ number_format($totalValuacionVenta, 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">Farma ERP &copy; Sistema Farmacéutico y Facturación SIAT Bolivia</td>
                <td style="width: 50%; text-align: right;">Documento Informativo y de Control de Existencias</td>
            </tr>
        </table>
    </div>
</body>
</html>