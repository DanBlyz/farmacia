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
        Schema::table('movimientos', function (Blueprint $table) {
            $table->foreignId('lote_id')->nullable()->after('producto_id')->constrained('lotes')->nullOnDelete();
            $table->string('numero_lote', 100)->nullable()->after('fecha_caducidad');
            $table->decimal('saldo_stock', 12, 2)->nullable()->after('salida');
            $table->string('tipo_movimiento', 50)->nullable()->after('descripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropForeign(['lote_id']);
            $table->dropColumn(['lote_id', 'numero_lote', 'saldo_stock', 'tipo_movimiento']);
        });
    }
};
