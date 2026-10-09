<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventoSignificativo extends Model
{
    use Auditable, HasFactory, SoftDeletes;

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
        'deleted_at',
    ];
}
