<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        $medicamentos = [
            ['nombre' => 'Paracetamol 500mg', 'principio_activo' => 'Paracetamol', 'gramaje' => '500 mg', 'presentacion' => 'Comprimidos'],
            ['nombre' => 'Amoxicilina 500mg', 'principio_activo' => 'Amoxicilina Trihidrato', 'gramaje' => '500 mg', 'presentacion' => 'Cápsulas'],
            ['nombre' => 'Ibuprofeno 400mg', 'principio_activo' => 'Ibuprofeno', 'gramaje' => '400 mg', 'presentacion' => 'Tabletas'],
            ['nombre' => 'Omeprazol 20mg', 'principio_activo' => 'Omeprazol', 'gramaje' => '20 mg', 'presentacion' => 'Cápsulas'],
            ['nombre' => 'Loratadina 10mg', 'principio_activo' => 'Loratadina', 'gramaje' => '10 mg', 'presentacion' => 'Comprimidos'],
            ['nombre' => 'Azitromicina 500mg', 'principio_activo' => 'Azitromicina', 'gramaje' => '500 mg', 'presentacion' => 'Comprimidos'],
            ['nombre' => 'Diclofenaco 50mg', 'principio_activo' => 'Diclofenaco Sódico', 'gramaje' => '50 mg', 'presentacion' => 'Grageas'],
            ['nombre' => 'Complejo B Forte', 'principio_activo' => 'Vitaminas B1, B6, B12', 'gramaje' => 'Forte', 'presentacion' => 'Inyectable'],
        ];

        $med = fake()->randomElement($medicamentos);
        $laboratorios = ['Laboratorios Bagó', 'Droguería INTI', 'Laboratorios VITA', 'Laboratorios Terbol', 'Laboratorios Cofar'];

        return [
            'proveedor_id' => Proveedor::factory(),
            'nombre' => $med['nombre'],
            'principio_activo' => $med['principio_activo'],
            'codigo' => 'MED-'.fake()->unique()->numerify('####'),
            'codigo_barras' => fake()->unique()->numerify('775##########'),
            'laboratorio' => fake()->randomElement($laboratorios),
            'gramaje' => $med['gramaje'],
            'unidad' => 'Caja',
            'presentacion' => $med['presentacion'],
            'composicion' => $med['principio_activo'].' excipientes c.s.p.',
            'accion_terapeutica' => 'Tratamiento sintomático según prescripción.',
            'minimo_stock' => 10,
            'unidades_por_caja' => 20,
            'ubicacion_estante' => 'Estante '.fake()->randomElement(['A', 'B', 'C']).'-'.fake()->numberBetween(1, 10),
            'precio_compra' => fake()->randomFloat(2, 5, 50),
            'precio_venta' => fake()->randomFloat(2, 10, 80),
            'codigo_producto_sin' => '83141',
            'unidad_medida_sin' => 57,
            'requiere_receta' => false,
            'estado' => 'ACTIVO',
        ];
    }
}
