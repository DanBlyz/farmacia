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
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreign('usuario_creador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_creador_id')->nullable();
            $table->foreign('usuario_modificador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_modificador_id')->nullable();
            $table->foreign('usuario_eliminador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_eliminador_id')->nullable();

            $table->foreign('usuario_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->foreign('punto_venta_id')->references('id')->on('punto_ventas');
            $table->unsignedBigInteger('punto_venta_id')->nullable();

            $table->dateTime('fecha_apertura')->nullable();
            $table->dateTime('fecha_cierre')->nullable();
            $table->decimal('monto_apertura', 12, 2)->nullable();
            $table->decimal('monto_cierre', 12, 2)->nullable();
            $table->string('descripcion')->nullable();
            $table->string('descripcion_cierre')->nullable();
            $table->decimal('total_venta', 12, 2)->nullable();
            $table->decimal('venta_contado', 12, 2)->nullable();
            $table->decimal('venta_credito', 12, 2)->nullable();
            $table->decimal('otro_ingreso', 12, 2)->nullable();
            $table->decimal('total_ingreso', 12, 2)->nullable();
            $table->decimal('total_qr', 12, 2)->nullable();
            $table->decimal('total_transferencia', 12, 2)->nullable();
            $table->decimal('total_salida', 12, 2)->nullable();
            $table->decimal('saldo', 12, 2)->nullable();

            $table->string('estado')->nullable();
            $table->datetime('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
