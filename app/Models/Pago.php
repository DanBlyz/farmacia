<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pago extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'pagos';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'factura_id',
        'punto_venta_id',
        'caja_id',
        'tipo_pago',
        'monto',
        'cambio',
        'fecha',
        'descripcion',
        'apertura_caja',
        'estado',
        'deleted_at',
    ];

    public function factura()
    {
        return $this->belongsTo('App\Models\Factura', 'factura_id');
    }

    public function puntoVenta()
    {
        return $this->belongsTo('App\Models\PuntoVenta', 'punto_venta_id');
    }

    public function caja()
    {
        return $this->belongsTo('App\Models\Caja', 'caja_id');
    }

    public function usuario()
    {
        return $this->belongsTo('App\Models\User', 'usuario_creador_id');
    }

    public static function pagosEfectuados($sucursal_id, $fechaIni, $fechaFin)
    {
        return static::select('pagos.*')
            ->join('punto_ventas', 'punto_ventas.id', '=', 'pagos.punto_venta_id')
            ->join('sucursales', 'sucursales.id', '=', 'punto_ventas.sucursal_id')
            ->where('sucursales.id', $sucursal_id)
            ->whereBetween('pagos.fecha', [$fechaIni, $fechaFin])
            ->get();
    }
}
