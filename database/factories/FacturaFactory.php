<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Factura>
 */
class FacturaFactory extends Factory
{
    protected $model = Factura::class;

    public function definition(): array
    {
        return [
            'usuario_creador_id' => User::factory(),
            'cufd_id' => null,
            'sucursal_id' => Sucursal::factory(),
            'caja_id' => null,
            'motivo_anulacion_id' => null,
            'cliente_id' => Cliente::factory(),
            'punto_venta_id' => null,
            'fecha' => now(),
            'nit' => '12345678',
            'razon_social' => 'CLIENTE FACTURA TEST',
            'numero_factura' => (string) fake()->unique()->numberBetween(1000, 99999),
            'facturado' => 'SI',
            'codigo_metodo_pago' => 1,
            'total' => 50.00,
            'monto_total_subjeto_iva' => 50.00,
            'descuento_adicional' => 0.00,
            'cuf' => strtoupper(fake()->bothify('??????????????????????????????????????##')),
            'tipo_factura' => '1',
            'estado_pago' => 'PAGADO',
            'estado' => 'VALIDADA',
        ];
    }

    public function anulada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'ANULADA',
            'fecha_anulacion' => now(),
            'motivo_anulacion_id' => 1,
            'motivo_anulacion_descripcion' => 'Factura mal emitida',
        ]);
    }
}
