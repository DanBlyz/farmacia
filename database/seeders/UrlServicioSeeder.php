<?php

namespace Database\Seeders;

use App\Models\UrlApiServicio;
use Illuminate\Database\Seeder;

class UrlServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UrlApiServicio::create([
            'nombre' => 'url_facturacionSincronizacion',
            'url_servicio' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionSincronizacion?wsdl',
            'ambiente' => 2,
            'modalidad' => 2,
        ]);
        UrlApiServicio::create([
            'nombre' => 'url_facturacionCodigos',
            'url_servicio' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionCodigos?wsdl',
            'ambiente' => 2,
            'modalidad' => 2,
        ]);
        UrlApiServicio::create([
            'nombre' => 'url_servicio_facturacion_compra_venta',
            'url_servicio' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/ServicioFacturacionCompraVenta?wsdl',
            'ambiente' => 2,
            'modalidad' => 2,
        ]);
        UrlApiServicio::create([
            'nombre' => 'url_facturacion_operaciones',
            'url_servicio' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionOperaciones?wsdl',
            'ambiente' => 2,
            'modalidad' => 2,
        ]);
        /* UrlApiServicio::create([
            'nombre' => 'url_facturacionSincronizacion',
            'url_servicio' => 'https://pilotosiatservicios.impuestos.gob.bo/sdf/FacturacionSincronizacion?wsdl',
            'ambiente' => 2,
            'modalidad' => 2,
        ]);
        UrlApiServicio::create([
            'nombre' => 'url_facturacionCodigos',
            'url_servicio' => 'https://pilotosiatservicios.impuestos.gob.bo/sdf/FacturacionCodigos?wsdl',
            'ambiente' => 2,
            'modalidad' => 2,
        ]);
        UrlApiServicio::create([
            'nombre' => 'url_servicio_facturacion_compra_venta',
            'url_servicio' => 'https://pilotosiatservicios.impuestos.gob.bo/sdf/ServicioFacturacionCompraVenta?wsdl',
            'ambiente' => 2,
            'modalidad' => 2,
        ]);
        UrlApiServicio::create([
            'nombre' => 'url_facturacion_operaciones',
            'url_servicio' => 'https://pilotosiatservicios.impuestos.gob.bo/sdf/FacturacionOperaciones?wsdl',
            'ambiente' => 2,
            'modalidad' => 2,
        ]); */
    }
}
