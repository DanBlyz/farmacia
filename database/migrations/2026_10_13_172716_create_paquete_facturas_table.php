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
        Schema::create('paquete_facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_significativo_id')->nullable()->constrained('evento_significativos')->nullOnDelete();
            $table->foreignId('punto_venta_id')->nullable()->constrained('punto_ventas')->nullOnDelete();
            $table->foreignId('cufd_id')->nullable()->constrained('cufds')->nullOnDelete();
            $table->foreignId('usuario_creador_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('codigo_recepcion')->nullable()->index();
            $table->integer('codigo_estado')->nullable();
            $table->string('estado')->default('PENDIENTE'); // PENDIENTE, VALIDADA, OBSERVADA, RECHAZADA
            $table->integer('cantidad_facturas')->default(1);
            $table->string('hash_archivo')->nullable();
            $table->string('nombre_archivo')->nullable();
            $table->json('mensajes_validacion')->nullable();
            $table->dateTime('fecha_envio')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('facturas', function (Blueprint $table) {
            if (! Schema::hasColumn('facturas', 'evento_significativo_id')) {
                $table->foreignId('evento_significativo_id')->nullable()->after('punto_venta_id')->constrained('evento_significativos')->nullOnDelete();
            }
            if (! Schema::hasColumn('facturas', 'paquete_id')) {
                $table->foreignId('paquete_id')->nullable()->after('punto_venta_id')->constrained('paquete_facturas')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            if (Schema::hasColumn('facturas', 'paquete_id')) {
                $table->dropForeign(['paquete_id']);
                $table->dropColumn('paquete_id');
            }
            if (Schema::hasColumn('facturas', 'evento_significativo_id')) {
                $table->dropForeign(['evento_significativo_id']);
                $table->dropColumn('evento_significativo_id');
            }
        });

        Schema::dropIfExists('paquete_facturas');
    }
};
