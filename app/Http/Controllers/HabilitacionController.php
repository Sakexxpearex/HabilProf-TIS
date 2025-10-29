<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Habilitacion;
use App\Models\User; // Usamos el modelo User para las relaciones

class HabilitacionController extends Controller
{
    /**
     * Recibe y guarda los datos de la Habilitación, validando según la modalidad.
     */
    public function store(Request $request)
    {
        // 1. Reglas de Validación Comunes
        $rules = [
            'modalidad' => 'required|in:PrIng,PrInv,PrTut', // Debe ser uno de estos tres
            'alumno_id' => 'required|exists:users,id', // Debe existir en la tabla 'users'
            'profesor_dinf_id' => 'required|exists:users,id',
            'semestre_inicio' => 'required|string|max:10',
            // La nota y fecha de nota no se ingresan en este paso (se actualizan después)
        ];

        // 2. Reglas Específicas por Modalidad (Validación Dinámica)
        if (in_array($request->modalidad, ['PrIng', 'PrInv'])) {
            // Reglas para Proyecto de Ingeniería y Proyecto de Investigación
            $rules = array_merge($rules, [
                'titulo' => 'required|string|max:255',
                'descripcion_proyecto' => 'required|string',
                'co_guia_nombre' => 'nullable|string|max:255',
                'comision_profesor_id' => 'required|exists:users,id', // Debe ser un usuario existente
            ]);
        } elseif ($request->modalidad === 'PrTut') {
            // Reglas para Práctica Tutelada
            $rules = array_merge($rules, [
                'empresa_nombre' => 'required|string|max:255',
                'supervisor_empresa' => 'required|string|max:255',
                'descripcion_practica' => 'required|string',
            ]);
        }

        // 3. Ejecutar la Validación
        $datosValidados = $request->validate($rules);

        // 4. Crear la Habilitación en la Base de Datos
        // Laravel automáticamente solo intenta guardar los campos que existen en la tabla.
        $habilitacion = Habilitacion::create($datosValidados);

        // 5. Respuesta a Vue (JSON con el nuevo registro)
        return response()->json($habilitacion, 201); // 201 = Creado exitosamente
    }

    // [Auxiliar para el Listado]
    public function index()
    {
        // Esto se usa para el listado, devolviendo todas las habilitaciones.
        return Habilitacion::with(['alumno', 'profesorDinf', 'comisionProfesor'])
                           ->latest()
                           ->get();
    }
}