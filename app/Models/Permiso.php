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
     * Módulo al que pertenece el permiso según su ID y prefijo.
     */
    public function getModuloAttribute(): string
    {
        $id = (int) $this->id;

        if (in_array($id, [1, 2, 3, 4], true) || ($id >= 11 && $id <= 21)) {
            return 'Administración y Sistema';
        }

        if ($id === 5 || ($id >= 22 && $id <= 26)) {
            return 'Pacientes e Instituciones';
        }

        if (in_array($id, [6, 7, 8], true) || ($id >= 27 && $id <= 46)) {
            return 'Proformas Clínicas y Admisión';
        }

        if ($id === 9 || ($id >= 47 && $id <= 59)) {
            return 'Farmacia e Inventario';
        }

        if ($id === 10 || ($id >= 60 && $id <= 70)) {
            return 'Caja y Finanzas';
        }

        return 'General';
    }
}
