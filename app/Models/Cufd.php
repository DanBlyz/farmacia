<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cufd extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'cufds';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'cuis_id',
        'sucursal_id',
        'punto_venta_id',
        'codigo_ambiente',
        'codigo',
        'codigo_control',
        'direccion',
        'fecha_vigencia',
        'estado',
        'deleted_at',
    ];
}
