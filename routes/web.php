<?php

use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DenunciaController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Rutas de autenticación
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Rutas protegidas (requieren autenticación)
Route::middleware('auth')->group(function () {

    // --- RUTA DASHBOARD INTELIGENTE (redirige según rol) ---
    Route::get('/dashboard', function () {
        $user = auth()->user();

        // Redirigir según el rol del usuario (jerarquía: admin > supervisor > funcionario > ciudadano)
        if ($user->esAdmin()) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->tieneRol('supervisor')) {
            return redirect()->route('supervisor.dashboard');
        } elseif ($user->tieneRol('funcionario')) {
            return redirect()->route('funcionario.dashboard');
        } else {
            // Si es ciudadano o cualquier otro rol, mostrar dashboard de ciudadano
            return app(DashboardController::class)->ciudadano(request());
        }
    })->name('dashboard');

    // --- RUTAS DEL CIUDADANO ---
    Route::middleware('role:ciudadano')->group(function () {
        // Dashboard del Ciudadano (ruta específica interna)
        Route::get('/ciudadano/dashboard', [DashboardController::class, 'ciudadano'])->name('ciudadano.dashboard');

        // Mis Denuncias
        Route::get('/mis-denuncias', [DenunciaController::class, 'index'])->name('denuncias.index');
        Route::get('/denuncias/nueva', [DenunciaController::class, 'create'])->name('denuncias.create');
        Route::post('/denuncias', [DenunciaController::class, 'store'])->name('denuncias.store');
        Route::get('/denuncias/{denuncia}', [DenunciaController::class, 'show'])->name('denuncias.show');

        // Notificaciones
        Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
        Route::post('/notificaciones/{id}/marcar-leida', [NotificacionController::class, 'markAsRead'])->name('notificaciones.markAsRead');
        Route::post('/notificaciones/marcar-todas-leidas', [NotificacionController::class, 'markAllAsRead'])->name('notificaciones.markAllAsRead');
        Route::delete('/notificaciones/{id}', [NotificacionController::class, 'destroy'])->name('notificaciones.destroy');
        Route::get('/notificaciones/no-leidas/count', [NotificacionController::class, 'unreadCount'])->name('notificaciones.unreadCount');

        // Perfil
        Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/perfil/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    });

    // --- RUTAS DEL FUNCIONARIO ---
    Route::middleware('role:funcionario,supervisor,admin')->prefix('funcionario')->name('funcionario.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [FuncionarioController::class, 'dashboard'])->name('dashboard');

        // Gestión de denuncias
        Route::get('/denuncias', [FuncionarioController::class, 'index'])->name('denuncias.index');
        Route::get('/denuncias/{denuncia}', [FuncionarioController::class, 'show'])->name('denuncias.show');

        // Acciones sobre denuncias
        Route::post('/denuncias/{denuncia}/cambiar-estado', [FuncionarioController::class, 'cambiarEstado'])->name('denuncias.cambiar-estado');
        Route::post('/denuncias/{denuncia}/tomar-asignacion', [FuncionarioController::class, 'tomarAsignacion'])->name('denuncias.tomar-asignacion');
        Route::post('/denuncias/{denuncia}/comentar', [FuncionarioController::class, 'agregarComentario'])->name('denuncias.comentar');
    });

    // --- RUTAS DEL SUPERVISOR ---
    Route::middleware('role:supervisor,admin')->prefix('supervisor')->name('supervisor.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [SupervisorController::class, 'dashboard'])->name('dashboard');

        // Gestión de denuncias
        Route::get('/denuncias', [SupervisorController::class, 'index'])->name('denuncias.index');
        Route::get('/denuncias/{denuncia}', [SupervisorController::class, 'show'])->name('denuncias.show');

        // Asignación y reasignación
        Route::post('/denuncias/{denuncia}/asignar', [SupervisorController::class, 'asignar'])->name('denuncias.asignar');
        Route::post('/denuncias/{denuncia}/reasignar', [SupervisorController::class, 'reasignar'])->name('denuncias.reasignar');
        Route::post('/denuncias/{denuncia}/cambiar-prioridad', [SupervisorController::class, 'cambiarPrioridad'])->name('denuncias.cambiar-prioridad');

        // Reportes
        Route::get('/reportes', [SupervisorController::class, 'reportes'])->name('reportes');
    });

    // --- RUTAS DEL ADMIN ---
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('dashboard');

        // Gestión de usuarios
        Route::resource('usuarios', UsuarioController::class);
        Route::post('/usuarios/{usuario}/toggle-activo', [UsuarioController::class, 'toggleActivo'])->name('usuarios.toggle-activo');

        // Auditoría
        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
        Route::get('/auditoria/{registro}', [AuditoriaController::class, 'show'])->name('auditoria.show');
        Route::get('/auditoria/estadisticas', [AuditoriaController::class, 'estadisticas'])->name('auditoria.estadisticas');

        // Seguridad
        Route::get('/seguridad', [AuditoriaController::class, 'eventosSeguridad'])->name('seguridad.index');
        Route::get('/seguridad/{evento}', [AuditoriaController::class, 'showEventoSeguridad'])->name('seguridad.show');

        // Reportes
        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/exportar-csv', [ReporteController::class, 'exportarCSV'])->name('reportes.exportar-csv');
        Route::get('/reportes/exportar-pdf', [ReporteController::class, 'exportarPDF'])->name('reportes.exportar-pdf');
        Route::get('/reportes/rendimiento-funcionarios', [ReporteController::class, 'rendimientoFuncionarios'])->name('reportes.rendimiento-funcionarios');
        Route::get('/reportes/sla', [ReporteController::class, 'reporteSLA'])->name('reportes.sla');
    });

    // --- RUTAS COMPARTIDAS (COMENTARIOS) ---
    Route::middleware('auth')->group(function () {
        Route::post('/denuncias/{denuncia}/comentarios', [ComentarioController::class, 'store'])->name('comentarios.store');
        Route::put('/comentarios/{comentario}', [ComentarioController::class, 'update'])->name('comentarios.update');
        Route::delete('/comentarios/{comentario}', [ComentarioController::class, 'destroy'])->name('comentarios.destroy');
    });

    // --- RUTA DE DIAGNÓSTICO (TEMPORAL - ELIMINAR EN PRODUCCIÓN) ---
    Route::get('/debug-user', function () {
        $user = auth()->user();
        $roles = \Illuminate\Support\Facades\DB::table('rol_usuario')
            ->join('roles', 'rol_usuario.rol_id', '=', 'roles.id')
            ->where('rol_usuario.usuario_id', $user->id)
            ->select('roles.id', 'roles.nombre', 'roles.codigo')
            ->get();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'apellido' => $user->apellido,
                'email' => $user->email,
            ],
            'roles' => $roles,
            'tieneRol_ciudadano' => $user->tieneRol('ciudadano'),
            'tieneRol_funcionario' => $user->tieneRol('funcionario'),
            'tieneRol_supervisor' => $user->tieneRol('supervisor'),
            'tieneRol_admin' => $user->tieneRol('admin'),
            'esAdmin' => $user->esAdmin(),
            'esFuncionario' => $user->esFuncionario(),
        ]);
    })->name('debug.user');
});
