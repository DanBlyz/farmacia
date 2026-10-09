<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoCambio extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'tipo_cambios';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'tipo_cambio_boliviano',
        'estado',
        'deleted_at',
    ];
}
