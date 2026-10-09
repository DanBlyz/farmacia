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
        'total',
        'total',
        'monto_total_subjeto_iva',
        'descuento_adicional',
        'cuf',
        'producto_xml',
        'codigo_descripcion',
        'codigo_recepcion',
        'codigo_transaccion',
        'descripcion',
        'estado_pago',
        'tipo_factura',
        'uso_cafc',
        'monto_gift_card',
        'estado',
        'deleted_at',
    ];

    public function usuarioCreador()
    {
        return $this->belongsTo('App\Models\User', 'usuario_creador_id');
    }

    public function sucursal()
    {
        return $this->belongsTo('App\Models\Sucursal', 'sucursal_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalles()
    {
        return $this->hasMany(Detalle::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }
}
