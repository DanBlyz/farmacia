<?php

namespace Database\Factories;

use App\Models\Caja;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Caja>
 */
class CajaFactory extends Factory
{
    protected $model = Caja::class;

    public function definition(): array
    {
        return [
            'usuario_creador_id' => User::factory(),
            'usuario_id' => User::factory(),
            'sucursal_id' => Sucursal::factory(),
            'punto_venta_id' => null,
            'fecha_apertura' => now(),
            'fecha_cierre' => null,
            'monto_apertura' => 100.00,
            'monto_cierre' => null,
            'descripcion' => 'Apertura de turno test',
            'descripcion_cierre' => null,
            'total_venta' => 0,
            'venta_contado' => 0,
            'venta_credito' => 0,
            'otro_ingreso' => 0,
            'total_ingreso' => 100.00,
            'total_qr' => 0,
            'total_transferencia' => 0,
            'total_salida' => 0,
            'saldo' => 100.00,
            'estado' => 'ABIERTA',
        ];
    }

    public function cerrada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'CERRADA',
            'fecha_cierre' => now(),
            'monto_cierre' => 100.00,
        ]);
    }
}
