<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Detalle extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'detalles';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'factura_id',
        'producto_id',
        'lote_id',
        'punto_venta_id',
        'sucursal_id',
        'nombre_producto',
        'numero_lote',
        'fecha_vencimiento',
        'descripcion_adicional',
        'precio',
        'cantidad',
        'descuento',
        'total',
        'importe',
        'fecha',
        'estado',
        'deleted_at',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'fecha' => 'datetime',
        'precio' => 'decimal:2',
        'cantidad' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'importe' => 'decimal:2',
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function puntoVenta(): BelongsTo
    {
        return $this->belongsTo(PuntoVenta::class, 'punto_venta_id');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }
}
