<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductoServicio extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'producto_servicios';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'codigo_ambiente',
        'codigo_actividad',
        'codigo_producto',
        'descripcion_producto',
        'nandina_pri',
        'nandina_seg',
        'estado',
        'deleted_at'
    ];

    public function puntoVenta(){
        return $this->belongsTo('App\Models\PuntoVenta', 'punto_venta_id');
    }

    public function sucursal(){
        return $this->belongsTo('App\Models\Sucursal', 'sucursal_id');
    }
}
