<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Auth/Login', [
        'canRegister' => Route::has('register'),
        'status' => session('status'),
    ]);
})->name('login');

Route::get('/register', function () {
    return Inertia::render('Auth/Register');
})->name('register');
