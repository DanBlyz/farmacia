<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cuis extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'cuis';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',

        'punto_venta_id',
        'sucursal_id',
        'codigo',
        'fecha_vigencia',
        'codigo_ambiente',

        'estado',
        'deleted_at'
    ];

    public function cuisVigente($sucursal_id, $punto_venta_id, $codigoAmbiente = null){
        $codigoAmbiente = $codigoAmbiente ?: config('siat.codigo_ambiente', env('CODIGO_AMBIENTE', 2));

        return Cuis::where('punto_venta_id', $punto_venta_id)
                    ->where('sucursal_id', $sucursal_id)
                    ->where('codigo_ambiente', $codigoAmbiente)
                    ->orderBy('id', 'desc')
                    ->first();
    }
}
