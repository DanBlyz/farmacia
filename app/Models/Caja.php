<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Caja extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'cajas';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'usuario_id',
        'fecha_apertura',
        'fecha_cierre',
        'monto_apertura',
        'monto_cierre',
        'descripcion',
        'total_venta',
        'venta_contado',
        'venta_credito',
        'otro_ingreso',
        'total_ingreso',
        'total_qr_transferencia',
        'total_salida',
        'saldo',
        'estado',
        'deleted_at'
    ];

    public function usuario(){
        return $this->belongsTo('App\Models\User', 'usuario_id');
    }

    public function sacaCajaVigente($usuario_id){
        return Caja::where('usuario_id',$usuario_id)
                    ->where('estado', 'Abierta')
                    ->first();
    }

    public function sacarCajasRangoFechas($fechaIni, $fechafin){

        return Caja::where('created_at','>=',$fechaIni.' 00:00:00')
                    ->where('created_at','<=',$fechafin.' 00:00:00')
                    ->get();
    }
}
