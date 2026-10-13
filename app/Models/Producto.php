<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'codigo_barras',
        'principio_activo',
        'gramaje',
        'unidad',
        'presentacion',
        'composicion',
        'accion_terapeutica',
        'minimo_stock',
        'unidades_por_caja',
        'ubicacion_estante',
        'precio_compra',
        'precio_venta',
        'codigo_producto_sin',
        'unidad_medida_sin',
        'requiere_receta',
        'estado',
        'deleted_at',
    ];

    protected $casts = [
        'requiere_receta' => 'boolean',
        'unidades_por_caja' => 'integer',
        'minimo_stock' => 'decimal:2',
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
    ];

    // ==========================================
    // RELACIONES
    // ==========================================

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'producto_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'producto_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(Detalle::class, 'producto_id');
    }

    // ==========================================
    // MÉTODOS DE NEGOCIO Y DOMINIO FARMACÉUTICO
    // ==========================================

    /**
     * Obtiene el stock físico vigente (no vencido) disponible en una sucursal específica.
     */
    public function stockEnSucursal(int $sucursalId): float
    {
        return (float) $this->lotes()
            ->where('sucursal_id', $sucursalId)
            ->where('estado', 'ACTIVO')
            ->vigentes()
            ->sum('stock_actual');
    }

    /**
     * Obtiene el stock físico global sumando todas las sucursales (solo lotes vigentes).
     */
    public function stockTotal(): float
    {
        return (float) $this->lotes()
            ->where('estado', 'ACTIVO')
            ->vigentes()
            ->sum('stock_actual');
    }

    /**
     * Retorna el lote candidato a dispensar bajo el método FEFO (First Expired, First Out)
     * para una sucursal específica. Solo lotes vigentes con stock > 0.
     */
    public function loteFefoDisponible(int $sucursalId): ?Lote
    {
        return $this->lotes()
            ->where('sucursal_id', $sucursalId)
            ->where('estado', 'ACTIVO')
            ->vigentes()
            ->conStock()
            ->fefo()
            ->first();
    }
}
