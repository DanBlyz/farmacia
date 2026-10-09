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
        Schema::create('evento_significativos', function (Blueprint $table) {
            $table->id();
            $table->foreign('usuario_creador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_creador_id')->nullable();
            $table->foreign('usuario_modificador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_modificador_id')->nullable();
            $table->foreign('usuario_eliminador_id')->references('id')->on('users');
            $table->unsignedBigInteger('usuario_eliminador_id')->nullable();

            $table->foreign('tipo_evento_significativo_id')->references('id')->on('tipo_evento_significativos');
            $table->unsignedBigInteger('tipo_evento_significativo_id')->nullable();
            $table->foreign('punto_venta_id')->references('id')->on('punto_ventas');
            $table->unsignedBigInteger('punto_venta_id')->nullable();
            $table->foreign('cufd_activo_id')->references('id')->on('cufds');
            $table->unsignedBigInteger('cufd_activo_id')->nullable();
            $table->foreign('cufd_evento_id')->references('id')->on('cufds');
            $table->unsignedBigInteger('cufd_evento_id')->nullable();
            $table->foreign('cui_id')->references('id')->on('cuis');
            $table->unsignedBigInteger('cui_id')->nullable();

            $table->text('descripcion')->nullable();
            $table->dateTime('fecha_ini')->nullable();
            $table->dateTime('fecha_fin')->nullable();
            $table->string('codigo_recepcion')->nullable();

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
        Schema::dropIfExists('evento_significativos');
    }
};
