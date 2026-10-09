<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DependeActividad extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'depende_actividades';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'nit_id',
        'codigo_ambiente',
        'codigo_caeb',
        'descripcion',
        'tipo_actividad',
        'estado',
        'deleted_at'
    ];

}
