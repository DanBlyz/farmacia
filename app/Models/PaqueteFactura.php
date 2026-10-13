<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaqueteFactura extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'paquete_facturas';

    protected $fillable = [
        'evento_significativo_id',
        'punto_venta_id',
        'cufd_id',
        'usuario_creador_id',
        'codigo_recepcion',
        'codigo_estado',
        'estado',
        'cantidad_facturas',
        'hash_archivo',
        'nombre_archivo',
        'mensajes_validacion',
        'fecha_envio',
    ];

    protected $casts = [
        'mensajes_validacion' => 'array',
        'fecha_envio' => 'datetime',
    ];

    public function eventoSignificativo(): BelongsTo
    {
        return $this->belongsTo(EventoSignificativo::class, 'evento_significativo_id');
    }

    public function puntoVenta(): BelongsTo
    {
        return $this->belongsTo(PuntoVenta::class, 'punto_venta_id');
    }

    public function cufd(): BelongsTo
    {
        return $this->belongsTo(Cufd::class, 'cufd_id');
    }

    public function usuarioCreador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'paquete_id');
    }
}
