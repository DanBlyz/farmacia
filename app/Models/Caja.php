<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Caja extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'cajas';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'usuario_id',
        'sucursal_id',
        'punto_venta_id',
        'fecha_apertura',
        'fecha_cierre',
        'monto_apertura',
        'monto_cierre',
        'descripcion',
        'descripcion_cierre',
        'total_venta',
        'venta_contado',
        'venta_credito',
        'otro_ingreso',
        'total_ingreso',
        'total_qr',
        'total_transferencia',
        'total_salida',
        'saldo',
        'estado',
        'deleted_at',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'monto_apertura' => 'decimal:2',
        'monto_cierre' => 'decimal:2',
        'total_venta' => 'decimal:2',
        'venta_contado' => 'decimal:2',
        'venta_credito' => 'decimal:2',
        'otro_ingreso' => 'decimal:2',
        'total_ingreso' => 'decimal:2',
        'total_qr' => 'decimal:2',
        'total_transferencia' => 'decimal:2',
        'total_salida' => 'decimal:2',
        'saldo' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function puntoVenta()
    {
        return $this->belongsTo(PuntoVenta::class, 'punto_venta_id');
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class, 'caja_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'caja_id');
    }

    public static function obtenerCajaAbierta(int $usuarioId): ?self
    {
        return static::where('usuario_id', $usuarioId)
            ->whereIn('estado', ['ABIERTA', 'Abierta'])
            ->latest('fecha_apertura')
            ->first();
    }
}
