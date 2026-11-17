<?php

use App\Models\UrlApiServicio;
use Illuminate\Support\Facades\Http;

test('comando siat:verificar-v3 advierte cuando los servicios responden 503 por mantenimiento', function () {
    Http::fake([
        '*' => Http::response('<html>Service Temporarily Unavailable</html>', 503),
    ]);

    UrlApiServicio::updateOrCreate(
        ['nombre' => 'url_facturacionCodigos', 'ambiente' => 2, 'modalidad' => 2],
        ['url_servicio' => 'https://pilotosiatservicios.impuestos.gob.bo/sdf/FacturacionCodigos?wsdl']
    );

    $this->artisan('siat:verificar-v3')
        ->expectsOutputToContain('Mantenimiento en Servidor SIN (HTTP 503)')
        ->expectsOutputToContain('ATENCIÓN: El servidor del SIAT V3')
        ->assertSuccessful();
});

test('comando siat:verificar-v3 reporta estado operativo cuando los servicios responden 200', function () {
    Http::fake([
        '*' => Http::response('<?xml version="1.0"?><wsdl:definitions></wsdl:definitions>', 200),
    ]);

    UrlApiServicio::updateOrCreate(
        ['nombre' => 'url_facturacionCodigos', 'ambiente' => 2, 'modalidad' => 2],
        ['url_servicio' => 'https://pilotosiatservicios.impuestos.gob.bo/sdf/FacturacionCodigos?wsdl']
    );

    $this->artisan('siat:verificar-v3')
        ->expectsOutputToContain('Operativo (WSDL Disponible)')
        ->expectsOutputToContain('Todos los servicios SOAP V3 se encuentran activos')
        ->assertSuccessful();
});
