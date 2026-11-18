<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Clientes Frecuentes</title>
    <style>
        @page {
            size: letter portrait;
            margin: 12mm 12mm 15mm 12mm;
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
            padding: 6px 5px;
            border: 1px solid #0d9488;
        }
        table.data-table td {
            padding: 5px 6px;
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
            font-size: 11pt;
            font-weight: bold;
        }
        .ranking-badge {
            display: inline-block;
            width: 16px;
            height: 16px;
            line-height: 16px;
            text-align: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 7pt;
        }
        .rank-top1 { background-color: #fef08a; color: #854d0e; }
        .rank-top2 { background-color: #e2e8f0; color: #334155; }
        .rank-top3 { background-color: #ffedd5; color: #9a3412; }
        .rank-other { background-color: #f1f5f9; color: #64748b; }
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
                    <div class="title">Ranking de Clientes Frecuentes</div>
                    <div class="subtitle">Frecuencia de Compra, Facturación Acumulada y Ticket Promedio</div>
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
                <td style="width: 35%;"><strong>Periodo:</strong> {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</td>
                <td style="width: 35%;"><strong>Sucursal:</strong> {{ $sucursal ? $sucursal->nombre : 'Todas las sucursales' }}</td>
                <td style="width: 30%; text-align: right;"><strong>Clientes Registrados:</strong> {{ count($clientesData) }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;">Rank</th>
                <th style="width: 34%;" class="text-left">Razón Social / Nombre del Cliente</th>
                <th style="width: 15%;" class="text-left">NIT / Cédula</th>
                <th style="width: 11%;" class="text-center">N° Facturas</th>
                <th style="width: 14%;" class="text-right">Total Comprado</th>
                <th style="width: 10%;" class="text-right">Ticket Prom.</th>
                <th style="width: 10%;" class="text-center">Última Compra</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clientesData as $index => $row)
                @php
                    $rankClass = match($index) {
                        0 => 'rank-top1',
                        1 => 'rank-top2',
                        2 => 'rank-top3',
                        default => 'rank-other',
                    };
                    $clienteModel = $clientesModels->get($row->cliente_id);
                @endphp
                <tr>
                    <td class="text-center">
                        <span class="ranking-badge {{ $rankClass }}">{{ $index + 1 }}</span>
                    </td>
                    <td class="text-left">
                        <strong>{{ $row->nombre_cliente }}</strong>
                        @if($clienteModel?->telefono)
                            <div style="font-size: 6.5pt; color: #64748b;">Tel: {{ $clienteModel->telefono }}</div>
                        @endif
                    </td>
                    <td class="text-left font-mono">
                        {{ $row->nit_cliente }}
                        @if($clienteModel?->complemento)
                            <span style="font-size: 6.5pt; color: #0f766e;">-{{ $clienteModel->complemento }}</span>
                        @endif
                    </td>
                    <td class="text-center font-mono font-bold" style="color: #0f766e;">
                        {{ $row->total_facturas }}
                    </td>
                    <td class="text-right font-mono font-bold">
                        Bs. {{ number_format($row->total_comprado, 2) }}
                    </td>
                    <td class="text-right font-mono">
                        Bs. {{ number_format($row->ticket_promedio, 2) }}
                    </td>
                    <td class="text-center font-mono">
                        {{ \Carbon\Carbon::parse($row->ultima_compra)->format('d/m/Y') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #64748b;">
                        No se encontraron registros de ventas a clientes en el rango de fechas seleccionado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="total-card">
        <tr>
            <td style="width: 33.3%; padding-right: 4px;">
                <div class="total-box" style="background-color: #f0fdfa; border-color: #99f6e4;">
                    <div class="total-box-label" style="color: #0f766e;">Total Facturas Emitidas</div>
                    <div class="total-box-val" style="color: #115e59;">{{ number_format($granTotalFacturas, 0) }} facturas</div>
                </div>
            </td>
            <td style="width: 33.3%; padding: 0 4px;">
                <div class="total-box" style="background-color: #eff6ff; border-color: #93c5fd;">
                    <div class="total-box-label" style="color: #1e40af;">Total Facturado Acumulado</div>
                    <div class="total-box-val" style="color: #1e3a8a;">Bs. {{ number_format($granTotalComprado, 2) }}</div>
                </div>
            </td>
            <td style="width: 33.3%; padding-left: 4px;">
                <div class="total-box" style="background-color: #f8fafc; border-color: #cbd5e1;">
                    <div class="total-box-label" style="color: #475569;">Ticket Promedio Global</div>
                    <div class="total-box-val" style="color: #334155;">Bs. {{ number_format($ticketPromedioGlobal, 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">Farma ERP &copy; Sistema Farmacéutico y Facturación SIAT Bolivia</td>
                <td style="width: 50%; text-align: right;">Inteligencia de Clientes y Fidelización</td>
            </tr>
        </table>
    </div>
</body>
</html>