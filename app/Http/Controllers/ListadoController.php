<?php

namespace App\Http\Controllers;

use App\Models\Habilitacion;
use App\Models\User;

class ListadoController extends Controller
{
public function listadoSemestre()
{
    $habilitaciones = Habilitacion::with([
        'alumno',
        'profesorDinf',
        'comisionProfesor'
    ])
    ->orderBy('semestre_inicio_año', 'asc')  // <-- corregido
    ->orderBy('semestre_inicio', 'asc')
    ->get();

    return inertia('Habilitacion/ListadoSemestre', [
        'habilitaciones' => $habilitaciones
    ]);
}



    public function listadoProfesor()
    {
        // Profesores que participan como guía o comisión
        $profesores = User::with([
            'habilitacionesComoGuia',
            'habilitacionesComoComision'
        ])
        ->where('tipo_usuario', 'profesor') // si tienes un campo para filtrar
        ->get();

        return inertia('Habilitacion/ListadoProfesor', [
            'profesores' => $profesores
        ]);
    }
}
