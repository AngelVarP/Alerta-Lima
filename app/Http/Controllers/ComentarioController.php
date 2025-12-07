<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use App\Models\Denuncia;
use Illuminate\Http\Request;

class ComentarioController extends Controller
{
    /**
     * Agregar comentario a una denuncia
     */
    public function store(Request $request, Denuncia $denuncia)
    {
        $this->authorize('comentar', $denuncia);

        $validated = $request->validate([
            'contenido' => 'required|string|min:5|max:1000',
            'es_interno' => 'boolean',
        ]);

        $comentario = $denuncia->comentarios()->create([
            'usuario_id' => $request->user()->id,
            'contenido' => $validated['contenido'],
            'es_interno' => $request->boolean('es_interno', true),
        ]);

        // Si el comentario es público, notificar al ciudadano
        if (! $comentario->es_interno) {
            \App\Models\Notificacion::create([
                'usuario_id' => $denuncia->ciudadano_id,
                'denuncia_id' => $denuncia->id,
                'tipo' => 'NUEVO_COMENTARIO',
                'titulo' => 'Nuevo comentario en tu denuncia',
                'mensaje' => "Hay un nuevo comentario en tu denuncia {$denuncia->codigo}",
                'url' => route('denuncias.show', $denuncia->id),
            ]);
        }

        return back()->with('success', 'Comentario agregado correctamente.');
    }

    /**
     * Actualizar un comentario
     */
    public function update(Request $request, Comentario $comentario)
    {
        // Solo el autor puede editar su comentario
        if ($comentario->usuario_id !== $request->user()->id && ! $request->user()->esAdmin()) {
            abort(403, 'No tienes permiso para editar este comentario.');
        }

        $validated = $request->validate([
            'contenido' => 'required|string|min:5|max:1000',
        ]);

        $comentario->update([
            'contenido' => $validated['contenido'],
            'editado' => true,
        ]);

        return back()->with('success', 'Comentario actualizado correctamente.');
    }

    /**
     * Eliminar un comentario
     */
    public function destroy(Request $request, Comentario $comentario)
    {
        // Solo el autor o admin pueden eliminar
        if ($comentario->usuario_id !== $request->user()->id && ! $request->user()->esAdmin()) {
            abort(403, 'No tienes permiso para eliminar este comentario.');
        }

        $comentario->delete();

        return back()->with('success', 'Comentario eliminado correctamente.');
    }
}
