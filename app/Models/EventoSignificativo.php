<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventoSignificativo extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'evento_significativos';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'tipo_evento_significativo_id',
        'punto_venta_id',
        'cufd_activo_id',
        'cufd_evento_id',
        'cui_id',
        'descripcion',
        'fecha_ini',
        'fecha_fin',
        'codigo_recepcion',
        'estado',
        'deleted_at',
    ];

    public function tipoEvento()
    {
        return $this->belongsTo(TipoEventoSignificativo::class, 'tipo_evento_significativo_id');
    }

    public function puntoVenta()
    {
        return $this->belongsTo(PuntoVenta::class, 'punto_venta_id');
    }

    public function cufdActivo()
    {
        return $this->belongsTo(Cufd::class, 'cufd_activo_id');
    }

    public function cufdEvento()
    {
        return $this->belongsTo(Cufd::class, 'cufd_evento_id');
    }

    public function cui()
    {
        return $this->belongsTo(Cuis::class, 'cui_id');
    }

    public function usuarioCreador()
    {
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }

    public function paquetes()
    {
        return $this->hasMany(PaqueteFactura::class, 'evento_significativo_id');
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class, 'evento_significativo_id');
    }
}
