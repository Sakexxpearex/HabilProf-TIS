<?php

namespace App\Http\Controllers;

use App\Models\Habilitacion;
use App\Models\User;

class ListadoController extends Controller
{
    /**
     * Listado ordenado por semestre (año y número)
     */
    public function listadoSemestre()
    {
    $habilitaciones = Habilitacion::with(['alumno', 'profesorDinf'])
        ->orderBy('semestre_inicio_año', 'asc')
        ->orderBy('semestre_inicio', 'asc')
        ->get()
        ->map(function ($h) {
            return [
                'rut_alumno' => $h->alumno->rut_alumno ?? 'N/A',
                'nombre_alumno' => $h->alumno->nombre_alumno ?? 'N/A',
                'tipo_habilitacion' => $h->tipo_habilitacion,
                'rut_profesor' => $h->profesorDinf->rut_usuario ?? 'N/A',
                'nombre_profesor' => $h->profesorDinf->nombre_usuario ?? 'N/A',
                'semestre_inicio' => $h->semestre_inicio,
            ];
        });

    return inertia('Habilitacion/ListadoSemestre', [
        'habilitaciones' => $habilitaciones
    ]);
}



    /**
     * Listado agrupado por profesor
     * (como profesor guía o como comisión)
     */
    public function listadoProfesor()
    {
        $profesores = User::with([
            'habilitacionesComoGuia',
            'habilitacionesComoComision'
        ])
        ->where('tipo_usuario', 'profesor')
        ->get();

        return inertia('Habilitacion/ListadoProfesor', [
            'profesores' => $profesores
        ]);
    }
}
