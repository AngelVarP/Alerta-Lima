<?php

namespace App\Http\Controllers;

use App\Models\CategoriaDenuncia;
use App\Models\Denuncia;
use App\Models\EstadoDenuncia;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $usuario = $request->user();

        // Estadísticas generales
        $estadisticas = $this->obtenerEstadisticas($usuario);

        // Denuncias recientes
        $denunciasRecientes = $this->obtenerDenunciasRecientes($usuario);

        // Denuncias con SLA vencido
        $denunciasSlaPendiente = Denuncia::slaPendiente()
            ->with(['estado', 'categoria', 'prioridad'])
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'estadisticas',
            'denunciasRecientes',
            'denunciasSlaPendiente'
        ));
    }

    public function ciudadano(Request $request)
    {
        $usuario = $request->user();

        // Obtener estadísticas y denuncias en paralelo de forma eficiente
        $total = Denuncia::where('ciudadano_id', $usuario->id)->count();
        $cerradas = Denuncia::where('ciudadano_id', $usuario->id)
            ->whereHas('estado', fn ($q) => $q->where('es_final', true))
            ->count();
        $abiertas = $total - $cerradas;

        $notificacionesNoLeidas = \App\Models\Notificacion::where('usuario_id', $usuario->id)
            ->noLeidas()
            ->count();

        // Obtener solo las denuncias recientes necesarias (limitadas)
        $denunciasRecientes = Denuncia::where('ciudadano_id', $usuario->id)
            ->with(['estado:id,nombre', 'categoria:id,nombre'])
            ->select('id', 'titulo', 'estado_id', 'categoria_id', 'creado_en')
            ->orderBy('creado_en', 'desc')
            ->limit(5)
            ->get();

        return \Inertia\Inertia::render('Ciudadano/Dashboard', [
            'stats' => [
                'total' => $total,
                'resueltas' => $cerradas,
                'en_proceso' => $abiertas,
                'notificaciones' => $notificacionesNoLeidas,
            ],
            'activities' => $denunciasRecientes,
        ]);
    }

    private function obtenerEstadisticas($usuario): array
    {
        $query = Denuncia::query();

        // Si es ciudadano, solo sus denuncias
        if ($usuario->tieneRol('ciudadano') && ! $usuario->esFuncionario()) {
            $query->where('ciudadano_id', $usuario->id);
        }
        // Si es funcionario, denuncias de su área
        elseif ($usuario->tieneRol('funcionario') && ! $usuario->esAdmin()) {
            $query->where('area_id', $usuario->area_id);
        }

        $total = $query->count();
        $abiertas = (clone $query)->whereHas('estado', fn ($q) => $q->where('es_final', false))->count();
        $cerradas = (clone $query)->whereHas('estado', fn ($q) => $q->where('es_final', true))->count();
        $slaVencido = (clone $query)->slaPendiente()->count();

        // Estadísticas por estado
        $porEstado = EstadoDenuncia::withCount([
            'denuncias' => function ($q) use ($usuario) {
                if ($usuario->tieneRol('ciudadano') && ! $usuario->esFuncionario()) {
                    $q->where('ciudadano_id', $usuario->id);
                } elseif ($usuario->tieneRol('funcionario') && ! $usuario->esAdmin()) {
                    $q->where('area_id', $usuario->area_id);
                }
            },
        ])->ordenado()->get();

        // Estadísticas por categoría
        $porCategoria = CategoriaDenuncia::withCount([
            'denuncias' => function ($q) use ($usuario) {
                if ($usuario->tieneRol('ciudadano') && ! $usuario->esFuncionario()) {
                    $q->where('ciudadano_id', $usuario->id);
                } elseif ($usuario->tieneRol('funcionario') && ! $usuario->esAdmin()) {
                    $q->where('area_id', $usuario->area_id);
                }
            },
        ])->activas()->get();

        return [
            'total' => $total,
            'abiertas' => $abiertas,
            'cerradas' => $cerradas,
            'slaVencido' => $slaVencido,
            'porEstado' => $porEstado,
            'porCategoria' => $porCategoria,
        ];
    }

    private function obtenerDenunciasRecientes($usuario)
    {
        $query = Denuncia::with(['estado', 'categoria', 'prioridad', 'distrito'])
            ->orderBy('registrada_en', 'desc')
            ->limit(10);

        if ($usuario->tieneRol('ciudadano') && ! $usuario->esFuncionario()) {
            $query->where('ciudadano_id', $usuario->id);
        } elseif ($usuario->tieneRol('funcionario') && ! $usuario->esAdmin()) {
            $query->where('area_id', $usuario->area_id);
        }

        return $query->get();
    }
}
