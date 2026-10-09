<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UrlApiServicio extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'url_api_servicios';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'ambiente',
        'modalidad',
        'nombre',
        'url_servicio',
        'estado',
        'deleted_at',
    ];

    public function getUrlCodigos($ambiente, $modalidad)
    {
        return $this->where('ambiente', $ambiente)
            ->where('modalidad', $modalidad)
            ->where('nombre', 'url_facturacionCodigos')
            ->first();
    }

    public function getUrlSincronizacion($ambiente, $modalidad)
    {
        return $this->where('ambiente', $ambiente)
            ->where('modalidad', $modalidad)
            ->where('nombre', 'url_facturacionSincronizacion')
            ->first();
    }

    public function getUrlFacturacionCompraVentaElectronica($ambiente, $modalidad)
    {
        return $this->where('ambiente', $ambiente)
            ->where('modalidad', $modalidad)
            ->where('nombre', 'url_servicio_facturacion_compra_venta')
            ->first();
    }

    public function getUrlVerificaFactura($ambiente)
    {
        return $this->where('ambiente', $ambiente)
            ->where('nombre', 'url_verificacion_factura')
            ->first();
    }

    public function getUrlOperaciones($ambiente, $modalidad)
    {
        return $this->where('ambiente', $ambiente)
            ->where('modalidad', $modalidad)
            ->where('nombre', 'url_facturacion_operaciones')
            ->first();
    }
}
