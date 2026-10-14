<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura N° {{ $factura->numero_factura }}</title>
    <style>
        @page {
            size: 80mm {{ $altoMm ?? 150 }}mm;
            margin: 2mm 3mm 2mm 3mm;
        }
        html, body {
            width: 80mm;
            max-width: 80mm;
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 7.5pt;
            line-height: 1.15;
            color: #111111;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
        .font-bold {
            font-weight: bold;
        }
        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }
        .uppercase {
            text-transform: uppercase;
        }
        .border-top-dashed {
            border-top: 1px dashed #444444;
        }
        .border-bottom-dashed {
            border-bottom: 1px dashed #444444;
        }
        .border-dashed-y {
            border-top: 1px dashed #444444;
            border-bottom: 1px dashed #444444;
        }
        .my-1 {
            margin-top: 3pt;
            margin-bottom: 3pt;
        }
        .my-2 {
            margin-top: 5pt;
            margin-bottom: 5pt;
        }
        .py-1 {
            padding-top: 2pt;
            padding-bottom: 2pt;
        }
        .header-box {
            margin-bottom: 4pt;
        }
        .empresa-nombre {
            font-size: 9.5pt;
            font-weight: bold;
            letter-spacing: 0.5pt;
        }
        .sub-header {
            font-size: 6.8pt;
            color: #222222;
        }
        .factura-box {
            padding: 3pt 0;
            text-align: center;
        }
        .nro-factura {
            font-size: 9pt;
            font-weight: bold;
        }
        .cuf-title {
            font-size: 6pt;
            color: #444444;
            margin-top: 1pt;
        }
        .cuf-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 5.8pt;
            word-break: break-all;
            line-height: 1.1;
            color: #222222;
        }
        .datos-cliente {
            font-size: 7pt;
            margin: 3pt 0;
            line-height: 1.2;
        }
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            margin-top: 2pt;
        }
        table.items-table th {
            padding: 2pt 0;
            font-size: 6.8pt;
        }
        table.items-table td {
            padding: 2.5pt 0;
            vertical-align: top;
        }
        .item-nombre {
            font-weight: bold;
            font-size: 7.2pt;
        }
        .item-meta {
            font-size: 6pt;
            color: #444444;
        }
        .totales-box {
            font-size: 7.2pt;
            padding-top: 3pt;
            margin-top: 2pt;
        }
        .totales-row {
            display: table;
            width: 100%;
            margin-bottom: 1.5pt;
        }
        .totales-cell-left {
            display: table-cell;
            text-align: left;
        }
        .totales-cell-right {
            display: table-cell;
            text-align: right;
        }
        .total-pagar {
            font-size: 8.5pt;
            font-weight: bold;
        }
        .literal-box {
            font-size: 6.5pt;
            text-transform: uppercase;
            font-weight: bold;
            color: #333333;
            margin-top: 3pt;
            padding-top: 2pt;
            border-top: 0.5px dotted #777777;
        }
        .qr-box {
            text-align: center;
            margin: 4pt 0 2pt 0;
        }
        .qr-img {
            width: 78pt;
            height: 78pt;
            margin: 0 auto;
            display: block;
        }
        .leyendas-box {
            font-size: 5.8pt;
            text-align: center;
            color: #333333;
            line-height: 1.15;
            padding-top: 3pt;
            margin-top: 3pt;
        }
    </style>
