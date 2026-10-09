<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermiso
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, int|string $permisoId): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401, 'No autenticado.');
        }

        if ($user->estado === 'INACTIVO' || (isset($user->activo) && ! $user->activo)) {
            abort(403, 'Su cuenta de usuario se encuentra inactiva.');
        }

        if ($user->esAdmin()) {
            return $next($request);
        }

        if (! $user->tienePermiso($permisoId)) {
            abort(403, 'No tiene autorización para acceder a este recurso.');
        }

        return $next($request);
    }
}
