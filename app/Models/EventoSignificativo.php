<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventoSignificativo extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'evento_significativos';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'tipo_evento_significativo_id',
        'punto_venta_id',
        'cufd_activo_id',
        'cufd_evento_id',
        'cui_id',
        'descripcion',
        'fecha_ini',
        'fecha_fin',
        'codigo_recepcion',
        'estado',
        'deleted_at'
    ];

}
