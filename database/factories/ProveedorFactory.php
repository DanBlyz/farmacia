<?php

namespace Database\Factories;

use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProveedorFactory extends Factory
{
    protected $model = Proveedor::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->name(),
            'direccion' => $this->faker->streetAddress(),
            'celular' => $this->faker->phoneNumber(),
            'nit' => $this->faker->unique()->numberBetween(10000000, 99999999),
            'razon_social' => 'Laboratorio '.$this->faker->company(),
            'usuario_creador_id' => User::factory(),
        ];
    }
}
