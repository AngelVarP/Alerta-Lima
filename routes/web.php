<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DenunciaController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (requieren autenticación)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Denuncias
    Route::prefix('denuncias')->name('denuncias.')->group(function () {
        Route::get('/', [DenunciaController::class, 'index'])->name('index');
        Route::get('/crear', [DenunciaController::class, 'create'])->name('create');
        Route::post('/', [DenunciaController::class, 'store'])->name('store');
        Route::get('/{denuncia}', [DenunciaController::class, 'show'])->name('show');
        Route::post('/{denuncia}/estado', [DenunciaController::class, 'cambiarEstado'])->name('cambiar-estado');
        Route::post('/{denuncia}/asignar', [DenunciaController::class, 'asignar'])->name('asignar');
        Route::post('/{denuncia}/comentario', [DenunciaController::class, 'agregarComentario'])->name('agregar-comentario');
    });

    // Perfil de usuario
    Route::get('/perfil', function () {
        return view('perfil.index');
    })->name('perfil');

});

/*
|--------------------------------------------------------------------------
| Rutas de Administración (requieren rol admin o supervisor)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'rol:admin,supervisor'])->prefix('admin')->name('admin.')->group(function () {

    // Gestión de usuarios
    Route::prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('index');
        Route::get('/crear', [UsuarioController::class, 'create'])->name('create');
        Route::post('/', [UsuarioController::class, 'store'])->name('store');
        Route::get('/{usuario}/editar', [UsuarioController::class, 'edit'])->name('edit');
        Route::put('/{usuario}', [UsuarioController::class, 'update'])->name('update');
        Route::post('/{usuario}/toggle-activo', [UsuarioController::class, 'toggleActivo'])->name('toggle-activo');
    });

});

/*
|--------------------------------------------------------------------------
| Rutas solo para Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'rol:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Auditoría
    Route::get('/auditoria', function () {
        $registros = \App\Models\RegistroAuditoria::with('usuario')
            ->orderBy('creado_en', 'desc')
            ->paginate(50);
        return view('admin.auditoria', compact('registros'));
    })->name('auditoria');

    // Eventos de seguridad
    Route::get('/seguridad', function () {
        $eventos = \App\Models\EventoSeguridad::with('usuario')
            ->orderBy('creado_en', 'desc')
            ->paginate(50);
        return view('admin.seguridad', compact('eventos'));
    })->name('seguridad');

});
