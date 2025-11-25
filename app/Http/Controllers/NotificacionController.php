<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificacionController extends Controller
{
    /**
     * Display a listing of notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Construir la consulta base
        $query = Notificacion::where('usuario_id', $user->id)
            ->with([
                'denuncia' => function ($q) {
                    $q->select('id', 'codigo', 'titulo');
                }
            ])
            ->orderBy('creado_en', 'desc');

        // Aplicar filtro de leídas/no leídas
        if ($request->filled('filtro')) {
            if ($request->input('filtro') === 'no_leidas') {
                $query->noLeidas();
            } elseif ($request->input('filtro') === 'leidas') {
                $query->leidas();
            }
        }

        // Aplicar filtro por tipo
        if ($request->filled('tipo')) {
            $query->porTipo($request->input('tipo'));
        }

        // Paginar resultados
        $notificaciones = $query->paginate(15)->withQueryString();

        // Contar no leídas
        $noLeidasCount = Notificacion::where('usuario_id', $user->id)
            ->noLeidas()
            ->count();

        return Inertia::render('Ciudadano/Notificaciones/Index', [
            'notificaciones' => $notificaciones,
            'noLeidasCount' => $noLeidasCount,
            'filtros' => [
                'filtro' => $request->input('filtro', ''),
                'tipo' => $request->input('tipo', ''),
            ],
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $notificacion = Notificacion::where('id', $id)
            ->where('usuario_id', Auth::id())
            ->firstOrFail();

        $notificacion->marcarComoLeida();

        return back()->with('success', 'Notificación marcada como leída');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Notificacion::where('usuario_id', Auth::id())
            ->noLeidas()
            ->update([
                'estado' => 'LEIDA',
                'leida_en' => now(),
            ]);

        return back()->with('success', 'Todas las notificaciones marcadas como leídas');
    }

    /**
     * Remove the specified notification.
     */
    public function destroy($id)
    {
        $notificacion = Notificacion::where('id', $id)
            ->where('usuario_id', Auth::id())
            ->firstOrFail();

        $notificacion->delete();

        return back()->with('success', 'Notificación eliminada');
    }

    /**
     * Get the count of unread notifications (for API/AJAX).
     */
    public function unreadCount()
    {
        $count = Notificacion::where('usuario_id', Auth::id())
            ->noLeidas()
            ->count();

        return response()->json(['count' => $count]);
    }
}
