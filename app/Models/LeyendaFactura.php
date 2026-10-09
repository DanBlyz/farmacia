<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeyendaFactura extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'leyendas_facturas';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'codigo_ambiente',
        'codigo_actividad',
        'descripcion_leyenda',
        'estado',
        'deleted_at',
    ];
}
