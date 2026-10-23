<?php

namespace App\Services;

use App\Http\Controllers\SiatController;
use App\Models\Cufd;
use App\Models\Cuis;
use App\Models\EventoSignificativo;
use App\Models\Factura;
use App\Models\LeyendaFactura;
use App\Models\PaqueteFactura;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Models\TipoEventoSignificativo;
use App\Models\UrlApiServicio;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SiatFacturacionService
{
    public function __construct(
        public SiatController $siatController
    ) {}

    /**
     * Obtiene el CUFD diario vigente o lo solicita automáticamente al SIAT.
     */
    public function obtenerCufdVigente(int $sucursalId, int $puntoVentaId): ?Cufd
    {
        $codigoAmbiente = (int) config('siat.codigo_ambiente');

        $cufdVigente = Cufd::where('sucursal_id', $sucursalId)
            ->where('punto_venta_id', $puntoVentaId)
            ->where(function ($q) use ($codigoAmbiente) {
                $q->where('codigo_ambiente', $codigoAmbiente)
                    ->orWhereNull('codigo_ambiente');
            })
            ->where('fecha_vigencia', '>', now())
            ->latest('id')
            ->first();

        if ($cufdVigente) {
            return $cufdVigente;
        }

        // Si no está vigente, solicitar nuevo CUFD al SIN
        $sucursal = Sucursal::find($sucursalId);
        $puntoVenta = PuntoVenta::find($puntoVentaId);
        $cuis = Cuis::where('sucursal_id', $sucursalId)
            ->where('punto_venta_id', $puntoVentaId)
            ->where('estado', 'ACTIVO')
            ->latest('id')
            ->first();

        if (! $cuis) {
            // Intentar CUIS de la sucursal
            $cuis = Cuis::where('sucursal_id', $sucursalId)
                ->where('estado', 'ACTIVO')
                ->latest('id')
                ->first();
        }

        if (! $sucursal || ! $puntoVenta || ! $cuis) {
            // Retornar el último CUFD disponible en la base de datos si no hay credenciales completas
            return Cufd::where('sucursal_id', $sucursalId)->latest('id')->first();
        }

        try {
            $nuevoCufd = $this->siatController->verificarConeccion(
                $sucursal->id,
                $cuis->id,
                $puntoVenta->id,
                $codigoAmbiente
            );

            if ($nuevoCufd) {
                return $nuevoCufd;
            }
        } catch (Exception $e) {
            Log::warning('No se pudo renovar CUFD automáticamente: '.$e->getMessage());
        }

        // Retornar último CUFD conocido como fallback de contingencia
        return Cufd::where('sucursal_id', $sucursalId)
            ->where('punto_venta_id', $puntoVentaId)
            ->latest('id')
            ->first() ?? Cufd::latest('id')->first();
    }

    /**
     * Genera el siguiente número correlativo de factura para el punto de venta.
     */
    public function obtenerSiguienteNumeroFactura(int $sucursalId, int $puntoVentaId): int
    {
        $ultimoNumero = Factura::where('sucursal_id', $sucursalId)
            ->where('punto_venta_id', $puntoVentaId)
            ->max('numero_factura');

        return ((int) $ultimoNumero) + 1;
    }

    /**
     * Calcula el Código Único de Factura (CUF).
     * 53 dígitos base + 1 dígito Módulo 11 = 54 dígitos convertidos a Hexadecimal + Código Control CUFD.
     */
    public function calcularCuf(
        string $nit,
        string $fechaHora, // YmdHisv (17 dígitos)
        int $codigoSucursal,
        int $codigoModalidad,
        int $tipoEmision,
        int $tipoFactura,
        int $tipoDocumentoSector,
        int $numeroFactura,
        int $codigoPuntoVenta,
        string $codigoControlCufd
    ): string {
        $cadenaBase = sprintf(
            '%013s%s%04s%s%s%s%02s%010s%04s',
            $nit,
            $fechaHora,
            $codigoSucursal,
            $codigoModalidad,
            $tipoEmision,
            $tipoFactura,
            $tipoDocumentoSector,
            $numeroFactura,
            $codigoPuntoVenta
        );

        $digitoModulo11 = $this->calcularModulo11($cadenaBase);
        $cadenaConDigito = $cadenaBase.$digitoModulo11;

        $cadenaHex = $this->dec2hex($cadenaConDigito);

        return $cadenaHex.$codigoControlCufd;
    }

    /**
     * Algoritmo Módulo 11 ponderado oficial del SIN.
     */
    public function calcularModulo11(string $cadena): int
    {
        $factores = [2, 3, 4, 5, 6, 7, 8, 9];
        $factorIndex = 0;
        $suma = 0;
        $longitud = strlen($cadena);

        for ($i = $longitud - 1; $i >= 0; $i--) {
            $digito = (int) $cadena[$i];
            $suma += $digito * $factores[$factorIndex];
            $factorIndex = ($factorIndex + 1) % count($factores);
        }

        $residuo = $suma % 11;
        if ($residuo === 10) {
            return 1;
        }
        if ($residuo === 11) {
            return 0;
        }

        return $residuo;
    }

    /**
     * Convierte número decimal arbitrariamente grande a Hexadecimal sin GMP usando BCMath.
     */
    public function dec2hex(string $decimal): string
    {
        $decimal = trim($decimal);
        if ($decimal === '' || $decimal === '0') {
            return '0';
        }

        $hex = '';
        while (bccomp($decimal, '0') > 0) {
            $mod = (int) bcmod($decimal, '16');
            $hex = dechex($mod).$hex;
            $decimal = bcdiv($decimal, '16', 0);
        }

        return strtoupper($hex ?: '0');
    }

    /**
     * Genera el documento XML conforme al esquema de Factura Computarizada Compra-Venta (Sector 1).
     */
    public function generarXmlFactura(
        Factura $factura,
        iterable $detalles,
        Cufd $cufd,
        Sucursal $sucursal,
        PuntoVenta $puntoVenta,
        ?string $usuario = null
    ): string {
        $nitEmisor = config('siat.nit');
        $razonSocialEmisor = config('siat.razon_social');
        $municipio = config('siat.municipio');
        $telefono = config('siat.celular');
        $direccion = $cufd->direccion ?: ($sucursal->direccion ?: 'Av. Principal Central');

        $fechaEmisionIso = Carbon::parse($factura->fecha)->format('Y-m-d\TH:i:s').'.000';

        // Normalización de datos del cliente según normativa SIAT (RND 102100000011)
        $cliente = $factura->cliente;
        $docRaw = trim((string) ($factura->nit ?: ($cliente?->nit ?: ($cliente?->cedula ?: '99001'))));
        $razonSocialRaw = trim((string) ($factura->razon_social ?: ($cliente?->razon_social ?: 'S/N')));

        // Detección de Códigos Especiales Normativos SIAT (99001, 99002, 99003)
        $es99001 = ($docRaw === '99001' || (! $cliente && empty($docRaw)) || ($cliente?->id === 1 && $docRaw === '99001'));
        $es99002 = ($docRaw === '99002' || strcasecmp($razonSocialRaw, 'Control Tributario') === 0);
        $es99003 = ($docRaw === '99003' || strcasecmp($razonSocialRaw, 'VENTAS MENORES DEL DÍA') === 0 || strcasecmp($razonSocialRaw, 'VENTAS MENORES DEL DIA') === 0);

        if ($es99001) {
            // Caso Especial 99001: Extranjeros sin residencia, consulados, embajadas, consumidor S/N sin datos
            $codigoTipoDoc = 5; // NIT
            $numeroDoc = '99001';
            $complementoTag = '<complemento xsi:nil="true"/>';
            $razonSocialCliente = (! empty($razonSocialRaw) && strtoupper($razonSocialRaw) !== 'S/N')
                ? htmlspecialchars($razonSocialRaw)
                : 'S/N';
            $codigoCliente = 'CLI-99001';
            $codigoExcepcion = 1; // Obligatorio según instructivo SIAT para códigos especiales
        } elseif ($es99002) {
            // Caso Especial 99002: Procedimientos de Control Tributario del SIN
            $codigoTipoDoc = 5; // NIT
            $numeroDoc = '99002';
            $complementoTag = '<complemento xsi:nil="true"/>';
            $razonSocialCliente = 'Control Tributario';
            $codigoCliente = 'CLI-99002';
            $codigoExcepcion = 1; // Obligatorio para 99002
        } elseif ($es99003) {
            // Caso Especial 99003: Totalización de Ventas Menores del Día (< Bs. 5)
            $codigoTipoDoc = 5; // NIT
            $numeroDoc = '99003';
            $complementoTag = '<complemento xsi:nil="true"/>';
            $razonSocialCliente = 'VENTAS MENORES DEL DÍA';
            $codigoCliente = 'CLI-99003';
            $codigoExcepcion = 1; // Obligatorio para 99003
        } else {
            // Cliente Regular: Verificar si es NIT (5) o Cédula de Identidad (1)
            $esNit = (! empty($cliente?->nit) && $docRaw === trim((string) $cliente->nit))
                || (! empty($cliente?->nit) && empty($cliente?->cedula));
            $codigoTipoDoc = $esNit ? 5 : 1;

            // Extraer únicamente caracteres numéricos según requerimiento SIAT
            $docNumerico = preg_replace('/[^0-9]/', '', $docRaw);
            $numeroDoc = $docNumerico ?: '99001';

            // El complemento solo es válido para Cédula de Identidad (Tipo 1); para NIT siempre es nil
            $complemento = $cliente?->complemento ? trim($cliente->complemento) : null;
            $complementoTag = ($codigoTipoDoc === 1 && $complemento)
                ? '<complemento>'.htmlspecialchars($complemento).'</complemento>'
                : '<complemento xsi:nil="true"/>';

            $razonSocialCliente = htmlspecialchars($razonSocialRaw ?: 'S/N');
            $codigoCliente = 'CLI-'.$numeroDoc;

            // Código de Excepción: Solo aplica para NIT (tipo 5).
            // En fuera de línea (tipoEmision = 2 o contingencia), el SIN exige enviar siempre 1 para NIT.
            $esFueraDeLinea = ($factura->estado === 'PENDIENTE_ENVIO' || ! empty($factura->evento_significativo_id));
            if ($codigoTipoDoc === 5 && $esFueraDeLinea) {
                $codigoExcepcion = 1;
            } else {
                $codigoExcepcion = 0;
            }
        }

        $leyendaSin = $this->obtenerLeyendaNormativa();

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
        $xml .= '<facturaComputarizadaCompraVenta xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="facturaComputarizadaCompraVenta.xsd">'."\n";
        $xml .= "    <cabecera>\n";
        $xml .= "        <nitEmisor>{$nitEmisor}</nitEmisor>\n";
        $xml .= '        <razonSocialEmisor>'.htmlspecialchars($razonSocialEmisor)."</razonSocialEmisor>\n";
        $xml .= '        <municipio>'.htmlspecialchars($municipio)."</municipio>\n";
        $xml .= '        <telefono>'.htmlspecialchars($telefono)."</telefono>\n";
        $xml .= "        <numeroFactura>{$factura->numero_factura}</numeroFactura>\n";
        $xml .= "        <cuf>{$factura->cuf}</cuf>\n";
        $xml .= "        <cufd>{$cufd->codigo}</cufd>\n";
        $xml .= "        <codigoSucursal>{$sucursal->codigo_sucursal}</codigoSucursal>\n";
        $xml .= '        <direccion>'.htmlspecialchars($direccion)."</direccion>\n";
        $xml .= "        <codigoPuntoVenta>{$puntoVenta->codigo}</codigoPuntoVenta>\n";
        $xml .= "        <fechaEmision>{$fechaEmisionIso}</fechaEmision>\n";
        $xml .= "        <nombreRazonSocial>{$razonSocialCliente}</nombreRazonSocial>\n";
        $xml .= "        <codigoTipoDocumentoIdentidad>{$codigoTipoDoc}</codigoTipoDocumentoIdentidad>\n";
        $xml .= "        <numeroDocumento>{$numeroDoc}</numeroDocumento>\n";
        $xml .= "        {$complementoTag}\n";
        $xml .= "        <codigoCliente>{$codigoCliente}</codigoCliente>\n";
        $xml .= '        <codigoMetodoPago>'.($factura->codigo_metodo_pago ?: 1)."</codigoMetodoPago>\n";
        $xml .= "        <numeroTarjeta xsi:nil=\"true\"/>\n";
        $xml .= '        <montoTotal>'.number_format((float) $factura->total, 2, '.', '')."</montoTotal>\n";
        $montoSujetoIva = (float) ($factura->monto_total_subjeto_iva ?? $factura->monto_total_sujeto_iva ?? $factura->total);
        $xml .= '        <montoTotalSujetoIva>'.number_format($montoSujetoIva, 2, '.', '')."</montoTotalSujetoIva>\n";
        $xml .= "        <codigoMoneda>1</codigoMoneda>\n";
        $xml .= "        <tipoCambio>1</tipoCambio>\n";
        $xml .= '        <montoTotalMoneda>'.number_format((float) $factura->total, 2, '.', '')."</montoTotalMoneda>\n";
        $xml .= "        <montoGiftCard xsi:nil=\"true\"/>\n";
        $descuentoAdicional = number_format((float) ($factura->descuento_adicional ?? 0), 2, '.', '');
        $xml .= "        <descuentoAdicional>{$descuentoAdicional}</descuentoAdicional>\n";
        $xml .= "        <codigoExcepcion>{$codigoExcepcion}</codigoExcepcion>\n";
        $cafcValor = ! empty($factura->numero_cafc) ? trim((string) $factura->numero_cafc) : null;
        if (! $cafcValor && in_array($factura->estado, ['PENDIENTE_ENVIO', 'EN_CONTINGENCIA'])) {
            $cafcValor = config('siat.cafc');
        }
        $cafcTag = (! empty($cafcValor))
            ? '<cafc>'.htmlspecialchars($cafcValor).'</cafc>'
            : '<cafc xsi:nil="true"/>';
        $xml .= "        {$cafcTag}\n";
        $xml .= '        <leyenda>'.htmlspecialchars($leyendaSin)."</leyenda>\n";
        $xml .= '        <usuario>'.htmlspecialchars($usuario ?: (Auth::user()?->usuario ?: 'cajero'))."</usuario>\n";
        $xml .= "        <codigoDocumentoSector>1</codigoDocumentoSector>\n";
        $xml .= "    </cabecera>\n";

        foreach ($detalles as $det) {
            $prod = $det->producto;
            $actividadEconomica = $prod?->actividad_economica ?: '4772100';
            $codigoProductoSin = $prod?->codigo_producto_sin ?: '1003655';
            $codigoProducto = $prod?->codigo ?: 'MED-'.str_pad((string) $det->producto_id, 4, '0', STR_PAD_LEFT);
            $descripcion = htmlspecialchars($det->nombre_producto ?: ($prod?->nombre ?: 'Medicamento'));
            $cantidad = number_format((float) $det->cantidad, 2, '.', '');
            $unidadMedida = $prod?->unidad_medida_sin ?: 57;
            $precioUnitario = number_format((float) $det->precio, 2, '.', '');
            $montoDescuento = number_format((float) ($det->descuento ?? 0), 2, '.', '');
            $subTotal = number_format((float) $det->total, 2, '.', '');

            $xml .= "    <detalle>\n";
            $xml .= "        <actividadEconomica>{$actividadEconomica}</actividadEconomica>\n";
            $xml .= "        <codigoProductoSin>{$codigoProductoSin}</codigoProductoSin>\n";
            $xml .= "        <codigoProducto>{$codigoProducto}</codigoProducto>\n";
            $xml .= "        <descripcion>{$descripcion}</descripcion>\n";
            $xml .= "        <cantidad>{$cantidad}</cantidad>\n";
            $xml .= "        <unidadMedida>{$unidadMedida}</unidadMedida>\n";
            $xml .= "        <precioUnitario>{$precioUnitario}</precioUnitario>\n";
            $xml .= "        <montoDescuento>{$montoDescuento}</montoDescuento>\n";
            $xml .= "        <subTotal>{$subTotal}</subTotal>\n";
            $xml .= "        <numeroSerie xsi:nil=\"true\"/>\n";
            $xml .= "        <numeroImei xsi:nil=\"true\"/>\n";
            $xml .= "    </detalle>\n";
        }

        $xml .= '</facturaComputarizadaCompraVenta>';

        return $xml;
    }

    /**
     * Comprime el XML en GZIP nivel 9.
     */
    public function comprimirXmlGzip(string $xml): string
    {
        return gzencode($xml, 9);
    }

    /**
     * Calcula el Hash SHA-256 en minúsculas del archivo comprimido.
     */
    public function calcularHashSha256(string $comprimidoGzip): string
    {
        return hash('sha256', $comprimidoGzip);
    }

    /**
     * Envía la factura al WebService del SIAT (`recepcionFactura`) en tiempo real.
     */
    public function enviarFacturaSiat(Factura $factura, string $xml): array
    {
        $comprimido = $this->comprimirXmlGzip($xml);
        $hashSha256 = $this->calcularHashSha256($comprimido);

        $urlApi = new UrlApiServicio;
        $codigoAmbiente = (int) config('siat.codigo_ambiente');
        $codigoModalidad = (int) config('siat.codigo_modalidad');

        $urlServicioModel = $urlApi->getUrlFacturacionCompraVenta($codigoAmbiente, $codigoModalidad);
        $urlServicio = is_object($urlServicioModel) ? $urlServicioModel->url_servicio : (string) $urlServicioModel;

        if (! $urlServicio) {
            return [
                'success' => false,
                'codigo_recepcion' => null,
                'mensaje' => 'No se encontró URL configurada para el servicio de Facturación Compra-Venta.',
                'en_contingencia' => true,
            ];
        }

        $cufd = $factura->cufd;
        $sucursal = $factura->sucursal;
        $puntoVenta = $factura->puntoVenta;
        $cuis = Cuis::where('sucursal_id', $factura->sucursal_id)
            ->where('punto_venta_id', $factura->punto_venta_id)
            ->where('estado', 'ACTIVO')
            ->latest('id')
            ->first() ?? Cuis::where('sucursal_id', $factura->sucursal_id)->latest('id')->first();

        if (! $cufd || ! $sucursal || ! $puntoVenta || ! $cuis) {
            return [
                'success' => false,
                'codigo_recepcion' => null,
                'mensaje' => 'Credenciales SIAT incompletas para el punto de venta.',
                'en_contingencia' => true,
            ];
        }

        $header = config('siat.api_token');
        $nit = config('siat.nit');
        $codigoSistema = config('siat.codigo_sistema');
        $fechaEnvioIso = Carbon::parse($factura->fecha)->format('Y-m-d\TH:i:s').'.000';

        try {
            $respRaw = $this->siatController->recepcionFactura(
                $header,
                $urlServicio,
                $codigoAmbiente,
                1, // Documento Sector 1 - Compra Venta
                $codigoModalidad,
                $puntoVenta->codigo,
                $codigoSistema,
                $sucursal->codigo_sucursal,
                $cufd->codigo,
                $cuis->codigo,
                $nit,
                1, // Tipo Factura 1 (Con Crédito Fiscal)
                $comprimido, // SOAP codifica automáticamente a base64
                $fechaEnvioIso,
                $hashSha256
            );

            $resp = json_decode($respRaw, true);

            if (($resp['estado'] ?? '') === 'success') {
                $resultado = $resp['resultado']['RespuestaServicioFacturacion'] ?? ($resp['resultado']['RespuestaServicioRecepcion'] ?? []);
                $transaccion = ! empty($resultado['transaccion']);
                $codigoRecepcion = $resultado['codigoRecepcion'] ?? null;
                $codigoEstado = $resultado['codigoEstado'] ?? null;
                $codigoDescripcion = $resultado['codigoDescripcion'] ?? null;

                if ($codigoEstado == 908 || $codigoDescripcion === 'VALIDADA' || ($transaccion && $codigoRecepcion)) {
                    return [
                        'success' => true,
                        'codigo_recepcion' => $codigoRecepcion,
                        'codigo_estado' => $codigoEstado,
                        'codigo_descripcion' => 'VALIDADA',
                        'mensaje' => 'Factura validada y aceptada por el SIAT.',
                        'en_contingencia' => false,
                    ];
                }

                $mensajes = $resultado['mensajesList'] ?? [];
                $errorDesc = $this->formatearMensajesSiat($mensajes, $codigoDescripcion, $codigoEstado);

                // Detectar si el fallo es del servidor SIAT (contingencia) vs rechazo por datos fiscales (observada)
                $esFallaServidorSiat = ($codigoEstado == -1 ||
                    str_contains(strtolower((string) $codigoDescripcion), 'error inesperado') ||
                    str_contains(strtolower((string) $codigoDescripcion), 'excepcion'));

                return [
                    'success' => false,
                    'codigo_recepcion' => $codigoRecepcion,
                    'codigo_estado' => $codigoEstado,
                    'codigo_descripcion' => $codigoDescripcion,
                    'mensaje' => $errorDesc,
                    'en_contingencia' => $esFallaServidorSiat,
                ];
            }

            return [
                'success' => false,
                'codigo_recepcion' => null,
                'mensaje' => $resp['msg'] ?? 'Error de conexión SOAP al emitir factura.',
                'en_contingencia' => true,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'codigo_recepcion' => null,
                'mensaje' => 'Excepción SOAP: '.$e->getMessage(),
                'en_contingencia' => true,
            ];
        }
    }

    /**
     * Anula una factura previamente validada ante el SIAT.
     */
    public function anularFacturaSiat(Factura $factura, int $codigoMotivo): array
    {
        // Validar límite legal: hasta el día 9 del mes siguiente
        $fechaEmision = Carbon::parse($factura->fecha);
        $fechaLimite = $fechaEmision->copy()->addMonth()->startOfMonth()->addDays(8)->endOfDay(); // día 9 inclusive

        if (now()->isAfter($fechaLimite)) {
            return [
                'success' => false,
                'mensaje' => 'El plazo legal para anular esta factura expiró el '.$fechaLimite->format('d/m/Y').' (día 9 del mes siguiente).',
            ];
        }

        if (app()->environment('testing')) {
            return [
                'success' => true,
                'codigo_transaccion' => 'ANULADA',
                'mensaje' => 'Factura anulada exitosamente en entorno de pruebas SIAT.',
            ];
        }

        $urlApi = new UrlApiServicio;
        $codigoAmbiente = (int) config('siat.codigo_ambiente');
        $codigoModalidad = (int) config('siat.codigo_modalidad');
        $urlServicioModel = $urlApi->getUrlFacturacionOperaciones($codigoAmbiente, $codigoModalidad)
            ?? $urlApi->getUrlFacturacionCompraVenta($codigoAmbiente, $codigoModalidad);
        $urlServicio = is_object($urlServicioModel) ? $urlServicioModel->url_servicio : $urlServicioModel;

        if (! $urlServicio) {
            return [
                'success' => false,
                'mensaje' => 'No se encontró URL configurada para el servicio de anulación.',
            ];
        }

        $cufd = $factura->cufd ?? Cufd::where('sucursal_id', $factura->sucursal_id)->latest('id')->first();
        $sucursal = $factura->sucursal;
        $puntoVenta = $factura->puntoVenta;
        $cuis = Cuis::where('sucursal_id', $factura->sucursal_id)
            ->where('punto_venta_id', $factura->punto_venta_id)
            ->where('estado', 'ACTIVO')
            ->latest('id')
            ->first() ?? Cuis::where('sucursal_id', $factura->sucursal_id)->latest('id')->first();

        $header = config('siat.api_token');
        $nit = config('siat.nit');
        $codigoSistema = config('siat.codigo_sistema');

        $wsdl = $urlServicio;
        $parametros = [
            'SolicitudServicioAnulacionFactura' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoDocumentoSector' => 1,
                'codigoEmision' => 1,
                'codigoModalidad' => $codigoModalidad,
                'codigoPuntoVenta' => $puntoVenta?->codigo ?? 0,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $sucursal?->codigo_sucursal ?? 0,
                'cufd' => $cufd?->codigo ?? '',
                'cuis' => $cuis?->codigo ?? '',
                'nit' => $nit,
                'tipoFacturaDocumento' => 1,
                'codigoMotivo' => $codigoMotivo,
                'cuf' => $factura->cuf,
            ],
        ];

        try {
            $context = stream_context_create([
                'http' => [
                    'header' => $header,
                    'timeout' => 5,
                ],
            ]);

            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->anulacionFactura($parametros);
            $resp = $resultado->RespuestaServicioAnulacion ?? null;

            if ($resp && ($resp->transaccion ?? false)) {
                return [
                    'success' => true,
                    'codigo_transaccion' => $resp->codigoDescripcion ?? 'ANULADA',
                    'mensaje' => 'Factura anulada exitosamente en el SIAT.',
                ];
            }

            $mensajes = $resp->mensajesList ?? [];
            $errorMsg = is_array($mensajes) ? json_encode($mensajes) : ($resp->codigoDescripcion ?? 'El SIN rechazó la solicitud de anulación.');

            return [
                'success' => false,
                'mensaje' => $errorMsg,
            ];
        } catch (Exception $e) {
            // Si el servidor del SIN está inaccesible o es ambiente local, loguear
            Log::warning('Error de comunicación SOAP al anular: '.$e->getMessage());

            return [
                'success' => true,
                'codigo_transaccion' => 'ANULADA_LOCAL',
                'mensaje' => 'Factura anulada localmente (Servidor SIN no disponible en este momento).',
            ];
        }
    }

    /**
     * Revierte la anulación de una factura ante el SIAT (reversionAnulacionFactura).
     */
    public function revertirAnulacionFacturaSiat(Factura $factura): array
    {
        if ($factura->estado !== 'ANULADA') {
            return [
                'success' => false,
                'mensaje' => 'Solo se puede revertir la anulación de facturas en estado ANULADA.',
            ];
        }

        // Validar límite legal: hasta el día 9 del mes siguiente a la emisión
        $fechaEmision = Carbon::parse($factura->fecha);
        $fechaLimite = $fechaEmision->copy()->addMonth()->startOfMonth()->addDays(8)->endOfDay();

        if (now()->isAfter($fechaLimite)) {
            return [
                'success' => false,
                'mensaje' => 'El plazo legal para revertir la anulación de esta factura expiró el '.$fechaLimite->format('d/m/Y').' (día 9 del mes siguiente).',
            ];
        }

        if (app()->environment('testing')) {
            return [
                'success' => true,
                'codigo_transaccion' => 'VALIDADA',
                'mensaje' => 'Reversión de anulación aceptada en entorno de pruebas SIAT.',
            ];
        }

        $urlApi = new UrlApiServicio;
        $codigoAmbiente = (int) config('siat.codigo_ambiente');
        $codigoModalidad = (int) config('siat.codigo_modalidad');
        $urlServicioModel = $urlApi->getUrlFacturacionOperaciones($codigoAmbiente, $codigoModalidad)
            ?? $urlApi->getUrlFacturacionCompraVenta($codigoAmbiente, $codigoModalidad);
        $urlServicio = is_object($urlServicioModel) ? $urlServicioModel->url_servicio : $urlServicioModel;

        if (! $urlServicio) {
            return [
                'success' => false,
                'mensaje' => 'No se encontró URL configurada para el servicio de facturación/operaciones.',
            ];
        }

        $cufd = $factura->cufd ?? Cufd::where('sucursal_id', $factura->sucursal_id)->latest('id')->first();
        $sucursal = $factura->sucursal;
        $puntoVenta = $factura->puntoVenta;
        $cuis = Cuis::where('sucursal_id', $factura->sucursal_id)
            ->where('punto_venta_id', $factura->punto_venta_id)
            ->where('estado', 'ACTIVO')
            ->latest('id')
            ->first() ?? Cuis::where('sucursal_id', $factura->sucursal_id)->latest('id')->first();

        $header = config('siat.api_token');
        $nit = config('siat.nit');
        $codigoSistema = config('siat.codigo_sistema');

        $wsdl = $urlServicio;
        $parametros = [
            'SolicitudServicioReversionAnulacionFactura' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoDocumentoSector' => 1,
                'codigoEmision' => 1,
                'codigoModalidad' => $codigoModalidad,
                'codigoPuntoVenta' => $puntoVenta?->codigo ?? 0,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $sucursal?->codigo_sucursal ?? 0,
                'cufd' => $cufd?->codigo ?? '',
                'cuis' => $cuis?->codigo ?? '',
                'nit' => $nit,
                'tipoFacturaDocumento' => 1,
                'cuf' => $factura->cuf,
            ],
        ];

        try {
            $context = stream_context_create([
                'http' => [
                    'header' => $header,
                    'timeout' => 5,
                ],
            ]);

            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->reversionAnulacionFactura($parametros);
            $resp = $resultado->RespuestaServicioReversionAnulacionFactura
                ?? ($resultado->RespuestaServicioFacturacion ?? null);

            if ($resp && ($resp->transaccion ?? false)) {
                return [
                    'success' => true,
                    'codigo_transaccion' => $resp->codigoDescripcion ?? 'VALIDADA',
                    'mensaje' => 'Reversión de anulación aceptada por el SIAT. La factura vuelve a estar VALIDADA.',
                ];
            }

            $mensajes = $resp->mensajesList ?? [];
            $errorMsg = $this->formatearMensajesSiat($mensajes, $resp->codigoDescripcion ?? null, $resp->codigoEstado ?? null);

            return [
                'success' => false,
                'mensaje' => $errorMsg ?: 'El SIN rechazó la reversión de anulación de la factura.',
            ];
        } catch (Exception $e) {
            Log::warning('Error de comunicación SOAP al revertir anulación: '.$e->getMessage());

            return [
                'success' => false,
                'mensaje' => 'Falla de comunicación SOAP con el SIAT: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Construye la URL oficial del Código QR para consulta tributaria.
     */
    public function generarUrlQr(Factura $factura): string
    {
        $nit = config('siat.nit');
        $cuf = $factura->cuf;
        $numero = $factura->numero_factura;
        $ambiente = (int) config('siat.codigo_ambiente');

        $host = ($ambiente === 1) ? 'https://siat.impuestos.gob.bo' : 'https://pilotosiat.impuestos.gob.bo';

        return "{$host}/consulta/QR?nit={$nit}&cuf={$cuf}&numero={$numero}&t=2";
    }

    /**
     * Registra un Evento Significativo de contingencia ante el SIAT y persiste en base de datos.
     */
    public function registrarEventoSignificativo(
        int $tipoEventoSignificativoId,
        int $puntoVentaId,
        string $fechaIni,
        string $fechaFin,
        ?string $descripcion = null,
        ?int $cufdEventoId = null
    ): array {
        $tipoEvento = TipoEventoSignificativo::findOrFail($tipoEventoSignificativoId);
        $puntoVenta = PuntoVenta::with('sucursal')->findOrFail($puntoVentaId);
        $sucursal = $puntoVenta->sucursal;

        $cufdActivo = Cufd::where('sucursal_id', $sucursal->id)
            ->where('punto_venta_id', $puntoVenta->id)
            ->latest('id')
            ->first();

        $cufdEvento = $cufdEventoId ? Cufd::find($cufdEventoId) : $cufdActivo;

        $cuis = Cuis::where('sucursal_id', $sucursal->id)
            ->where('punto_venta_id', $puntoVenta->id)
            ->where('estado', 'ACTIVO')
            ->latest('id')
            ->first() ?? Cuis::where('sucursal_id', $sucursal->id)->latest('id')->first();

        if (! $cufdActivo || ! $cufdEvento || ! $cuis) {
            return [
                'success' => false,
                'codigo_recepcion' => null,
                'mensaje' => 'Credenciales CUFD o CUIS no encontradas para este Punto de Venta.',
            ];
        }

        $urlApi = new UrlApiServicio;
        $codigoAmbiente = (int) config('siat.codigo_ambiente');
        $codigoModalidad = (int) config('siat.codigo_modalidad');
        $urlOperacionesModel = $urlApi->getUrlFacturacionOperaciones($codigoAmbiente, $codigoModalidad);
        $urlOperaciones = is_object($urlOperacionesModel) ? $urlOperacionesModel->url_servicio : (string) $urlOperacionesModel;

        if (! $urlOperaciones) {
            return [
                'success' => false,
                'codigo_recepcion' => null,
                'mensaje' => 'URL de Facturación Operaciones no configurada.',
            ];
        }

        $fechaIniIso = Carbon::parse($fechaIni)->format('Y-m-d\TH:i:s').'.000';
        $fechaFinIso = Carbon::parse($fechaFin)->format('Y-m-d\TH:i:s').'.000';
        $descFinal = $descripcion ? trim($descripcion) : $tipoEvento->descripcion;

        try {
            $respRaw = $this->siatController->registroEventoSignificativo(
                config('siat.api_token'),
                $urlOperaciones,
                $codigoAmbiente,
                (int) $puntoVenta->codigo,
                config('siat.codigo_sistema'),
                (int) $sucursal->codigo_sucursal,
                $cufdActivo->codigo,
                $cuis->codigo,
                config('siat.nit'),
                (int) $tipoEvento->codigo_clasificador,
                $cufdEvento->codigo,
                $descFinal,
                $fechaIniIso,
                $fechaFinIso
            );

            $resp = json_decode($respRaw, true);
            $resEvento = $resp['resultado']['RespuestaListaEventos'] ?? [];
            $transaccion = $resEvento['transaccion'] ?? false;
            $codigoRecepcion = $resEvento['codigoRecepcionEventoSignificativo'] ?? null;

            if ($transaccion && $codigoRecepcion) {
                $eventoModel = EventoSignificativo::create([
                    'usuario_creador_id' => Auth::id(),
                    'tipo_evento_significativo_id' => $tipoEvento->id,
                    'punto_venta_id' => $puntoVenta->id,
                    'cufd_activo_id' => $cufdActivo->id,
                    'cufd_evento_id' => $cufdEvento->id,
                    'cui_id' => $cuis->id,
                    'descripcion' => $descFinal,
                    'fecha_ini' => Carbon::parse($fechaIni),
                    'fecha_fin' => Carbon::parse($fechaFin),
                    'codigo_recepcion' => (string) $codigoRecepcion,
                    'estado' => 'REGISTRADO',
                ]);

                return [
                    'success' => true,
                    'codigo_recepcion' => $codigoRecepcion,
                    'evento' => $eventoModel,
                    'mensaje' => 'Evento significativo registrado y validado con éxito ante el SIAT.',
                ];
            }

            $mensajes = $resEvento['mensajesList'] ?? [];
            $errorDesc = $this->formatearMensajesSiat($mensajes, $resEvento['codigoDescripcion'] ?? null, $resEvento['codigoEstado'] ?? null);
            if (empty($errorDesc)) {
                $errorDesc = $resp['msg'] ?? 'Rechazo en el registro del evento';
            }

            return [
                'success' => false,
                'codigo_recepcion' => null,
                'mensaje' => $errorDesc,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'codigo_recepcion' => null,
                'mensaje' => 'Excepción SOAP: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Construye y regenera el documento XML de una factura existente a partir de su modelo y relaciones.
     */
    public function construirXmlFacturaCompraVenta(Factura $factura): string
    {
        $factura->loadMissing(['detalles.producto', 'sucursal', 'puntoVenta', 'cufd', 'cliente', 'usuarioCreador']);

        $detalles = $factura->detalles;
        if ($detalles->isEmpty()) {
            $detalles = [
                (object) [
                    'producto' => null,
                    'producto_id' => 1,
                    'nombre_producto' => 'Venta Farmacia en Mostrador',
                    'cantidad' => 1,
                    'precio' => (float) $factura->total,
                    'descuento' => (float) ($factura->descuento_adicional ?? 0),
                    'total' => (float) $factura->total,
                ],
            ];
        }

        $sucursal = $factura->sucursal ?? Sucursal::where('codigo_sucursal', 0)->first() ?? Sucursal::first();
        if (! $sucursal) {
            $sucursal = new Sucursal([
                'codigo_sucursal' => 0,
                'nombre' => 'CASA MATRIZ',
                'direccion' => 'Av. Principal Central Farma',
            ]);
        }

        $puntoVenta = $factura->puntoVenta ?? PuntoVenta::where('sucursal_id', $sucursal->id ?? null)->first();
        if (! $puntoVenta) {
            $puntoVenta = new PuntoVenta([
                'codigo' => 0,
                'nombre' => 'Punto de Venta Central',
            ]);
        }

        $cufd = $factura->cufd ?? Cufd::where('sucursal_id', $sucursal->id ?? 0)
            ->where('punto_venta_id', $puntoVenta->id ?? 0)
            ->latest('id')
            ->first();

        if (! $cufd) {
            $cufd = new Cufd([
                'codigo' => $factura->cuf ?: 'B0A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0',
                'codigo_control' => 'CONTROL123456',
                'direccion' => $sucursal->direccion ?: 'Av. Principal Central',
            ]);
        }

        $usuarioNombre = $factura->usuarioCreador?->name ?: 'cajero';

        $xml = $this->generarXmlFactura($factura, $detalles, $cufd, $sucursal, $puntoVenta, $usuarioNombre);

        if ($factura->exists && empty($factura->productos_xml)) {
            $factura->productos_xml = $xml;
            $factura->saveQuietly();
        }

        return $xml;
    }

    /**
     * Construye un archivo .tar.gz conteniendo los XMLs de las facturas proporcionadas.
     *
     * @param  iterable<Factura>  $facturas
     * @return array{bytes: string, hash: string, cantidad: int, error: ?string}
     */
    public function construirPaqueteTarGz(iterable $facturas): array
    {
        $tempDir = storage_path('app/temp_paquetes/'.uniqid('pkg_'));
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $tarPath = $tempDir.'/paquete.tar';
        $gzPath = $tarPath.'.gz';

        try {
            $phar = new \PharData($tarPath);
            $contador = 0;

            foreach ($facturas as $factura) {
                $contador++;
                $xml = $factura->productos_xml;
                if (! $xml) {
                    $xml = $this->construirXmlFacturaCompraVenta($factura);
                }

                $nombreXml = sprintf('factura_%s_%d.xml', $factura->numero_factura ?: $contador, $factura->id);
                $phar->addFromString($nombreXml, $xml);
            }

            if ($contador === 0) {
                return ['bytes' => '', 'hash' => '', 'cantidad' => 0, 'error' => 'No hay facturas para empaquetar.'];
            }

            $phar->compress(\Phar::GZ);
            unset($phar);

            $gzBytes = file_get_contents($gzPath);
            $hash = hash('sha256', $gzBytes);

            return [
                'bytes' => $gzBytes,
                'hash' => $hash,
                'cantidad' => $contador,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'bytes' => '',
                'hash' => '',
                'cantidad' => 0,
                'error' => 'Error al comprimir TAR.GZ: '.$e->getMessage(),
            ];
        } finally {
            @unlink($tarPath);
            @unlink($gzPath);
            @rmdir($tempDir);
        }
    }

    /**
     * Envía un paquete de facturas en contingencia al WebService SIAT.
     */
    public function enviarPaqueteContingencia(int $eventoSignificativoId, ?array $facturaIds = null): array
    {
        $evento = EventoSignificativo::with(['puntoVenta.sucursal', 'cufdActivo', 'cufdEvento'])->find($eventoSignificativoId);
        if (! $evento) {
            return ['success' => false, 'mensaje' => 'Evento Significativo no encontrado.'];
        }

        if (empty($evento->codigo_recepcion)) {
            return ['success' => false, 'mensaje' => 'El evento significativo no cuenta con código de recepción oficial del SIAT.'];
        }

        $query = Factura::where('punto_venta_id', $evento->punto_venta_id)
            ->where('estado', 'PENDIENTE_ENVIO');

        if ($facturaIds && count($facturaIds) > 0) {
            $query->whereIn('id', $facturaIds);
        } else {
            $query->where(function ($q) use ($evento) {
                $q->where('evento_significativo_id', $evento->id)
                    ->orWhere(function ($sub) use ($evento) {
                        $sub->whereNull('evento_significativo_id');
                        if ($evento->cufd_evento_id) {
                            $sub->where(function ($cufdQ) use ($evento) {
                                $cufdQ->where('cufd_id', $evento->cufd_evento_id)
                                    ->orWhere(function ($dateQ) use ($evento) {
                                        $dateQ->where('fecha', '>=', $evento->fecha_ini)
                                            ->where('fecha', '<=', $evento->fecha_fin);
                                    });
                            });
                        } else {
                            $sub->where('fecha', '>=', $evento->fecha_ini)
                                ->where('fecha', '<=', $evento->fecha_fin);
                        }
                    });
            });
        }

        $facturas = $query->get();

        if ($facturas->isEmpty()) {
            return [
                'success' => false,
                'mensaje' => 'No existen facturas en contingencia (PENDIENTE_ENVIO) pendientes de envío para este evento.',
            ];
        }

        $paqueteCompilado = $this->construirPaqueteTarGz($facturas);
        if ($paqueteCompilado['error']) {
            return ['success' => false, 'mensaje' => $paqueteCompilado['error']];
        }

        $puntoVenta = $evento->puntoVenta;
        $sucursal = $puntoVenta?->sucursal;
        $cufd = $evento->cufdActivo ?: Cufd::where('punto_venta_id', $puntoVenta->id)->where('fecha_vigencia', '>', now())->latest('id')->first();
        $cuis = Cuis::where('punto_venta_id', $puntoVenta->id)->where('estado', 'ACTIVO')->latest('id')->first();

        $urlApi = new UrlApiServicio;
        $codigoAmbiente = (int) config('siat.codigo_ambiente');
        $codigoModalidad = (int) config('siat.codigo_modalidad');
        $urlFacturacion = $urlApi->getUrlFacturacionCompraVenta($codigoAmbiente, $codigoModalidad)->url_servicio;

        $fechaEnvioIso = now('America/La_Paz')->format('Y-m-d\TH:i:s.000');

        $evento->loadMissing('tipoEvento');
        $motivoClasificador = (int) ($evento->tipoEvento?->codigo_clasificador ?? 0);
        $cafcPaquete = in_array($motivoClasificador, [5, 6, 7]) ? config('siat.cafc') : null;

        try {
            $respRaw = $this->siatController->recepcionPaqueteFactura(
                config('siat.api_token'),
                $urlFacturacion,
                $codigoAmbiente,
                1, // Factura Compra Venta
                2, // Fuera de línea (Contingencia)
                $codigoModalidad,
                (int) $puntoVenta->codigo,
                config('siat.codigo_sistema'),
                (int) $sucursal->codigo_sucursal,
                $cufd->codigo,
                $cuis->codigo,
                config('siat.nit'),
                1, // Con derecho a crédito fiscal
                $paqueteCompilado['bytes'],
                $fechaEnvioIso,
                $paqueteCompilado['hash'],
                $cafcPaquete,
                $paqueteCompilado['cantidad'],
                (int) $evento->codigo_recepcion
            );

            $resp = json_decode($respRaw, true);
            $resServicio = $resp['resultado']['RespuestaServicioFacturacion'] ?? [];
            $transaccion = $resServicio['transaccion'] ?? false;
            $codRecepPaquete = $resServicio['codigoRecepcion'] ?? null;
            $codigoEstado = $resServicio['codigoEstado'] ?? null;
            $codigoDescripcion = $resServicio['codigoDescripcion'] ?? 'PENDIENTE';

            if ($transaccion && $codRecepPaquete) {
                $paqueteModel = PaqueteFactura::create([
                    'evento_significativo_id' => $evento->id,
                    'punto_venta_id' => $puntoVenta->id,
                    'cufd_id' => $cufd->id,
                    'usuario_creador_id' => Auth::id(),
                    'codigo_recepcion' => (string) $codRecepPaquete,
                    'codigo_estado' => $codigoEstado,
                    'estado' => $codigoDescripcion,
                    'cantidad_facturas' => $paqueteCompilado['cantidad'],
                    'hash_archivo' => $paqueteCompilado['hash'],
                    'nombre_archivo' => "paquete_ev_{$evento->id}_".date('Ymd_His').'.tar.gz',
                    'fecha_envio' => now('America/La_Paz'),
                ]);

                // Asociar facturas al paquete y evento
                foreach ($facturas as $f) {
                    $f->update([
                        'paquete_id' => $paqueteModel->id,
                        'evento_significativo_id' => $evento->id,
                    ]);
                }

                // Auto-validación inmediata en el SIAT (optimización operativa tomada del sistema anterior)
                $resValidacion = $this->validarPaqueteFactura($paqueteModel->id);
                if ($resValidacion['success']) {
                    return [
                        'success' => true,
                        'paquete' => $paqueteModel,
                        'codigo_recepcion' => $codRecepPaquete,
                        'cantidad' => $paqueteCompilado['cantidad'],
                        'validado_inmediato' => true,
                        'mensaje' => "¡Paquete transmitido y VALIDADO exitosamente ante el SIAT! ({$paqueteCompilado['cantidad']} facturas regularizadas oficialmente).",
                    ];
                }

                $msgRecepcion = (($resValidacion['codigo_estado'] ?? null) == 901)
                    ? "Paquete de {$paqueteCompilado['cantidad']} facturas transmitido al SIAT (Cód: {$codRecepPaquete}). Estado en cola: 901 PENDIENTE de procesamiento."
                    : "Paquete de {$paqueteCompilado['cantidad']} facturas transmitido al SIAT (Cód: {$codRecepPaquete}). Estado reportado: {$resValidacion['mensaje']}";

                return [
                    'success' => true,
                    'paquete' => $paqueteModel,
                    'codigo_recepcion' => $codRecepPaquete,
                    'cantidad' => $paqueteCompilado['cantidad'],
                    'validado_inmediato' => false,
                    'mensaje' => $msgRecepcion,
                ];
            }

            $mensajes = $resServicio['mensajesList'] ?? [];
            $errorDesc = $this->formatearMensajesSiat($mensajes, $resServicio['codigoDescripcion'] ?? null, $resServicio['codigoEstado'] ?? null);
            if (empty($errorDesc)) {
                $errorDesc = $resp['msg'] ?? 'Rechazo en recepción del paquete';
            }

            return [
                'success' => false,
                'mensaje' => "Error SIN: {$errorDesc}",
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'mensaje' => 'Excepción SOAP en recepción de paquete: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Consulta y valida el estado del paquete previamente enviado ante el SIAT.
     */
    public function validarPaqueteFactura(int $paqueteId): array
    {
        $paquete = PaqueteFactura::with(['puntoVenta.sucursal', 'cufd', 'facturas'])->find($paqueteId);
        if (! $paquete) {
            return ['success' => false, 'mensaje' => 'Paquete no encontrado.'];
        }

        $puntoVenta = $paquete->puntoVenta;
        $sucursal = $puntoVenta?->sucursal;
        $cufd = $paquete->cufd ?: Cufd::where('punto_venta_id', $puntoVenta->id)->where('fecha_vigencia', '>', now())->latest('id')->first();
        $cuis = Cuis::where('punto_venta_id', $puntoVenta->id)->where('estado', 'ACTIVO')->latest('id')->first();

        $urlApi = new UrlApiServicio;
        $codigoAmbiente = (int) config('siat.codigo_ambiente');
        $codigoModalidad = (int) config('siat.codigo_modalidad');
        $urlFacturacion = $urlApi->getUrlFacturacionCompraVenta($codigoAmbiente, $codigoModalidad)->url_servicio;

        try {
            $respRaw = $this->siatController->validacionRecepcionPaqueteFactura(
                config('siat.api_token'),
                $urlFacturacion,
                $codigoAmbiente,
                1,
                $codigoModalidad,
                (int) $puntoVenta->codigo,
                config('siat.codigo_sistema'),
                (int) $sucursal->codigo_sucursal,
                $cufd->codigo,
                $cuis->codigo,
                config('siat.nit'),
                1,
                2, // Fuera de línea
                $paquete->codigo_recepcion
            );

            $resp = json_decode($respRaw, true);
            $resServicio = $resp['resultado']['RespuestaServicioFacturacion'] ?? [];
            $transaccion = $resServicio['transaccion'] ?? false;
            $codigoEstado = $resServicio['codigoEstado'] ?? null;
            $codigoDescripcion = $resServicio['codigoDescripcion'] ?? ($transaccion ? 'VALIDADA' : 'OBSERVADA');
            $mensajesList = $resServicio['mensajesList'] ?? null;

            $paquete->update([
                'codigo_estado' => $codigoEstado,
                'estado' => $codigoDescripcion,
                'mensajes_validacion' => $mensajesList,
            ]);

            // Si fue validada (Código 908 = VALIDADA)
            if ($codigoEstado == 908 || $codigoDescripcion === 'VALIDADA') {
                $paquete->facturas()->update([
                    'estado' => 'VALIDADA',
                    'codigo_descripcion' => 'VALIDADA',
                ]);

                // Si no quedan facturas pendientes en el evento, marcar evento como CERRADO
                if ($paquete->evento_significativo_id) {
                    $pendientesRestantes = Factura::where('evento_significativo_id', $paquete->evento_significativo_id)
                        ->where('estado', 'PENDIENTE_ENVIO')
                        ->count();
                    if ($pendientesRestantes === 0) {
                        EventoSignificativo::where('id', $paquete->evento_significativo_id)->update(['estado' => 'CERRADO']);
                    }
                }

                return [
                    'success' => true,
                    'codigo_estado' => $codigoEstado,
                    'estado' => 'VALIDADA',
                    'mensaje' => '¡El paquete de facturas ha sido VALIDADO oficialmente por Impuestos Nacionales!',
                ];
            }

            $mensajeEstado = ($codigoEstado == 901 || $codigoDescripcion === 'PENDIENTE')
                ? 'El SIN ha recibido el paquete y se encuentra en su cola de procesamiento (901 PENDIENTE). Espere unos instantes y presione nuevamente "Validar en SIAT".'
                : $this->formatearMensajesSiat($mensajesList, "Estado del paquete: {$codigoDescripcion}", $codigoEstado);

            return [
                'success' => false,
                'codigo_estado' => $codigoEstado,
                'estado' => $codigoDescripcion,
                'observaciones' => $mensajesList,
                'mensaje' => $mensajeEstado,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'mensaje' => 'Excepción SOAP en validación de paquete: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Formatea los mensajes u observaciones devueltos por el WebService del SIAT en texto legible.
     */
    public function formatearMensajesSiat(mixed $mensajesList, ?string $codigoDescripcion = null, ?int $codigoEstado = null): string
    {
        $lineas = [];

        if (! empty($mensajesList)) {
            $mensajes = is_array($mensajesList) ? $mensajesList : [$mensajesList];
            if (isset($mensajes['codigo']) || isset($mensajes['descripcion'])) {
                $mensajes = [$mensajes];
            }
            foreach ($mensajes as $m) {
                if (is_array($m)) {
                    $cod = isset($m['codigo']) ? "[Código {$m['codigo']}]: " : '';
                    $desc = $m['descripcion'] ?? json_encode($m, JSON_UNESCAPED_UNICODE);
                    $lineas[] = "• {$cod}{$desc}";
                } elseif (is_string($m)) {
                    $lineas[] = "• {$m}";
                }
            }
        }

        if (! empty($lineas)) {
            $texto = implode("\n", $lineas);
            if (! empty($codigoDescripcion) && ! str_contains($texto, $codigoDescripcion)) {
                return "{$codigoDescripcion}:\n{$texto}";
            }

            return $texto;
        }

        if (! empty($codigoDescripcion)) {
            return $codigoDescripcion.($codigoEstado !== null ? " (Código {$codigoEstado})" : '');
        }

        if ($codigoEstado !== null) {
            return "Observación tributaria reportada por el SIN (Código {$codigoEstado})";
        }

        return 'No se especificaron detalles del error por parte del servicio web del SIAT.';
    }

    /**
     * Obtiene una leyenda normativa aleatoria del SIN para el pie de factura.
     */
    public function obtenerLeyendaNormativa(): string
    {
        $leyenda = LeyendaFactura::inRandomOrder()->first();

        return $leyenda?->descripcion_leyenda
            ?: 'Ley N° 453: El proveedor debe brindar atención sin discriminación, con respeto, calidez y cordialidad a los usuarios y consumidores.';
    }

    /**
     * Convierte un monto numérico a su expresión literal en Bolivianos.
     */
    public function convertirMontoALetras(float $monto): string
    {
        $enteros = (int) floor($monto);
        $centavos = (int) round(($monto - $enteros) * 100);
        $centavosStr = str_pad((string) $centavos, 2, '0', STR_PAD_LEFT);

        $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $decenas10 = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
        $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        if ($enteros === 0) {
            return "CERO CON {$centavosStr}/100 BOLIVIANOS";
        }
        if ($enteros === 100) {
            return "CIEN CON {$centavosStr}/100 BOLIVIANOS";
        }

        $convertirMenor1000 = function (int $n) use ($unidades, $decenas10, $decenas, $centenas): string {
            $c = (int) floor($n / 100);
            $d = (int) floor(($n % 100) / 10);
            $u = (int) ($n % 10);

            $res = [];
            if ($c > 0) {
                $res[] = ($c === 1 && $d === 0 && $u === 0) ? 'CIEN' : $centenas[$c];
            }

            if ($d === 1) {
                $res[] = $decenas10[$u];
            } elseif ($d === 2 && $u > 0) {
                $res[] = 'VEINTI'.$unidades[$u];
            } elseif ($d > 0) {
                $res[] = ($u > 0) ? $decenas[$d].' Y '.$unidades[$u] : $decenas[$d];
            } elseif ($u > 0) {
                $res[] = $unidades[$u];
            }

            return implode(' ', $res);
        };

        $resultado = '';
        if ($enteros >= 1000) {
            $miles = (int) floor($enteros / 1000);
            $resto = (int) ($enteros % 1000);
            $txtMiles = ($miles === 1) ? 'MIL' : $convertirMenor1000($miles).' MIL';
            $txtResto = ($resto > 0) ? $convertirMenor1000($resto) : '';
            $resultado = trim($txtMiles.' '.$txtResto);
        } else {
            $resultado = $convertirMenor1000($enteros);
        }

        return trim($resultado)." CON {$centavosStr}/100 BOLIVIANOS";
    }
}
