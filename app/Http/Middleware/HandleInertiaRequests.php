<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'nombre' => $request->user()->nombre,
                    'apellido' => $request->user()->apellido,
                    'email' => $request->user()->email,
                    'roles' => $this->getUserRoles($request->user()),
                    'permissions' => $this->getUserPermissions($request->user()),
                ] : null,
            ],
        ];
    }

    /**
     * Obtener roles del usuario
     */
    private function getUserRoles($user): array
    {
        $roles = DB::table('rol_usuario')
            ->join('roles', 'rol_usuario.rol_id', '=', 'roles.id')
            ->where('rol_usuario.usuario_id', $user->id)
            ->pluck('roles.nombre')
            ->toArray();

        return $roles;
    }

    /**
     * Obtener permisos del usuario
     */
    private function getUserPermissions($user): array
    {
        // Obtener permisos a través de roles
        $permissions = DB::table('rol_usuario')
            ->join('rol_permiso', 'rol_usuario.rol_id', '=', 'rol_permiso.rol_id')
            ->join('permisos', 'rol_permiso.permiso_id', '=', 'permisos.id')
            ->where('rol_usuario.usuario_id', $user->id)
            ->pluck('permisos.nombre')
            ->unique()
            ->toArray();

        return $permissions;
    }
}
