<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarPermiso
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permisos
     */
    public function handle(Request $request, Closure $next, string ...$permisos): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Admin tiene todos los permisos
        if ($request->user()->esAdmin()) {
            return $next($request);
        }

        foreach ($permisos as $permiso) {
            if ($request->user()->tienePermiso($permiso)) {
                return $next($request);
            }
        }

        abort(403, 'No tienes permiso para realizar esta acción.');
    }
}
