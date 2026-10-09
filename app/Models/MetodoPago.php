<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetodoPago extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'metodo_pagos';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'tipo_clasificador',
        'descripcion',
        'estado',
        'deleted_at',
    ];
}
