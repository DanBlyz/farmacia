<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use Auditable, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'rol_id',
        'punto_venta_id',
        'nombres',
        'ap_paterno',
        'ap_materno',
        'cedula',
        'direccion',
        'celular',
        'estado',
        'deleted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getActivoAttribute(): bool
    {
        return $this->estado === 'ACTIVO' || $this->estado === null;
    }

    public function puntoVenta()
    {
        return $this->belongsTo(PuntoVenta::class, 'punto_venta_id');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    /**
     * Permisos específicos asignados al usuario.
     */
    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'permiso_usuario', 'user_id', 'permiso_id')
            ->withTimestamps();
    }

    /**
     * Determina si el usuario tiene el rol de Administrador (Superusuario).
     */
    public function esAdmin(): bool
    {
        $rolNombre = strtoupper((string) ($this->rol?->nombre ?? ''));

        return in_array($rolNombre, ['ADMIN', 'ADMINISTRADOR', 'SUPERADMIN'], true);
    }

    /**
     * Comprueba si el usuario tiene un permiso específico por ID numérico o slug/nombre.
     */
    public function tienePermiso(int|string $permiso): bool
    {
        if ($this->esAdmin()) {
            return true;
        }

        if (is_numeric($permiso)) {
            return $this->permisos()->where('permisos.id', (int) $permiso)->exists();
        }

        if ($this->permisos()->where('permisos.nombre', $permiso)->exists()) {
            return true;
        }

        // Retrocompatibilidad con permisos generales/módulos base (IDs 1 al 10)
        $legacyPrefixMap = [
            'usuarios.' => [1, 'gestion-usuarios'],
            'sucursales.' => [2, 'gestion-sucursales'],
            'roles.' => [3, 'gestion-roles'],
            'catalogos.' => [4, 'gestion-catalogos'],
            'pacientes.' => [5, 'gestion-pacientes'],
            'instituciones.' => [5, 'gestion-pacientes'],
            'farmacia.' => [9, 'despachar-farmacia'],
            'caja.' => [10, 'cobro-caja'],
        ];

        foreach ($legacyPrefixMap as $prefix => $legacyIdentifiers) {
            if (str_starts_with($permiso, $prefix)) {
                return $this->permisos()->where(function ($q) use ($legacyIdentifiers) {
                    $q->whereIn('permisos.id', array_filter($legacyIdentifiers, 'is_int'))
                        ->orWhereIn('permisos.nombre', array_filter($legacyIdentifiers, 'is_string'));
                })->exists();
            }
        }

        return false;
    }

    /**
     * Comprueba si el usuario tiene al menos uno de los permisos dados.
     *
     * @param  array<int|string>  $permisos
     */
    public function tieneAlgunPermiso(array $permisos): bool
    {
        if ($this->esAdmin()) {
            return true;
        }

        foreach ($permisos as $permiso) {
            if ($this->tienePermiso($permiso)) {
                return true;
            }
        }

        return false;
    }
}
