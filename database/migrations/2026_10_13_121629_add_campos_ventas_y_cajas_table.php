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
        Schema::table('cajas', function (Blueprint $table) {
            if (! Schema::hasColumn('cajas', 'sucursal_id')) {
                $table->foreignId('sucursal_id')->nullable()->after('usuario_id')->constrained('sucursales')->nullOnDelete();
            }
        });

        Schema::table('facturas', function (Blueprint $table) {
            if (! Schema::hasColumn('facturas', 'caja_id')) {
                $table->foreignId('caja_id')->nullable()->after('punto_venta_id')->constrained('cajas')->nullOnDelete();
            }
            if (! Schema::hasColumn('facturas', 'codigo_metodo_pago')) {
                $table->unsignedInteger('codigo_metodo_pago')->default(1)->after('facturado');
            }
            if (! Schema::hasColumn('facturas', 'fecha_anulacion')) {
                $table->dateTime('fecha_anulacion')->nullable()->after('estado');
            }
            if (! Schema::hasColumn('facturas', 'motivo_anulacion_descripcion')) {
                $table->string('motivo_anulacion_descripcion')->nullable()->after('fecha_anulacion');
            }
        });

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
        Schema::table('cajas', function (Blueprint $table) {
            if (Schema::hasColumn('cajas', 'sucursal_id')) {
                $table->dropForeign(['sucursal_id']);
                $table->dropColumn('sucursal_id');
            }
        });

        Schema::table('facturas', function (Blueprint $table) {
            if (Schema::hasColumn('facturas', 'caja_id')) {
                $table->dropForeign(['caja_id']);
                $table->dropColumn('caja_id');
            }
            if (Schema::hasColumn('facturas', 'codigo_metodo_pago')) {
                $table->dropColumn('codigo_metodo_pago');
            }
            if (Schema::hasColumn('facturas', 'fecha_anulacion')) {
                $table->dropColumn('fecha_anulacion');
            }
            if (Schema::hasColumn('facturas', 'motivo_anulacion_descripcion')) {
                $table->dropColumn('motivo_anulacion_descripcion');
            }
        });

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
