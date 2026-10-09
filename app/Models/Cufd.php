<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cufd extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'cufds';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'cui_id',
        'punto_venta_id',
        'codigo_ambiente',
        'codigo',
        'codigo_control',
        'direccion',
        'fecha_vigencia',
        'estado',
        'deleted_at'
    ];
}
