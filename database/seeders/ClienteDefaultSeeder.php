<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClienteDefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Garantiza el cliente genérico ID = 1 para consumidor final (S/N) según normativa SIN.
     */
    public function run(): void
    {
        $cliente = Cliente::find(1);

        if ($cliente) {
            $cliente->update([
                'nombres' => 'Sin Nombre',
                'ap_paterno' => '',
                'ap_materno' => '',
                'razon_social' => 'S/N',
                'nit' => '99001',
                'cedula' => '99001',
                'complemento' => null,
                'correo' => null,
                'estado' => 'ACTIVO',
            ]);
        } else {
            Cliente::create([
                'id' => 1,
                'nombres' => 'Sin Nombre',
                'ap_paterno' => '',
                'ap_materno' => '',
                'razon_social' => 'S/N',
                'nit' => '99001',
                'cedula' => '99001',
                'complemento' => null,
                'correo' => null,
                'estado' => 'ACTIVO',
            ]);
        }
    }
}
