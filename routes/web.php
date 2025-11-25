<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DenunciaController; // <--- Importamos tu controlador
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

// Rutas de autenticación
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Rutas protegidas (requieren autenticación)
Route::middleware('auth')->group(function () {

    // --- RUTAS DEL CIUDADANO ---
    Route::middleware('role:ciudadano')->group(function () {

        // Dashboard del Ciudadano
        Route::get('/dashboard', function () {
            return Inertia::render('Ciudadano/Dashboard');
        })->name('dashboard');

        // Mis Denuncias (Usando tu DenunciaController)
        Route::get('/mis-denuncias', [DenunciaController::class, 'index'])
            ->name('denuncias.index');

        // Aquí agregarías las rutas para crear y ver detalle:
        Route::get('/denuncias/nueva', [DenunciaController::class, 'create'])->name('denuncias.create');
        Route::post('/denuncias', [DenunciaController::class, 'store'])->name('denuncias.store');
        Route::get('/denuncias/{denuncia}', [DenunciaController::class, 'show'])->name('denuncias.show');
    });

    // --- RUTAS ADMINISTRATIVAS ---
    // Dashboard de Funcionario
    Route::get('/funcionario', function () {
        return Inertia::render('Funcionario/Dashboard');
    })->middleware('role:funcionario,supervisor,admin')->name('funcionario.dashboard');

    // Dashboard de Supervisor
    Route::get('/supervisor', function () {
        return Inertia::render('Supervisor/Dashboard');
    })->middleware('role:supervisor,admin')->name('supervisor.dashboard');

    // Dashboard de Admin
    Route::get('/admin', function () {
        return Inertia::render('Admin/Dashboard');
    })->middleware('role:admin')->name('admin.dashboard');
});