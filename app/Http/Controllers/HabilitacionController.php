<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HabilitacionController extends Controller
{
    public function store(Request $request)
    {
        // 1️⃣ Validación (semestre_inicio usa 'in' y profesor_tutor usa 'integer')
        $validated = $request->validate([
            'rut_alumno' => 'required|integer', 
            'semestre_inicio_año' => 'required|integer|min:2000|max:2100',
            // ✅ CORRECCIÓN: Usando 'in' para evitar errores de regex.
            'semestre_inicio' => 'required|string|in:S1,S2', 
            'descripcion' => 'nullable|string',
            'tipo' => 'required|string|in:ingenieria,investigacion,practica',
            
            // Validaciones Condicionales
            'titulo' => 'nullable|string|max:255',
            'profesor_guia' => 'nullable|integer',
            'profesor_comision' => 'nullable|integer',
            'profesor_coguia' => 'nullable|integer',
            // ✅ CORRECCIÓN: Consistencia de tipo de dato
            'profesor_tutor' => 'nullable|integer', 
            'nombre_empresa' => 'nullable|string|max:255',
            'nombre_supervisor' => 'nullable|string|max:255',
        ]);

        $existe = DB::table('habilitacion')
            ->where('rut_alumno', $validated['rut_alumno'])
            ->exists();

        if ($existe) {
            return response()->json([
                'errors' => [
                    'rut_alumno' => ['El alumno ya posee una habilitación registrada y no puede tener más de una.']
                ]
            ], 422);
        }

        // **INICIO DE LA TRANSACCIÓN**
        DB::beginTransaction();
        
        try {
            // ❌ ELIMINADO: Se eliminan el cálculo manual del ID (Pasos 2, 3 y 4).

            // Validaciones Adicionales
            if (($validated['tipo'] === 'ingenieria' || $validated['tipo'] === 'investigacion') && (!$request->input('profesor_guia') || !$request->input('profesor_comision') || !$request->input('titulo'))) {
                DB::rollBack();
                return response()->json(['errors' => ['proyecto' => ['Faltan campos requeridos para el Proyecto (Guía, Comisión, Título).']]], 422);
            }
            if ($validated['tipo'] === 'practica' && (!$request->input('profesor_tutor') || !$request->input('nombre_empresa') || !$request->input('nombre_supervisor'))) {
                DB::rollBack();
                return response()->json(['errors' => ['practica' => ['Faltan campos requeridos para la Práctica (Tutor, Empresa, Supervisor).']]], 422);
            }

            // 5️⃣ Insertar en la tabla habilitacion y RECUPERAR el ID generado por la BD
            // Usamos insertGetId y le indicamos a Laravel que la PK es 'id_habilitacion'.
            $nuevoID = DB::table('habilitacion')->insertGetId([
                // 'id_habilitacion' ya NO se inserta manualmente
                'rut_alumno' => $validated['rut_alumno'],
                'semestre_inicio_año' => $validated['semestre_inicio_año'],
                'semestre_inicio' => $validated['semestre_inicio'],
                'descripcion' => $validated['descripcion'] ?? null,
                
            ], 'id_habilitacion'); // <-- Especificamos la columna PK para PostgreSQL

            // 6️⃣ Insertar según tipo de habilitación, usando el ID generado ($nuevoID)
            switch ($validated['tipo']) {
                case 'ingenieria':
                case 'investigacion':
                    $table = $validated['tipo'] === 'ingenieria' ? 'proyecto_ingenieria' : 'proyecto_investigacion';
                    
                    DB::table($table)->insert([
                        'id_habilitacion' => $nuevoID,
                        'titulo' => $request->input('titulo'),
                    ]);

                    // Guía y Comisión
                    DB::table('supervisa')->insert([
                        ['rut_profesor' => $request->input('profesor_guia'), 'id_habilitacion' => $nuevoID, 'tipo_profesor' => 'guía'],
                        ['rut_profesor' => $request->input('profesor_comision'), 'id_habilitacion' => $nuevoID, 'tipo_profesor' => 'comision'],
                    ]);

                    // Co-guía opcional
                    if ($request->filled('profesor_coguia')) {
                        DB::table('supervisa')->insert([
                            'rut_profesor' => $request->input('profesor_coguia'),
                            'id_habilitacion' => $nuevoID,
                            'tipo_profesor' => 'co-guia',
                        ]);
                    }
                    break;

                case 'practica':
                    DB::table('practica_tutelada')->insert([
                        'id_habilitacion' => $nuevoID,
                        'nombre_empresa' => $request->input('nombre_empresa'),
                        'nombre_supervisor' => $request->input('nombre_supervisor'),
                    ]);

                    DB::table('supervisa')->insert([
                        'rut_profesor' => $request->input('profesor_tutor'),
                        'id_habilitacion' => $nuevoID,
                        'tipo_profesor' => 'tutor',
                    ]);
                    break;
            }

            // Confirmar todas las inserciones
            DB::commit(); 

            // 7️⃣ Devolver respuesta con ID generado
            return response()->json([
                'message' => 'Habilitación creada con éxito',
                'id_habilitacion' => str_pad($nuevoID, 6, '0', STR_PAD_LEFT)
            ]);

        } catch (\Exception $e) {
            // Deshacer todas las inserciones si algo falló
            DB::rollBack(); 

            // Devolver error de base de datos detallado
            return response()->json([
                'message' => 'Error de base de datos al guardar la habilitación. La transacción fue revertida.',
                'error_detail' => $e->getMessage() 
            ], 500);
        }
    }
    
    // NOTA: La función getNextId se mantiene sin cambios, pero ya no es crucial.
    public function getNextId()
    {
        $ultimoID = DB::table('habilitacion')->max('id_habilitacion');
        $nuevoID = $ultimoID ? $ultimoID + 1 : 1;

        if ($nuevoID > 999999) {
            return response()->json(['error' => 'Límite máximo de ID alcanzado'], 400);
        }

        return response()->json([
            'next_id' => str_pad($nuevoID, 6, '0', STR_PAD_LEFT)
        ]);
    }
    public function obtenerProfesores()
    {
    // Profesores del DINF
    $profesoresDinf = DB::table('profesor')
        ->whereRaw("LOWER(TRIM(departamento)) = 'dinf'")
        ->select('rut_profesor', 'nombre_profesor', 'departamento')
        ->orderBy('nombre_profesor')
        ->get();

    // Todos los profesores (para co-guía)
    $profesoresTodos = DB::table('profesor')
        ->select('rut_profesor', 'nombre_profesor', 'departamento')
        ->orderBy('nombre_profesor')
        ->get();

    return response()->json([
        'dinf' => $profesoresDinf,
        'todos' => $profesoresTodos,
    ]);
    }
}

