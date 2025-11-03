<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // 👈 1. IMPORTAR LA CLASE LOG

class SyncDatosUniversidad extends Command
{
    protected $signature = 'sync:universidad';
    protected $description = 'Sincroniza alumnos, profesores y notas desde la base fuente hacia la base destino';

    public function handle()
    {
        Log::info('▶️ Sincronización de Universidad iniciada por Scheduler.'); // 👈 LOG DE INICIO

        try { // 👈 2. INICIO DEL BLOQUE DE MANEJO DE ERRORES

            // === 1. Alumnos ===
            $alumnos = DB::connection('pgsql_source')->table('sistema_alumnos')->get();

            foreach ($alumnos as $a) {
                DB::connection('pgsql')->table('alumno')->updateOrInsert(
                    ['rut_alumno' => $a->rut_alumno],
                    [
                        'rut_alumno' => $a->rut_alumno, // RECOMENDACIÓN: asegurar que la clave se guarda
                        'nombre_alumno' => $a->nombre_alumno
                    ]
                );
            }

            $this->info('✅ Alumnos sincronizados.');
            Log::info('✅ Alumnos sincronizados correctamente.'); // 👈 LOG DE ÉXITO

            // === 2. Profesores ===
            $profesores = DB::connection('pgsql_source')->table('sistema_profesores')->get();

            foreach ($profesores as $p) {
                DB::connection('pgsql')->table('profesor')->updateOrInsert(
                    ['rut_profesor' => $p->rut_profesor],
                    [
                        'rut_profesor' => $p->rut_profesor, // RECOMENDACIÓN: asegurar que la clave se guarda
                        'nombre_profesor' => $p->nombre_profesor
                    ]
                );
            }
            $this->info('✅ Profesores sincronizados.');
            Log::info('✅ Profesores sincronizados correctamente.'); // 👈 LOG DE ÉXITO

            // === 3. Notas ===
            $notas = DB::connection('pgsql_source')->table('notas_en_linea')->get();

            foreach ($notas as $n) {
                $idHabilitacion = DB::connection('pgsql')->table('habilitacion')
                    ->where('rut_alumno', $n->rut_alumno)
                    ->value('id_habilitacion');

                if ($idHabilitacion) {
                    DB::connection('pgsql')->table('habilitacion')->updateOrInsert(
                        ['id_habilitacion' => $idHabilitacion],
                        [
                            'nota_final' => $n->nota,
                            'fecha_nota' => $n->fecha_nota,
                        ]
                    );
                }
            }
            $this->info('✅ Notas sincronizadas.');
            Log::info('✅ Notas sincronizadas correctamente.'); // 👈 LOG DE ÉXITO

            $this->info('🎉 Sincronización completada con éxito.');
            Log::info('🎉 Sincronización completada con éxito.');

        } catch (\Exception $e) { // 👈 3. CAPTURAR CUALQUIER EXCEPCIÓN
            
            // 🚨 Si falla, registraremos el error completo en el archivo laravel.log
            Log::error('🚨 Falla Crítica de Sincronización. Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine()
            ]);
            $this->error('🚨 Falla Crítica. Revisa el log de Laravel.');
        }
    }
}