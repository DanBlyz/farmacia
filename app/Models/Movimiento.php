<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movimiento extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'movimientos';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'producto_id',
        'lote_id',
        'detalle_id',
        'sucursal_id',
        'numero_lote',
        'tipo_movimiento',
        'precio_compra',
        'precio_venta',
        'fecha_caducidad',
        'ingreso',
        'salida',
        'saldo_stock',
        'fecha',
        'descripcion',
        'estado',
        'deleted_at',
    ];

    protected $casts = [
        'fecha_caducidad' => 'date',
        'fecha' => 'datetime',
        'ingreso' => 'decimal:2',
        'salida' => 'decimal:2',
        'saldo_stock' => 'decimal:2',
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function usuarioCreador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }

    public function detalle(): BelongsTo
    {
        return $this->belongsTo(Detalle::class);
    }

    public function cantidaDisponileACiertaFecha($sucursal_id, $servicio_id, $fecha_ini, $fecha_fin)
    {
        $ingresos = Movimiento::where('sucursal_id', $sucursal_id)
            ->where('producto_id', $servicio_id)
            ->where('ingreso', '>', 0)
            ->where('fecha', '<=', $fecha_ini)
            ->sum('ingreso');

        $salidas = Movimiento::where('sucursal_id', $sucursal_id)
            ->where('producto_id', $servicio_id)
            ->where('salida', '>', 0)
            ->where('fecha', '<=', $fecha_fin)
            ->sum('salida');

        return $ingresos - $salidas;
    }

    public function cantidaDisponile($sucursal_id, $servicio_id)
    {
        $ingresos = Movimiento::where('sucursal_id', $sucursal_id)
            ->where('producto_id', $servicio_id)
            ->where('ingreso', '>', 0)
            ->sum('ingreso');

        $salidas = Movimiento::where('sucursal_id', $sucursal_id)
            ->where('producto_id', $servicio_id)
            ->where('salida', '>', 0)
            ->sum('salida');

        return $ingresos - $salidas;
    }
}
