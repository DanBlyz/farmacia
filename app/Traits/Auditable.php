<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /**
     * Boot the trait to handle automatic auditing on creating, updating, and deleting.
     */
    protected static function bootAuditable(): void
    {
        static::creating(function ($model): void {
            if (Auth::check() && empty($model->usuario_creador_id)) {
                $model->usuario_creador_id = Auth::id();
            }
        });

        static::updating(function ($model): void {
            if (Auth::check()) {
                $model->usuario_modificador_id = Auth::id();
            }
        });

        static::deleting(function ($model): void {
            if (Auth::check() && in_array(SoftDeletes::class, class_uses_recursive($model))) {
                $model->usuario_eliminador_id = Auth::id();
                $model->saveQuietly();
            }
        });
    }

    /**
     * Usuario que creó el registro.
     */
    public function usuarioCreador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }

    /**
     * Usuario que modificó el registro.
     */
    public function usuarioModificador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_modificador_id');
    }

    /**
     * Usuario que eliminó el registro (SoftDelete).
     */
    public function usuarioEliminador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_eliminador_id');
    }
}
