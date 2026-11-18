<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Utilidades y Rentabilidad</title>
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
            border-radius: 6px;
            padding: 8px 10px;
            text-align: right;
            border: 1px solid #cbd5e1;
        }
        .total-box-label {
            font-size: 7.2pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .total-box-val {
            font-size: 10.5pt;
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
                    <div class="title">Reporte de Utilidades y Rentabilidad</div>
                    <div class="subtitle">Margen Bruto sobre Costo de Lote, Facturación Validada y Medios de Pago</div>
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
                <td style="width: 25%;"><strong>Usuario / Vendedor:</strong> {{ $usuarioFiltro ? $usuarioFiltro->name : 'Todos los usuarios' }}</td>
                <td style="width: 25%; text-align: right;"><strong>Caja N°:</strong> {{ $cajaFiltro ? 'Caja #'.$cajaFiltro->id : 'Todas las cajas' }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;">N° Fact.</th>
                <th style="width: 11%;">Fecha / Hora</th>
                <th style="width: 14%;" class="text-left">Usuario (Cajero)</th>
                <th style="width: 6%;">Caja</th>
                <th style="width: 17%;" class="text-left">Cliente</th>
                <th style="width: 8%;" class="text-right">Venta Total</th>
                <th style="width: 8%;" class="text-right">Costo Lote</th>
                <th style="width: 8%;" class="text-right">Utilidad</th>
                <th style="width: 6%;" class="text-center">Margen</th>
                <th style="width: 8%;" class="text-right">Efectivo</th>
                <th style="width: 8%;" class="text-right">QR / Transf.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($facturasData as $row)
                @php $f = $row['factura']; @endphp
                <tr>
                    <td class="text-center font-mono font-bold">#{{ $f->numero_factura }}</td>
                    <td class="text-center font-mono">{{ \Carbon\Carbon::parse($f->fecha)->format('d/m/Y H:i') }}</td>
                    <td class="text-left">{{ $f->usuarioCreador->name ?? 'Usuario Sistema' }}</td>
                    <td class="text-center font-mono">
                        {{ $f->caja_id ? 'C#'.$f->caja_id : 'S/C' }}
                    </td>
                    <td class="text-left">
                        <strong>{{ Str::limit($f->razon_social, 20) }}</strong>
                        <div style="font-size: 6.5pt; color: #64748b;">NIT/CI: {{ $f->nit }}</div>
                    </td>
                    <td class="text-right font-mono font-bold">Bs. {{ number_format($f->total, 2) }}</td>
                    <td class="text-right font-mono" style="color: #475569;">Bs. {{ number_format($row['costo'], 2) }}</td>
                    <td class="text-right font-mono font-bold" style="color: {{ $row['utilidad'] >= 0 ? '#166534' : '#b91c1c' }};">
                        Bs. {{ number_format($row['utilidad'], 2) }}
                    </td>
                    <td class="text-center font-mono font-bold" style="color: {{ $row['margen'] >= 20 ? '#166534' : '#d97706' }};">
                        {{ number_format($row['margen'], 1) }}%
                    </td>
                    <td class="text-right font-mono">Bs. {{ number_format($row['efectivo'], 2) }}</td>
                    <td class="text-right font-mono">Bs. {{ number_format($row['qr'] + $row['transferencia'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 15px; color: #64748b;">
                        No se registraron facturas validadas en el rango de fechas seleccionado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="total-card">
        <tr>
            <td style="width: 20%; padding-right: 4px;">
                <div class="total-box" style="background-color: #eff6ff; border-color: #93c5fd;">
                    <div class="total-box-label" style="color: #1e40af;">Total Ventas (Ingresos)</div>
                    <div class="total-box-val" style="color: #1e3a8a;">Bs. {{ number_format($totalVentas, 2) }}</div>
                </div>
            </td>
            <td style="width: 20%; padding: 0 4px;">
                <div class="total-box" style="background-color: #f8fafc; border-color: #cbd5e1;">
                    <div class="total-box-label" style="color: #475569;">Costo Mercadería (Lotes)</div>
                    <div class="total-box-val" style="color: #334155;">Bs. {{ number_format($totalCosto, 2) }}</div>
                </div>
            </td>
            <td style="width: 20%; padding: 0 4px;">
                <div class="total-box" style="background-color: #f0fdf4; border-color: #86efac;">
                    <div class="total-box-label" style="color: #166534;">Utilidad Bruta (Ganancia)</div>
                    <div class="total-box-val" style="color: #14532d;">Bs. {{ number_format($totalUtilidad, 2) }} ({{ number_format($margenGlobal, 1) }}%)</div>
                </div>
            </td>
            <td style="width: 20%; padding: 0 4px;">
                <div class="total-box" style="background-color: #f0fdfa; border-color: #99f6e4;">
                    <div class="total-box-label" style="color: #0f766e;">Cobrado en Efectivo</div>
                    <div class="total-box-val" style="color: #115e59;">Bs. {{ number_format($totalEfectivo, 2) }}</div>
                </div>
            </td>
            <td style="width: 20%; padding-left: 4px;">
                <div class="total-box" style="background-color: #fefce8; border-color: #fef08a;">
                    <div class="total-box-label" style="color: #a16207;">QR (Bs. {{ number_format($totalQr, 2) }}) / Transf.</div>
                    <div class="total-box-val" style="color: #854d0e;">Bs. {{ number_format($totalQr + $totalTransferencia, 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">Farma ERP &copy; Sistema Farmacéutico y Facturación SIAT Bolivia</td>
                <td style="width: 50%; text-align: right;">Reporte de Utilidades y Cuadre Financiero</td>
            </tr>
        </table>
    </div>
</body>
</html>