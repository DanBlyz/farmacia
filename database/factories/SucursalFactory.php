<?php

namespace Database\Factories;

use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SucursalFactory extends Factory
{
    protected $model = Sucursal::class;

    public function definition(): array
    {
        return [
            'nombre' => 'Sucursal '.$this->faker->unique()->city(),
            'direccion' => $this->faker->streetAddress(),
            'codigo_sucursal' => 'SUC-'.$this->faker->unique()->numberBetween(100, 999),
            'usuario_creador_id' => User::factory(),
        ];
    }
}
