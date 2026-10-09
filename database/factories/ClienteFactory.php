<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'nombres' => $this->faker->firstName(),
            'ap_paterno' => $this->faker->lastName(),
            'ap_materno' => $this->faker->lastName(),
            'cedula' => $this->faker->unique()->numberBetween(1000000, 9999999),
            'complemento' => '',
            'nit' => '',
            'razon_social' => '',
            'correo' => $this->faker->unique()->safeEmail(),
            'numero_celular' => $this->faker->phoneNumber(),
            'usuario_creador_id' => User::factory(),
        ];
    }
}
