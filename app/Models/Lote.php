<?php

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lote extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'lotes';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'producto_id',
        'sucursal_id',
        'numero_lote',
        'fecha_vencimiento',
        'stock_actual',
        'stock_minimo_alerta',
        'precio_compra',
        'precio_venta',
        'ubicacion_estante',
        'estado',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'stock_actual' => 'decimal:2',
        'stock_minimo_alerta' => 'decimal:2',
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
    ];

    // ==========================================
    // RELACIONES
    // ==========================================

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(Detalle::class, 'lote_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'lote_id');
    }

    public function usuarioCreador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }

    // ==========================================
    // SCOPES DE CONSULTA (FEFO & SEMAFORIZACIÓN)
    // ==========================================

    /**
     * Lotes con fecha de caducidad vigente (no vencidos).
     */
    public function scopeVigentes(Builder $query): Builder
    {
        return $query->where('fecha_vencimiento', '>=', now()->toDateString());
    }

    /**
     * Lotes ya caducados a la fecha actual.
     */
    public function scopeVencidos(Builder $query): Builder
    {
        return $query->where('fecha_vencimiento', '<', now()->toDateString());
    }

    /**
     * Lotes próximos a vencer dentro del rango de días (por defecto 60 días).
     */
    public function scopeProximosAVencer(Builder $query, int $dias = 60): Builder
    {
        return $query->where('fecha_vencimiento', '>=', now()->toDateString())
            ->where('fecha_vencimiento', '<=', now()->addDays($dias)->toDateString());
    }

    /**
     * Lotes que cuentan con existencia física mayor a cero.
     */
    public function scopeConStock(Builder $query): Builder
    {
        return $query->where('stock_actual', '>', 0);
    }

    /**
     * Orden FEFO estricto: Primer vencimiento en salir.
     */
    public function scopeFefo(Builder $query): Builder
    {
        return $query->orderBy('fecha_vencimiento', 'asc');
    }

    /**
     * Filtro por sucursal específica.
     */
    public function scopePorSucursal(Builder $query, int $sucursalId): Builder
    {
        return $query->where('sucursal_id', $sucursalId);
    }

    // ==========================================
    // MÉTODOS DE DOMINIO Y ATRIBUTOS CALCULADOS
    // ==========================================

    /**
     * Determina si el lote ya expiró (comparando contra el final del día de caducidad).
     */
    public function esVencido(): bool
    {
        return Carbon::parse($this->fecha_vencimiento)->endOfDay()->isPast();
    }

    /**
     * Determina si el lote está a punto de caducar (dentro de los próximos X días).
     */
    public function esProximoAVencer(int $dias = 60): bool
    {
        if ($this->esVencido()) {
            return false;
        }

        $vencimiento = Carbon::parse($this->fecha_vencimiento)->startOfDay();
        $limite = now()->addDays($dias)->endOfDay();

        return $vencimiento->lessThanOrEqualTo($limite);
    }

    /**
     * Alias de compatibilidad semántica para esProximoAVencer.
     */
    public function estaProximoAVencer(int $dias = 60): bool
    {
        return $this->esProximoAVencer($dias);
    }

    /**
     * Semaforización farmacéutica: 'rojo', 'naranja', 'verde'.
     */
    public function getEstadoSemaforoAttribute(): string
    {
        if ($this->esVencido()) {
            return 'rojo';
        }

        if ($this->esProximoAVencer(60)) {
            return 'naranja';
        }

        return 'verde';
    }

    /**
     * Descuenta stock del lote validando existencia y vencimiento.
     */
    public function descontarStock(float $cantidad): void
    {
        if ($this->esVencido()) {
            throw new \RuntimeException("No se puede dispensar el lote {$this->numero_lote} porque está caducado.");
        }

        if ((float) $this->stock_actual < $cantidad) {
            throw new \RuntimeException("Stock insuficiente en el lote {$this->numero_lote}. Disponible: {$this->stock_actual}, Solicitado: {$cantidad}");
        }

        $nuevoStock = (float) $this->stock_actual - $cantidad;
        $this->stock_actual = $nuevoStock;

        if ($nuevoStock <= 0) {
            $this->estado = 'AGOTADO';
        }

        $this->save();
    }

    /**
     * Incrementa stock al lote (por compra, ajuste positivo o devolución).
     */
    public function incrementarStock(float $cantidad): void
    {
        $this->stock_actual = (float) $this->stock_actual + $cantidad;

        if ($this->estado === 'AGOTADO' && $this->stock_actual > 0) {
            $this->estado = 'ACTIVO';
        }

        $this->save();
    }
}
