<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Services\SiatFacturacionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FacturaImpresionController extends Controller
{
    public function __construct(public SiatFacturacionService $siatService) {}

    /**
     * Generar e imprimir el ticket térmico en formato PDF ajustando el alto dinámicamente y con simple-qrcode.
     */
    public function imprimirTicket(Factura $factura): Response
    {
        // Cargar relaciones necesarias
        $factura->load([
            'detalles.producto',
            'sucursal',
            'puntoVenta',
            'usuarioCreador',
            'pagos',
            'cliente',
        ]);

        $urlQr = $this->siatService->generarUrlQr($factura);
        $literal = $this->siatService->convertirMontoALetras((float) $factura->total);
        $leyendaSin = $this->siatService->obtenerLeyendaNormativa();

        // Generar código QR localmente con simple-qrcode en formato SVG convertido a Base64 para DomPDF
        $qrSvg = (string) QrCode::size(115)->margin(0)->generate($urlQr);
        $qrBase64 = 'data:image/svg+xml;base64,'.base64_encode($qrSvg);

        // CÁLCULO DINÁMICO DEL ALTO DEL TICKET (en puntos tipográficos pt y mm):
        // 1 mm = 2.83465 pt  |  1 pt = 0.352778 mm
        // Ancho estándar de rollo térmico de 80mm: (80 / 25.4) * 72 ≈ 226.77 pt.
        $anchoPt = 226.77;

        // Alto base para cabecera, datos del cliente, totales, QR, leyendas SIN y márgenes de corte
        $altoBase = 410;

        // Altura por cada ítem del detalle (nombre, lote, vencimiento, cantidad y subtotal)
        $cantItems = $factura->detalles->count();
        $altoPorItem = 34;
        $altoItems = $cantItems * $altoPorItem;

        // Altura adicional para textos largos o pagos múltiples
        $altoAdicional = 0;
        if ((float) $factura->descuento_adicional > 0) {
            $altoAdicional += 14;
        }
        if (mb_strlen($factura->razon_social) > 28) {
            $altoAdicional += 12;
        }
        if ($factura->pagos->count() > 1) {
            $altoAdicional += ($factura->pagos->count() * 12);
        }

        $altoTotalPt = $altoBase + $altoItems + $altoAdicional;
        $altoMm = round($altoTotalPt * 0.352778, 2);

        $pdf = Pdf::loadView('facturas.ticket-pdf', [
            'factura' => $factura,
            'urlQr' => $urlQr,
            'qrBase64' => $qrBase64,
            'literal' => $literal,
            'leyendaSin' => $leyendaSin,
            'altoMm' => $altoMm,
        ]);

        // Ajustar el tamaño del papel exactamente a 80mm de ancho y al alto calculado
        $pdf->setPaper([0, 0, $anchoPt, $altoTotalPt], 'portrait');
        $pdf->getDomPDF()->getOptions()->set('isHtml5ParserEnabled', true);
        $pdf->getDomPDF()->getOptions()->set('isRemoteEnabled', true);

        return $pdf->stream("Factura_{$factura->numero_factura}.pdf", [
            'Attachment' => false,
        ]);
    }
}
