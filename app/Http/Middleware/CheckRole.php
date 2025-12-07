<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Verificar si el usuario está autenticado
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // Si no se especificaron roles, solo verificar autenticación
        if (empty($roles)) {
            return $next($request);
        }

        // Verificar si el usuario tiene alguno de los roles permitidos
        foreach ($roles as $role) {
            if ($request->user()->tieneRol($role)) {
                return $next($request);
            }
        }

        // Si no tiene el rol requerido, denegar acceso
        abort(403, 'No tienes permisos para acceder a esta página.');
    }
}
