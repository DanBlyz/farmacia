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
            if (! Schema::hasColumn('detalles', 'lote_id')) {
                $table->foreignId('lote_id')->nullable()->after('producto_id')->constrained('lotes')->nullOnDelete();
            }
            if (! Schema::hasColumn('detalles', 'numero_lote')) {
                $table->string('numero_lote')->nullable()->after('nombre_producto');
            }
            if (! Schema::hasColumn('detalles', 'fecha_vencimiento')) {
                $table->date('fecha_vencimiento')->nullable()->after('numero_lote');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalles', function (Blueprint $table) {
            if (Schema::hasColumn('detalles', 'lote_id')) {
                $table->dropForeign(['lote_id']);
                $table->dropColumn('lote_id');
            }
            if (Schema::hasColumn('detalles', 'numero_lote')) {
                $table->dropColumn('numero_lote');
            }
            if (Schema::hasColumn('detalles', 'fecha_vencimiento')) {
                $table->dropColumn('fecha_vencimiento');
            }
        });
    }
};
