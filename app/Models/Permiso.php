<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permiso extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'permisos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'permiso_usuario', 'permiso_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Módulo al que pertenece el permiso según su slug 'modulo.apartado.accion'.
     */
    public function getModuloAttribute(): string
    {
        $partes = explode('.', (string) $this->nombre);
        $slug = $partes[0] ?? '';

        return match ($slug) {
            'administracion' => 'Administración y Personal',
            'sincronizacion' => 'Sincronización SIAT',
            'inventario' => 'Inventario Farmacéutico',
            'cajas' => 'Control de Cajas',
            'ventas' => 'Ventas y Facturación',
            default => ucfirst($slug ?: 'General'),
        };
    }

    /**
     * Apartado o sección del módulo al que pertenece el permiso.
     */
    public function getApartadoAttribute(): string
    {
        $partes = explode('.', (string) $this->nombre);
        $slug = $partes[1] ?? '';

        return match ($slug) {
            'sucursales' => 'Sucursales',
            'usuarios' => 'Usuarios',
            'roles' => 'Roles',
            'clientes' => 'Clientes',
            'proveedores' => 'Proveedores y Laboratorios',
            'catalogos' => 'Catálogos SIN',
            'eventos' => 'Eventos Significativos',
            'paquetes' => 'Paquetes de Contingencia',
            'productos' => 'Medicamentos y Productos',
            'lotes' => 'Lotes y Vencimientos (FEFO)',
            'kardex' => 'Kardex de Movimientos',
            'control' => 'Control de Cajas y Turnos',
            'pos' => 'Terminal POS y Dispensación',
            'facturas' => 'Facturación e Historial',
            default => ucfirst($slug ?: 'General'),
        };
    }
}