</head>
<body>
    <!-- 1. Encabezado de la Farmacia -->
    <div class="header-box text-center">
        <div class="empresa-nombre uppercase">{{ config('siat.razon_social') }}</div>
        <div class="sub-header font-bold">{{ $factura->sucursal?->nombre ?? 'Casa Matriz' }}</div>
        <div class="sub-header">{{ $factura->sucursal?->direccion ?? 'Av. Principal N° 100' }}</div>
        <div class="sub-header">Tel: {{ config('siat.celular') }} - {{ config('siat.municipio') }}</div>
        <div class="sub-header font-bold">NIT: {{ config('siat.nit') }}</div>
    </div>

    <!-- 2. Información Fiscal de la Factura y CUF -->
    <div class="factura-box border-dashed-y">
        <div class="nro-factura">FACTURA N° {{ $factura->numero_factura }}</div>
        <div class="cuf-title font-bold">CÓDIGO DE AUTORIZACIÓN (CUF):</div>
        <div class="cuf-code">{{ $factura->cuf }}</div>
    </div>

    <!-- 3. Datos del Cliente y Emisión -->
    <div class="datos-cliente">
        <div><span class="font-bold">Fecha:</span> {{ $factura->fecha ? $factura->fecha->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</div>
        <div><span class="font-bold">Señor(es):</span> {{ $factura->razon_social }}</div>
        <div><span class="font-bold">NIT/CI:</span> {{ $factura->nit }}</div>
        <div><span class="font-bold">Cajero(a):</span> {{ $factura->usuarioCreador?->name ?? 'Operador' }}</div>
    </div>

    <!-- 4. Detalle de Medicamentos / Ítems -->
    <table class="items-table border-top-dashed">
        <thead>
            <tr class="border-bottom-dashed">
                <th class="text-left">DETALLE</th>
                <th class="text-right" style="width: 25pt;">CANT</th>
                <th class="text-right" style="width: 42pt;">SUBT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factura->detalles as $det)
                <tr>
                    <td class="text-left">
                        <div class="item-nombre">{{ $det->nombre_producto }}</div>
                        <div class="item-meta">Lote: {{ $det->numero_lote }} - Venc: {{ $det->fecha_vencimiento ? \Carbon\Carbon::parse($det->fecha_vencimiento)->format('d/m/Y') : 'S/F' }}</div>
                        <div class="item-meta">P. Unit: Bs. {{ number_format((float) $det->precio, 2) }} @if((float) $det->descuento > 0) (Desc: Bs. {{ number_format((float) $det->descuento, 2) }}) @endif</div>
                    </td>
                    <td class="text-right font-bold">{{ number_format((float) $det->cantidad, 0) }}</td>
                    <td class="text-right font-bold">Bs. {{ number_format((float) $det->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 5. Totales y Descuentos -->
    <div class="totales-box border-top-dashed">
        @php
            $subtotalGeneral = (float) $factura->detalles->sum('total');
        @endphp

        @if((float) $factura->descuento_adicional > 0)
            <div class="totales-row">
                <span class="totales-cell-left">Subtotal:</span>
                <span class="totales-cell-right">Bs. {{ number_format($subtotalGeneral, 2) }}</span>
            </div>
            <div class="totales-row">
                <span class="totales-cell-left">Descuento Adicional:</span>
                <span class="totales-cell-right">- Bs. {{ number_format((float) $factura->descuento_adicional, 2) }}</span>
            </div>
        @endif

        <div class="totales-row total-pagar">
            <span class="totales-cell-left">TOTAL A PAGAR:</span>
            <span class="totales-cell-right">Bs. {{ number_format((float) $factura->total, 2) }}</span>
        </div>

        <div class="totales-row" style="font-size: 6.5pt; color: #444444;">
            <span class="totales-cell-left">Importe Base Crédito Fiscal:</span>
            <span class="totales-cell-right">Bs. {{ number_format((float) $factura->monto_total_subjeto_iva, 2) }}</span>
        </div>

        <div class="literal-box">
            SON: {{ $literal }}
        </div>
    </div>

    <!-- 6. Formas de Pago Registradas -->
    @if($factura->pagos->count() > 0)
        <div style="font-size: 6.5pt; margin-top: 3pt; padding-top: 2pt; border-top: 0.5px dotted #888888;">
            @foreach($factura->pagos as $pago)
                <div class="totales-row">
                    <span class="totales-cell-left font-bold">{{ $pago->tipo_pago }}:</span>
                    <span class="totales-cell-right">Bs. {{ number_format((float) $pago->monto, 2) }}</span>
                </div>
                @if((float) $pago->cambio > 0)
                    <div class="totales-row" style="color: #444444;">
                        <span class="totales-cell-left">Cambio / Vuelto:</span>
                        <span class="totales-cell-right">Bs. {{ number_format((float) $pago->cambio, 2) }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    <!-- 7. Código QR Oficial del SIN generado con simple-qrcode -->
    <div class="qr-box">
        <img src="{{ $qrBase64 }}" alt="QR SIAT" class="qr-img">
        <div style="font-size: 5.5pt; color: #555555; margin-top: 1pt;">Escanee para verificar validez en SIAT</div>
    </div>

    <!-- 8. Leyendas Normativas SIN -->
    <div class="leyendas-box border-top-dashed">
        <div class="font-bold">"ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS, EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE ACUERDO A LEY"</div>
        <div style="margin-top: 2pt;">{{ $leyendaSin }}</div>
        <div style="margin-top: 2pt; font-size: 5.2pt; color: #666666;">
            Representación Gráfica de Factura Digital emitido en modalidad Computarizada en Línea
        </div>
    </div>

    <!-- Disparador de impresión para visores PDF compatibles -->
    <script type="text/javascript">
        try {
            this.print(true);
        } catch(e) {}
    </script>
</body>
</html>
