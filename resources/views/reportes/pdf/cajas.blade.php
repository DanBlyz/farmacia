<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Control de Cajas</title>
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
        .badge {
            display: inline-block;
            padding: 1px 5px;
            font-size: 6.5pt;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .badge-open {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }
        .badge-closed {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
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
                    <div class="title">Reporte de Control de Cajas y Arqueos</div>
                    <div class="subtitle">Auditoría de Turnos, Cobranzas en Efectivo, QR, Transferencia y Cuadre</div>
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
                <td style="width: 25%;"><strong>Cajero / Usuario:</strong> {{ $usuarioFiltro ? $usuarioFiltro->name : 'Todos los usuarios' }}</td>
                <td style="width: 25%; text-align: right;"><strong>Estado:</strong> {{ $estadoCaja ? $estadoCaja : 'Todos los estados' }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">Caja #</th>
                <th style="width: 12%;" class="text-left">Cajero / Usuario</th>
                <th style="width: 12%;">Apertura</th>
                <th style="width: 12%;">Cierre</th>
                <th style="width: 6%;">Estado</th>
                <th style="width: 8%;" class="text-right">Monto Apertura</th>
                <th style="width: 8%;" class="text-right">Efectivo Cobrado</th>
                <th style="width: 8%;" class="text-right">QR</th>
                <th style="width: 8%;" class="text-right">Transferencia</th>
                <th style="width: 8%;" class="text-right">Monto Cierre</th>
                <th style="width: 8%;" class="text-right">Diferencia</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cajasData as $row)
                @php $c = $row['caja']; @endphp
                <tr>
                    <td class="text-center font-mono font-bold">#{{ $c->id }}</td>
                    <td class="text-left">
                        <strong>{{ $c->usuario->name ?? 'Usuario Desconocido' }}</strong>
                        @if($c->puntoVenta)
                            <div style="font-size: 6.5pt; color: #64748b;">POS: {{ $c->puntoVenta->nombre }}</div>
                        @endif
                    </td>
                    <td class="text-center font-mono">{{ \Carbon\Carbon::parse($c->fecha_apertura)->format('d/m/Y H:i') }}</td>
                    <td class="text-center font-mono">
                        {{ $c->fecha_cierre ? \Carbon\Carbon::parse($c->fecha_cierre)->format('d/m/Y H:i') : '--' }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $c->estado === 'ABIERTA' ? 'badge-open' : 'badge-closed' }}">
                            {{ $c->estado }}
                        </span>
                    </td>
                    <td class="text-right font-mono">Bs. {{ number_format($row['monto_apertura'], 2) }}</td>
                    <td class="text-right font-mono font-bold" style="color: #0f766e;">Bs. {{ number_format($row['efectivo'], 2) }}</td>
                    <td class="text-right font-mono">Bs. {{ number_format($row['qr'], 2) }}</td>
                    <td class="text-right font-mono">Bs. {{ number_format($row['transferencia'], 2) }}</td>
                    <td class="text-right font-mono font-bold">
                        {{ $row['monto_cierre'] !== null ? 'Bs. ' . number_format($row['monto_cierre'], 2) : 'Sin Cerrar' }}
                    </td>
                    <td class="text-right font-mono font-bold">
                        @if($row['diferencia'] !== null)
                            @if(abs($row['diferencia']) < 0.01)
                                <span style="color: #166534;">Bs. 0.00 (OK)</span>
                            @elseif($row['diferencia'] > 0)
                                <span style="color: #1e40af;">+Bs. {{ number_format($row['diferencia'], 2) }} (Sob.)</span>
                            @else
                                <span style="color: #b91c1c;">-Bs. {{ number_format(abs($row['diferencia']), 2) }} (Falt.)</span>
                            @endif
                        @else
                            <span style="color: #94a3b8;">--</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 15px; color: #64748b;">
                        No se encontraron registros de caja para los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="total-card">
        <tr>
            <td style="width: 20%; padding-right: 4px;">
                <div class="total-box" style="background-color: #f8fafc; border-color: #cbd5e1;">
                    <div class="total-box-label" style="color: #475569;">Total Aperturas (Fondo)</div>
                    <div class="total-box-val" style="color: #334155;">Bs. {{ number_format($totalAperturas, 2) }}</div>
                </div>
            </td>
            <td style="width: 20%; padding: 0 4px;">
                <div class="total-box" style="background-color: #f0fdf4; border-color: #86efac;">
                    <div class="total-box-label" style="color: #166534;">Cobrado en Efectivo</div>
                    <div class="total-box-val" style="color: #14532d;">Bs. {{ number_format($totalEfectivo, 2) }}</div>
                </div>
            </td>
            <td style="width: 20%; padding: 0 4px;">
                <div class="total-box" style="background-color: #eff6ff; border-color: #93c5fd;">
                    <div class="total-box-label" style="color: #1e40af;">Cobrado por QR</div>
                    <div class="total-box-val" style="color: #1e3a8a;">Bs. {{ number_format($totalQr, 2) }}</div>
                </div>
            </td>
            <td style="width: 20%; padding: 0 4px;">
                <div class="total-box" style="background-color: #faf5ff; border-color: #d8b4fe;">
                    <div class="total-box-label" style="color: #6b21a8;">Cobrado por Transferencia</div>
                    <div class="total-box-val" style="color: #581c87;">Bs. {{ number_format($totalTransferencia, 2) }}</div>
                </div>
            </td>
            <td style="width: 20%; padding-left: 4px;">
                <div class="total-box" style="background-color: #f0fdfa; border-color: #5eead4;">
                    <div class="total-box-label" style="color: #0f766e;">Total Ventas Registradas</div>
                    <div class="total-box-val" style="color: #115e59;">Bs. {{ number_format($totalVentas, 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">Farma ERP &copy; Sistema Farmacéutico y Facturación SIAT Bolivia</td>
                <td style="width: 50%; text-align: right;">Auditoría de Cajas y Arqueos de Turno</td>
            </tr>
        </table>
    </div>
</body>
</html>