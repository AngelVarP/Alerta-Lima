<?php

use App\Http\Controllers\AuthController;
use Inertia\Inertia;

// Rutas de autenticación
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Rutas protegidas (requieren autenticación)
Route::middleware('auth')->group(function () {
    // Dashboard de Ciudadano (por defecto)
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware('role:ciudadano')->name('dashboard');

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
