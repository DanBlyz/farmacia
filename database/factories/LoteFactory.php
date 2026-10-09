<?php

namespace Database\Factories;

use App\Models\Lote;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lote>
 */
class LoteFactory extends Factory
{
    protected $model = Lote::class;

    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'sucursal_id' => Sucursal::factory(),
            'numero_lote' => 'LT-'.fake()->bothify('##??-####'),
            'fecha_vencimiento' => now()->addMonths(fake()->numberBetween(6, 24))->toDateString(),
            'stock_actual' => fake()->randomFloat(2, 20, 100),
            'stock_minimo_alerta' => 5,
            'precio_compra' => fake()->randomFloat(2, 5, 50),
            'precio_venta' => fake()->randomFloat(2, 10, 80),
            'ubicacion_estante' => 'Gaveta '.fake()->numberBetween(1, 10),
            'estado' => 'ACTIVO',
        ];
    }

    /**
     * Estado para lote próximo a caducar (< 60 días, alerta ámbar/naranja).
     */
    public function proximoAVencer(int $dias = 30): static
    {
        return $this->state(fn () => [
            'fecha_vencimiento' => now()->addDays($dias)->toDateString(),
            'estado' => 'ACTIVO',
        ]);
    }

    /**
     * Estado para lote caducado / vencido (alerta roja, bloqueado).
     */
    public function vencido(int $diasAtras = 10): static
    {
        return $this->state(fn () => [
            'fecha_vencimiento' => now()->subDays($diasAtras)->toDateString(),
            'estado' => 'VENCIDO',
        ]);
    }

    /**
     * Estado para lote sin existencias.
     */
    public function agotado(): static
    {
        return $this->state(fn () => [
            'stock_actual' => 0,
            'estado' => 'AGOTADO',
        ]);
    }
}
