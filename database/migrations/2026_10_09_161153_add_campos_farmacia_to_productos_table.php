<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->string('codigo_barras', 100)->nullable()->index()->after('codigo');
            $table->string('principio_activo', 255)->nullable()->after('nombre');
            $table->string('presentacion', 100)->nullable()->after('unidad');
            $table->text('accion_terapeutica')->nullable()->after('composicion');
            $table->boolean('requiere_receta')->default(false)->after('estado');
            $table->string('ubicacion_estante', 100)->nullable()->after('minimo_stock');
            $table->unsignedInteger('unidades_por_caja')->default(1)->after('minimo_stock');

            // Homologación con catálogos del SIN (SIAT Facturación)
            $table->string('codigo_producto_sin', 50)->nullable()->after('precio_venta');
            $table->unsignedInteger('unidad_medida_sin')->nullable()->after('codigo_producto_sin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex(['codigo_barras']);
            $table->dropColumn([
                'codigo_barras',
                'principio_activo',
                'laboratorio',
                'presentacion',
                'accion_terapeutica',
                'requiere_receta',
                'ubicacion_estante',
                'unidades_por_caja',
                'codigo_producto_sin',
                'unidad_medida_sin',
            ]);
        });
    }
};
