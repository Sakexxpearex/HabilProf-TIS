<?php

use App\Http\Controllers\AlumnoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserSyncController;
use App\Http\Controllers\HabilitacionController;
use App\Models\User;

// Rutas API públicas (sin auth)
Route::get('/users/list', [UserSyncController::class, 'index'])->name('users.list');

// Rutas principales de habilitaciones
Route::post('/habilitaciones', [HabilitacionController::class, 'store'])->name('habilitaciones.store');
Route::get('/habilitaciones', [HabilitacionController::class, 'index'])->name('habilitaciones.index');
Route::get('/habilitaciones/next-id', [HabilitacionController::class, 'getNextId']);
Route::get('/profesores', [HabilitacionController::class, 'obtenerProfesores']);
Route::get('/alumnos/{rut}', [AlumnoController::class, 'buscarPorRut']);