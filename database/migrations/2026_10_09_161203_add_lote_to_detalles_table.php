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
        Schema::table('detalles', function (Blueprint $table) {
            $table->foreignId('lote_id')->nullable()->after('producto_id')->constrained('lotes')->nullOnDelete();
            $table->string('numero_lote', 100)->nullable()->after('nombre_producto');
            $table->date('fecha_vencimiento')->nullable()->after('numero_lote');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalles', function (Blueprint $table) {
            $table->dropForeign(['lote_id']);
            $table->dropColumn(['lote_id', 'numero_lote', 'fecha_vencimiento']);
        });
    }
};
