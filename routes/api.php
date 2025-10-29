<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HabilitacionController; 
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Estas rutas son cargadas por el RouteServiceProvider y usan el middleware "api".
|
*/

// Agrupamos todas las rutas de la API bajo el middleware de autenticación Sanctum.
Route::middleware('auth:sanctum')->group(function () {
    
    // [Auxiliar] Ruta para obtener la lista de usuarios (Alumnos y Profesores)
    Route::get('/users/list', function () {
        // Devolvemos todos los usuarios registrados (ID y Nombre)
        return User::select('id', 'name')->orderBy('name')->get();
    })->name('users.list');

    // [CREAR - Funcionalidad b] Ruta para recibir el formulario de Vue
    Route::post('/habilitaciones', [HabilitacionController::class, 'store'])->name('habilitaciones.store');

    // [LEER - Funcionalidad d] Ruta para obtener el listado principal
    Route::get('/habilitaciones', [HabilitacionController::class, 'index'])->name('habilitaciones.index');
    
    // Puedes agregar aquí la ruta /user para obtener los datos del usuario autenticado si es necesario
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});