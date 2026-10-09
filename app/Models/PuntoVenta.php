<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PuntoVenta extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'punto_ventas';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'sucursal_id',
        'codigo',
        'nombre',
        'tipo',
        'codigo_ambiente',
        'estado',
        'deleted_at'
    ];

    public function sucursal(){
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}
