<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'proveedor_id',
        'nombre',
        'codigo',
        'gramaje',
        'unidad',
        'composicion',
        'minimo_stock',
        'precio_compra',
        'precio_venta',
        'estado',
        'deleted_at',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class);
    }
}
