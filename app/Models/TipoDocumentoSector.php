<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoDocumentoSector extends Model
{
    use SoftDeletes;
    protected $table = 'tipo_documento_sectores';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_creador_id',
        'usuario_eliminador_id',
        'codigo_clasificador',
        'descripcion',
        'estado',
        'deleted_at'
    ];
}
