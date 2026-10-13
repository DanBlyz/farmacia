<?php

namespace App\Http\Controllers;

use App\Models\Cufd;
use App\Models\Cuis;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Models\UrlApiServicio;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use SoapFault;

class SiatController extends Controller
{
    protected $timeout = 1;

    protected $codigo_ambiente;

    protected $codigo_modalidad;

    protected $api_token;

    protected $codigo_sistema;

    protected $nit;

    // *************************** GENERACION DE DATOS Y CODIGOS **************************
    public function cuis($header, $url1, $codigoAmbiente, $codigoModalidad, $codigoPuntoVenta, $codigoSistema, $codigoSucursal, $nit)
    {
        $wsdl = $url1;
        $parametros = [
            'SolicitudCuis' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoModalidad' => $codigoModalidad,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->cuis($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
            $data['msg'] = $fault;

        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function sincronizarActividades($header, $url2, $codigoAmbiente, $codigoPuntoVenta, $codigoSistema, $codigoSucursal, $scuis, $nit)
    {

        $wsdl = $url2;
        $codigoAmbiente = $codigoAmbiente;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->sincronizarActividades($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
            $data['mensaje'] = $fault->getMessage();
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function sincronizarListaProductosServicios($header, $url2, $codigoAmbiente, $codigoPuntoVenta, $codigoSistema, $codigoSucursal, $scuis, $nit)
    {
        $wsdl = $url2;
        $codigoAmbiente = $codigoAmbiente;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->sincronizarListaProductosServicios($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
            $data['msg'] = $fault;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function anulacionFactura(
        $header,
        $url3,
        $codigoAmbiente,
        $codigoDocumentoSector,
        $codigoModalidad,
        $codigoPuntoVenta,
        $codigoSistema,
        $codigoSucursal,
        $scufd,
        $scuis,
        $nit,
        $tipoFacturaDocumento,

        $codMot, $cuf1
    ) {
        // ESO VERIFICAR !!!!!!!!!!!!! OJOOOO !!!!!!!!!!! PIOJO!!!!!!!!!
        // $this->verificarConeccion();
        // ESO VERIFICAR !!!!!!!!!!!!! OJOOOO !!!!!!!!!!! PIOJO!!!!!!!!!

        $wsdl = $url3;
        $codigoAmbiente = $codigoAmbiente;
        $codigoDocumentoSector = $codigoDocumentoSector; // NUEVO SECTOR EDUCATIIVO
        $codigoEmision = 1; // NUEVO LINENA
        $codigoModalidad = $codigoModalidad;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cufd = $scufd; // NUEVO
        $cuis = $scuis;
        $nit = $nit;
        // $tipoFacturaDocumento   = 1; //NUEVO FACTURA CON DERECHO A CREDITO FISCAL
        $codigoMotivo = $codMot;
        $cuf = $cuf1;

        $parametros = [
            'SolicitudServicioAnulacionFactura' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoDocumentoSector' => $codigoDocumentoSector,
                'codigoEmision' => $codigoEmision,
                'codigoModalidad' => $codigoModalidad,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cufd' => $cufd,
                'cuis' => $cuis,
                'nit' => $nit,
                'tipoFacturaDocumento' => $tipoFacturaDocumento,
                'codigoMotivo' => $codigoMotivo,
                'cuf' => $cuf,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->anulacionFactura($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
            $data['msg'] = $fault;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function reversionAnulacionFactura(
        $header,
        $url3,
        $codigoAmbiente,
        $codigoDocumentoSector,
        $codigoModalidad,
        $codigoPuntoVenta,
        $codigoSistema,
        $codigoSucursal,
        $scufd,
        $scuis,
        $nit,
        $cuf,
        $tipoFacturaDocumento
    ) {
        $wsdl = $url3;
        $codigoAmbiente = $codigoAmbiente;
        $codigoDocumentoSector = $codigoDocumentoSector; // NUEVO SECTOR EDUCATIIVO
        $codigoEmision = 1; // NUEVO LINENA
        $codigoModalidad = $codigoModalidad;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cufd = $scufd; // NUEVO
        $cuis = $scuis;
        $nit = $nit;
        // $tipoFacturaDocumento   = 1; //NUEVO FACTURA CON DERECHO A CREDITO FISCAL
        $cuf = $cuf;

        $parametros = [
            'SolicitudServicioReversionAnulacionFactura' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoDocumentoSector' => $codigoDocumentoSector,
                'codigoEmision' => $codigoEmision,
                'codigoModalidad' => $codigoModalidad,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cufd' => $cufd,
                'cuis' => $cuis,
                'nit' => $nit,
                'tipoFacturaDocumento' => $tipoFacturaDocumento,
                'cuf' => $cuf,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->reversionAnulacionFactura($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);

    }

    public function consultaPuntoVenta($header, $url4, $codigoAmbiente, $codigoSistema, $codigoSucursal, $scuis, $nit)
    {
        $wsdl = $url4;
        $codigoAmbiente = $codigoAmbiente;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudConsultaPuntoVenta' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->consultaPuntoVenta($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
            $data['msg'] = $fault;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function registroPuntoVenta($descripcionPuntoVenta, $nombrePuntoVenta, $header, $url4, $codigoAmbiente, $codigoModalidad, $codigoSistema, $codigoSucursal, $codigoTipoPuntoVenta, $scuis, $nit)
    {
        $wsdl = $url4;
        $codigoAmbiente = $codigoAmbiente;
        $codigoModalidad = $codigoModalidad;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $codigoTipoPuntoVenta = $codigoTipoPuntoVenta;                        // PUNTO VENTA VENTANILLA DE COBRANZA
        $cuis = $scuis;
        $descripcion = $descripcionPuntoVenta;
        $nit = $nit;
        $nombrePuntoVenta = $nombrePuntoVenta;

        $parametros = [
            'SolicitudRegistroPuntoVenta' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoModalidad' => $codigoModalidad,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'codigoTipoPuntoVenta' => $codigoTipoPuntoVenta,
                'cuis' => $cuis,
                'descripcion' => $descripcion,
                'nit' => $nit,
                'nombrePuntoVenta' => $nombrePuntoVenta,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->registroPuntoVenta($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function cierrePuntoVenta($header, $url4, $codigoAmbiente, $codigoPuntoVenta, $codigoSistema, $codigoSucursal, $scuis, $nit)
    {
        $wsdl = $url4;
        $codigoAmbiente = $codigoAmbiente;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudCierrePuntoVenta' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->cierrePuntoVenta($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
            $data['msg'] = $fault->getMessage();
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function cierreOperacionesSistema($header, $url4, $codigoAmbiente, $codigoModalidad, $codigoPuntoVenta, $codigoSistema, $codigoSucursal, $scuis, $nit)
    {
        $wsdl = $url4;
        $codigoAmbiente = $codigoAmbiente;
        $codigoModalidad = $codigoModalidad;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudOperaciones' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoModalidad' => $codigoModalidad,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->cierreOperacionesSistema($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
            $data['msg'] = $fault->getMessage();
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    // ********************* SINCRONIZACION DE CATALOGOS *********************
    public function sincronizarParametricaTipoDocumentoSector($header, $url2, $codigoAmbiente, $codigoPuntoVenta, $codigoSistema, $codigoSucursal, $scuis, $nit)
    {
        $wsdl = $url2;
        $codigoAmbiente = $codigoAmbiente;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->sincronizarParametricaTipoDocumentoSector($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
            $data['msg'] = $fault;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function sincronizarParametricaTipoPuntoVenta($header, $url2, $codigoAmbiente, $codigoPuntoVenta, $codigoSistema, $codigoSucursal, $scuis, $nit)
    {

        $wsdl = $url2;
        $codigoAmbiente = $codigoAmbiente;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->sincronizarParametricaTipoPuntoVenta($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function sincronizarParametricaUnidadMedida($header, $url2, $codigoAmbiente, $codigoPuntoVenta, $codigoSistema, $codigoSucursal, $scuis, $nit)
    {
        $wsdl = $url2;
        $codigoAmbiente = $codigoAmbiente;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->sincronizarParametricaUnidadMedida($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function sincronizarParametricaTipoDocumentoIdentidad($header, $url2, $codigoAmbiente, $codigoPuntoVenta, $codigoSistema, $codigoSucursal, $scuis, $nit)
    {
        $wsdl = $url2;
        $codigoAmbiente = $codigoAmbiente;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->sincronizarParametricaTipoDocumentoIdentidad($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function sincronizarParametricaTipoMetodoPago($header, $url2, $codigoAmbiente, $codigoPuntoVenta, $codigoSistema, $codigoSucursal, $scuis, $nit)
    {
        $wsdl = $url2;
        $codigoAmbiente = $codigoAmbiente;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->sincronizarParametricaTipoMetodoPago($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function sincronizarParametricaTipoMoneda($header, $url2, $codigoAmbiente, $codigoPuntoVenta, $codigoSistema, $codigoSucursal, $scuis, $nit)
    {
        $wsdl = $url2;
        $codigoAmbiente = $codigoAmbiente;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->sincronizarParametricaTipoMoneda($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function sincronizarParametricaMotivoAnulacion(
        $header,
        $url2,
        $codigoAmbiente,
        $codigoPuntoVenta,
        $codigoSistema,
        $codigoSucursal,
        $scuis,
        $nit
    ) {
        $wsdl = $url2;
        $codigoAmbiente = $codigoAmbiente;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->sincronizarParametricaMotivoAnulacion($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function sincronizarParametricaEventosSignificativos(
        $header,
        $url2,
        $codigoAmbiente,
        $codigoPuntoVenta,
        $codigoSistema,
        $codigoSucursal,
        $scuis,
        $nit
    ) {
        $wsdl = $url2;
        $codigoAmbiente = $codigoAmbiente;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->sincronizarParametricaEventosSignificativos($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function sincronizarFechaHora(
        $header,
        $url2,
        $codigoAmbiente,
        $codigoPuntoVenta,
        $codigoSistema,
        $codigoSucursal,
        $scuis,
        $nit
    ) {
        // $this->verificarConeccion();
        $wsdl = $url2;
        $codigoAmbiente = $codigoAmbiente;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->sincronizarFechaHora($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
        }

        // dd($data);

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function sincronizarListaActividadesDocumentoSector(
        $header,
        $url2,
        $codigoAmbiente,
        $codigoPuntoVenta,
        $codigoSistema,
        $codigoSucursal,
        $scuis,
        $nit
    ) {
        // $this->verificarConeccion();
        $wsdl = $url2;
        $codigoAmbiente = $codigoAmbiente;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->sincronizarListaActividadesDocumentoSector($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
        }

        // dd($data);

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function sincronizarListaLeyendasFactura(
        $header,
        $url2,
        $codigoAmbiente,
        $codigoPuntoVenta,
        $codigoSistema,
        $codigoSucursal,
        $scuis,
        $nit
    ) {
        // $this->verificarConeccion();
        $wsdl = $url2;
        $codigoAmbiente = $codigoAmbiente;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->sincronizarListaLeyendasFactura($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
        }

        // dd($data);

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function sincronizarListaMensajesServicios(
        $header,
        $url2,
        $codigoAmbiente,
        $codigoPuntoVenta,
        $codigoSistema,
        $codigoSucursal,
        $scuis,
        $nit
    ) {
        // $this->verificarConeccion();
        $wsdl = $url2;
        $codigoAmbiente = $codigoAmbiente;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->sincronizarListaMensajesServicios($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
        }

        // dd($data);

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function sincronizarParametricaPaisOrigen(
        $header,
        $url2,
        $codigoAmbiente,
        $codigoPuntoVenta,
        $codigoSistema,
        $codigoSucursal,
        $scuis,
        $nit
    ) {
        // $this->verificarConeccion();
        $wsdl = $url2;
        $codigoAmbiente = $codigoAmbiente;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->sincronizarParametricaPaisOrigen($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function sincronizarParametricaTipoEmision(
        $header,
        $url2,
        $codigoAmbiente,
        $codigoPuntoVenta,
        $codigoSistema,
        $codigoSucursal,
        $scuis,
        $nit
    ) {
        // $this->verificarConeccion();
        $wsdl = $url2;
        $codigoAmbiente = $codigoAmbiente;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->sincronizarParametricaTipoEmision($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function sincronizarParametricaTipoHabitacion(
        $header,
        $url2,
        $codigoAmbiente,
        $codigoPuntoVenta,
        $codigoSistema,
        $codigoSucursal,
        $scuis,
        $nit
    ) {
        // $this->verificarConeccion();
        $wsdl = $url2;
        $codigoAmbiente = $codigoAmbiente;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->sincronizarParametricaTipoHabitacion($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function sincronizarParametricaTiposFactura(
        $header,
        $url2,
        $codigoAmbiente,
        $codigoPuntoVenta,
        $codigoSistema,
        $codigoSucursal,
        $scuis,
        $nit
    ) {
        // $this->verificarConeccion();
        $wsdl = $url2;
        $codigoAmbiente = $codigoAmbiente;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;

        $parametros = [
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->sincronizarParametricaTiposFactura($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    // ********************* SINCRONIZACION DE CATALOGOS *********************

    // ********************* CONECCIONES *********************
    public function verificarComunicacion($url1, $header)
    {
        $wsdl = $url1;
        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);
        $data = [];

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);
            $resultado = $client->verificarComunicacion();
            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
            $data['msg'] = $fault->getMessage();
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function verificarConeccion($sucursal_id, $cuis_id, $punto_venta_id, $codigoAmbiente)
    {

        $this->inicializarVariables();
        $cufdDelDia = Cufd::where('sucursal_id', $sucursal_id)
            ->where('cuis_id', $cuis_id)
            ->where('punto_venta_id', $punto_venta_id)
            ->where('codigo_ambiente', $codigoAmbiente)
            ->latest()
            ->first();

        // dd(
        //     $cufdDelDia,$sucursal_id, $cuis_id, $punto_venta_id, $codigoAmbiente
        // );

        $cufdRescatadoUtilizar = null;

        $sucursal = Sucursal::find($sucursal_id);
        $cuis = Cuis::find($cuis_id);
        $punto_venta = PuntoVenta::find($punto_venta_id);

        $urlApiServicioSiat = new UrlApiServicio;
        $UrlSincronizacion = $urlApiServicioSiat->getUrlCodigos($this->codigo_ambiente, $this->codigo_modalidad);

        if ($UrlSincronizacion == null) {
            dd([
                'ambiente' => $this->codigo_ambiente,
                'modalidad' => $this->codigo_modalidad,
                'resultado' => $urlApiServicioSiat->getUrlCodigos($this->codigo_ambiente, $this->codigo_modalidad),
            ]);
        }

        $header = $this->api_token;
        $url1 = $UrlSincronizacion->url_servicio;
        $codigoAmbiente = $this->codigo_ambiente;
        $codigoModalidad = $this->codigo_modalidad;
        $codigoPuntoVenta = $punto_venta->codigo;
        $codigoSistema = $this->codigo_sistema;
        $codigoSucursal = $sucursal->codigo_sucursal;
        $scuis = $cuis->codigo;
        $nit = $this->nit;

        if ($cufdDelDia) {
            $fechaVigencia = $cufdDelDia->fecha_vigencia;
            if ($fechaVigencia < date('Y-m-d H:i')) {

                $cufd = json_decode($this->cufd(
                    $header,
                    $url1,
                    $codigoAmbiente,
                    $codigoModalidad,
                    $codigoPuntoVenta,
                    $codigoSistema,
                    $codigoSucursal,
                    $scuis,
                    $nit
                ));

                if ($cufd->estado == 'success') {
                    if ($cufd->resultado->RespuestaCufd->transaccion) {
                        $cufdNew = new Cufd;
                        $cufdNew->usuario_creador_id = Auth::user()->id;
                        $cufdNew->sucursal_id = $sucursal_id;
                        $cufdNew->cuis_id = $cuis_id;
                        $cufdNew->punto_venta_id = $punto_venta_id;
                        $cufdNew->codigo_ambiente = $codigoAmbiente;
                        $cufdNew->codigo = $cufd->resultado->RespuestaCufd->codigo;
                        $cufdNew->codigo_control = $cufd->resultado->RespuestaCufd->codigoControl;
                        $cufdNew->direccion = $cufd->resultado->RespuestaCufd->direccion;
                        $cufdNew->fecha_vigencia = Carbon::parse($cufd->resultado->RespuestaCufd->fechaVigencia)->format('Y-m-d H:i:s');
                        $cufdNew->save();
                        $cufdRescatadoUtilizar = $cufdNew;
                    } else {
                        $cufdRescatadoUtilizar = null;
                    }
                } else {
                    $cufdRescatadoUtilizar = null;
                }
            } else {
                $cufdRescatadoUtilizar = $cufdDelDia;
            }
        } else {
            $cufd = json_decode($this->cufd(
                $header,
                $url1,
                $codigoAmbiente,
                $codigoModalidad,
                $codigoPuntoVenta,
                $codigoSistema,
                $codigoSucursal,
                $scuis,
                $nit
            ));

            if ($cufd->estado == 'success') {
                if ($cufd->resultado->RespuestaCufd->transaccion) {

                    $cufdNew = new Cufd;
                    $cufdNew->usuario_creador_id = Auth::user()->id;
                    $cufdNew->sucursal_id = $sucursal_id;
                    $cufdNew->cuis_id = $cuis_id;
                    $cufdNew->punto_venta_id = $punto_venta_id;
                    $cufdNew->codigo_ambiente = $codigoAmbiente;
                    $cufdNew->codigo = $cufd->resultado->RespuestaCufd->codigo;
                    $cufdNew->codigo_control = $cufd->resultado->RespuestaCufd->codigoControl;
                    $cufdNew->direccion = $cufd->resultado->RespuestaCufd->direccion;
                    $cufdNew->fecha_vigencia = Carbon::parse($cufd->resultado->RespuestaCufd->fechaVigencia)->format('Y-m-d H:i:s');
                    $cufdNew->save();

                    $cufdRescatadoUtilizar = $cufdNew;

                } else {
                    $cufdRescatadoUtilizar = null;
                }
            } else {
                $cufdRescatadoUtilizar = null;
            }
        }

        return $cufdRescatadoUtilizar;
    }

    public function cufd(
        $header,
        $url1,
        $codigoAmbiente,
        $codigoModalidad,
        $codigoPuntoVenta,
        $codigoSistema,
        $codigoSucursal,
        $scuis,
        $nit
    ) {

        $comunicacion = json_decode($this->verificarComunicacion($url1, $header));

        if ($comunicacion->estado == 'success') {
            if ($comunicacion->resultado->RespuestaComunicacion->transaccion) {
                $wsdl = $url1;
                $codigoAmbiente = $codigoAmbiente;
                $codigoModalidad = $codigoModalidad;
                $codigoPuntoVenta = $codigoPuntoVenta;
                $codigoSistema = $codigoSistema;
                $codigoSucursal = $codigoSucursal;
                $cuis = $scuis;
                $nit = $nit;
                $parametros = [
                    'SolicitudCufd' => [
                        'codigoAmbiente' => $codigoAmbiente,
                        'codigoModalidad' => $codigoModalidad,
                        'codigoPuntoVenta' => $codigoPuntoVenta,
                        'codigoSistema' => $codigoSistema,
                        'codigoSucursal' => $codigoSucursal,
                        'cuis' => $cuis,
                        'nit' => $nit,
                    ],
                ];
                $aoptions = [
                    'http' => [
                        'header' => $header,
                        'timeout' => $this->timeout,
                    ],
                ];
                $context = stream_context_create($aoptions);
                try {
                    $client = new \SoapClient($wsdl, [
                        'stream_context' => $context,
                        'cache_wsdl' => WSDL_CACHE_NONE,
                        'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
                    ]);

                    $resultado = $client->cufd($parametros);
                    $data['estado'] = 'success';
                    $data['resultado'] = $resultado;
                } catch (SoapFault $fault) {
                    $resultado = false;
                    $data['estado'] = 'error';
                    $data['resultado'] = $resultado;
                    $data['error'] = $fault;
                    $data['msgS'] = $fault->getMessage();
                } catch (Exception $e) {
                    // Captura cualquier otra excepción y maneja el error
                    $data['msgE'] = $e->getMessage();
                }

                // dd($data);

                return json_encode($data, JSON_UNESCAPED_UNICODE);

            } else {
                $data['estado'] = 'error';
                $data['resultado'] = $comunicacion->resultado;
            }
        } else {
            $data['estado'] = 'error';
            $data['resultado'] = $comunicacion;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function verificarNit($url1, $header, $codigoAmbiente, $codigoPuntoVenta, $codigoSistema, $codigoSucursal, $scuis, $nit, $nitVeri)
    {
        $wsdl = $url1;
        $codigoAmbiente = $codigoAmbiente;
        $codigoModalidad = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cuis = $scuis;
        $nit = $nit;
        $nitParaVerificacion = $nitVeri;

        $parametros = [
            'SolicitudVerificarNit' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoModalidad' => $codigoModalidad,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cuis' => $cuis,
                'nit' => $nit,
                'nitParaVerificacion' => $nitParaVerificacion,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->verificarNit($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
            $data['mensaje'] = $fault->getMessage();
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    // ********************* CONECCIONES *********************

    public function recepcionFactura(
        $header,
        $url3,
        $codigoAmbiente,
        $codigoDocumentoSector,
        $codigoModalidad,
        $codigoPuntoVenta,
        $codigoSistema,
        $codigoSucursal,
        $scufd,
        $scuis,
        $nit,
        $tipoFacturaDocumento,

        $arch, $fecEnv, $hasArch
    ) {

        // dd(
        //     $header,
        //     $url3,
        //     $codigoAmbiente,
        //     $codigoDocumentoSector,
        //     $codigoModalidad,
        //     $codigoPuntoVenta,
        //     $codigoSistema,
        //     $codigoSucursal,
        //     $scufd,
        //     $scuis,
        //     $nit,

        //     $arch, $fecEnv, $hasArch
        // );
        // ESO VERIFICAR !!!!!!!!!!!!! OJOOOO !!!!!!!!!!! PIOJO!!!!!!!!!
        // $this->verificarConeccion();
        // ESO VERIFICAR !!!!!!!!!!!!! OJOOOO !!!!!!!!!!! PIOJO!!!!!!!!!
        $wsdl = $url3;
        $codigoAmbiente = $codigoAmbiente;
        $codigoDocumentoSector = $codigoDocumentoSector;     // NUEVO SECTOR EDUCATIIVO
        $codigoEmision = 1;                                // NUEVO LINENA
        $codigoModalidad = $codigoModalidad;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cufd = $scufd; // NUEVO
        $cuis = $scuis;
        $nit = $nit;
        $tipoFacturaDocumento = $tipoFacturaDocumento;                        // NUEVO FACTURA CON DERECHO A CREDITO FISCAL
        $archivo = $arch;
        $fechaEnvio = $fecEnv;
        $hashArchivo = $hasArch;

        $parametros = [
            'SolicitudServicioRecepcionFactura' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoDocumentoSector' => $codigoDocumentoSector,
                'codigoEmision' => $codigoEmision,
                'codigoModalidad' => $codigoModalidad,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cufd' => $cufd,
                'cuis' => $cuis,
                'nit' => $nit,
                'tipoFacturaDocumento' => $tipoFacturaDocumento,
                'archivo' => $archivo,
                'fechaEnvio' => $fechaEnvio,
                'hashArchivo' => $hashArchivo,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->recepcionFactura($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
            $data['msg'] = $fault->getMessage();
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function registroEventoSignificativo(
        $header,
        $url4,
        $codigoAmbiente,
        $codigoPuntoVenta,
        $codigoSistema,
        $codigoSucursal,
        $scufd,
        $scuis,
        $nit,
        $codMotEvent, $cufdEvent, $desc, $fechaIni, $fechaFin
    ) {

        $wsdl = $url4;
        $codigoAmbiente = $codigoAmbiente;
        $codigoMotivoEvento = $codMotEvent;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cufd = $scufd;
        $cufdEvento = $cufdEvent;
        $cuis = $scuis;
        $descripcion = $desc;
        $fechaHoraFinEvento = $fechaFin;
        $fechaHoraInicioEvento = $fechaIni;
        $nit = $nit;

        $parametros = [
            'SolicitudEventoSignificativo' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoMotivoEvento' => $codigoMotivoEvento,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cufd' => $cufd,
                'cufdEvento' => $cufdEvento,
                'cuis' => $cuis,
                'descripcion' => $descripcion,
                'fechaHoraFinEvento' => $fechaHoraFinEvento,
                'fechaHoraInicioEvento' => $fechaHoraInicioEvento,
                'nit' => $nit,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->registroEventoSignificativo($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
            $data['msg'] = $fault;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function recepcionPaqueteFactura(
        $header,
        $url3,
        $codigoAmbiente,
        $codigoDocumentoSector,
        $tipo_online_o_offline,
        $codigoModalidad,
        $codigoPuntoVenta,
        $codigoSistema,
        $codigoSucursal,
        $scufd,
        $scuis,
        $nit,
        $tipoFacturaDocumento,

        $arch, $fechaenv, $hasarch, $cafcC, $canFact, $codEvent
    ) {

        $wsdl = $url3;
        $codigoAmbiente = $codigoAmbiente;
        $codigoDocumentoSector = $codigoDocumentoSector;     // SECTOR EDUCATIVO
        // $codigoEmision          = 2;                                // FUERA DE  LINEA (LINEA = 1 | FUERA DE LINEA = 2)
        $codigoEmision = $tipo_online_o_offline;                                // FUERA DE  LINEA (LINEA = 1 | FUERA DE LINEA = 2)
        $codigoModalidad = $codigoModalidad;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cufd = $scufd;
        $cuis = $scuis;
        $nit = $nit;
        // $tipoFacturaDocumento   = 1;                        //NUEVO FACTURA CON DERECHO A CREDITO FISCAL
        $archivo = $arch;
        $fechaEnvio = $fechaenv;
        $hashArchivo = $hasarch;
        $cafc = $cafcC;
        $cantidadFacturas = $canFact;
        $codigoEvento = $codEvent;

        $parametros = [
            'SolicitudServicioRecepcionPaquete' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoDocumentoSector' => $codigoDocumentoSector,
                'codigoEmision' => $codigoEmision,
                'codigoModalidad' => $codigoModalidad,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cufd' => $cufd,
                'cuis' => $cuis,
                'nit' => $nit,
                'tipoFacturaDocumento' => $tipoFacturaDocumento,
                'archivo' => $archivo,
                'fechaEnvio' => $fechaEnvio,
                'hashArchivo' => $hashArchivo,
                'cafc' => $cafc,
                'cantidadFacturas' => $cantidadFacturas,
                'codigoEvento' => $codigoEvento,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->recepcionPaqueteFactura($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
            $data['msg'] = $fault->getMessage();
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function validacionRecepcionPaqueteFactura(
        $header,
        $url3,
        $codigoAmbiente,
        $codigoDocumentoSector,
        $codigoModalidad,
        $codigoPuntoVenta,
        $codigoSistema,
        $codigoSucursal,
        $scufd,
        $scuis,
        $nit,
        $tipoFacturaDocumento,

        $codEmision, $codRecepcion
    ) {
        $wsdl = $url3;
        $codigoAmbiente = $codigoAmbiente;
        $codigoDocumentoSector = $codigoDocumentoSector;                           // SECTOR EDUCATIVO
        $codigoEmision = $codEmision;                  // NUEVO LINENA 1 LINEA | 2 FUENRA DE LINEA
        $codigoModalidad = $codigoModalidad;
        $codigoPuntoVenta = $codigoPuntoVenta;
        $codigoSistema = $codigoSistema;
        $codigoSucursal = $codigoSucursal;
        $cufd = $scufd;
        $cuis = $scuis;
        $nit = $nit;
        // $tipoFacturaDocumento   = 1;                            //NUEVO FACTURA CON DERECHO A CREDITO FISCAL
        $codigoRecepcion = $codRecepcion;

        $parametros = [
            'SolicitudServicioValidacionRecepcionPaquete' => [
                'codigoAmbiente' => $codigoAmbiente,
                'codigoDocumentoSector' => $codigoDocumentoSector,
                'codigoEmision' => $codigoEmision,
                'codigoModalidad' => $codigoModalidad,
                'codigoPuntoVenta' => $codigoPuntoVenta,
                'codigoSistema' => $codigoSistema,
                'codigoSucursal' => $codigoSucursal,
                'cufd' => $cufd,
                'cuis' => $cuis,
                'nit' => $nit,
                'tipoFacturaDocumento' => $tipoFacturaDocumento,
                'codigoRecepcion' => $codigoRecepcion,
            ],
        ];

        $aoptions = [
            'http' => [
                'header' => $header,
                'timeout' => $this->timeout,
            ],
        ];

        $context = stream_context_create($aoptions);

        try {
            $client = new \SoapClient($wsdl, [
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);

            $resultado = $client->validacionRecepcionPaqueteFactura($parametros);

            $data['estado'] = 'success';
            $data['resultado'] = $resultado;
        } catch (SoapFault $fault) {
            $resultado = false;
            $data['estado'] = 'error';
            $data['resultado'] = $resultado;
            $data['msg'] = $fault;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    // FUNCIONES PRIVADAS
    private function inicializarVariables()
    {
        $this->codigo_ambiente = config('siat.codigo_ambiente');
        $this->codigo_modalidad = config('siat.codigo_modalidad');
        $this->api_token = config('siat.api_token');
        $this->codigo_sistema = config('siat.codigo_sistema');
        $this->nit = config('siat.nit');
    }
}
