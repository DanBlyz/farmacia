<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Factura extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'facturas';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'cufd_id',
        'sucursal_id',
        'caja_id',
        'motivo_anulacion_id',
        'cliente_id',
        'punto_venta_id',
        'fecha',
        'nit',
        'razon_social',
        'numero_factura',
        'numero_recibo',
        'numero_cafc',
        'facturado',
        'codigo_metodo_pago',
        'total',
        'monto_total_subjeto_iva',
        'descuento_adicional',
        'cuf',
        'productos_xml',
        'codigo_descripcion',
        'codigo_recepcion',
        'codigo_transaccion',
        'descripcion',
        'estado_pago',
        'tipo_factura',
        'uso_cafc',
        'monto_gift_card',
        'estado',
        'fecha_anulacion',
        'motivo_anulacion_descripcion',
        'deleted_at',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'fecha_anulacion' => 'datetime',
        'total' => 'decimal:2',
        'monto_total_subjeto_iva' => 'decimal:2',
        'descuento_adicional' => 'decimal:2',
        'monto_gift_card' => 'decimal:2',
    ];

    public function usuarioCreador()
    {
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function puntoVenta()
    {
        return $this->belongsTo(PuntoVenta::class, 'punto_venta_id');
    }

    public function cufd()
    {
        return $this->belongsTo(Cufd::class, 'cufd_id');
    }

    public function motivoAnulacion()
    {
        return $this->belongsTo(MotivoAnulacion::class, 'motivo_anulacion_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalles()
    {
        return $this->hasMany(Detalle::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'factura_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }
}
