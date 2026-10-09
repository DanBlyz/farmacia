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
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();

            // Relaciones principales
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();

            // Datos del lote y vencimiento (FEFO)
            $table->string('numero_lote', 100);
            $table->date('fecha_vencimiento');
            $table->decimal('stock_actual', 12, 2)->default(0);
            $table->decimal('stock_minimo_alerta', 12, 2)->default(0);
            $table->decimal('precio_compra', 12, 2)->nullable();
            $table->decimal('precio_venta', 12, 2)->nullable();
            $table->string('ubicacion_estante', 100)->nullable();
            $table->string('estado', 50)->default('ACTIVO'); // ACTIVO, AGOTADO, VENCIDO, BLOQUEADO, CUARENTENA

            // Auditoría y trazabilidad
            $table->foreignId('usuario_creador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('usuario_modificador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('usuario_eliminador_id')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            // Índices optimizados para despacho FEFO y auditorías sanitarias
            $table->index(['producto_id', 'sucursal_id', 'fecha_vencimiento'], 'idx_lotes_fefo_sucursal');
            $table->index('numero_lote');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
