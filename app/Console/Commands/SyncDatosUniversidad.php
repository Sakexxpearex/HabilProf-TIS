<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncDatosUniversidad extends Command
{
    protected $signature = 'sync:universidad';
    protected $description = 'Sincroniza alumnos, profesores y notas desde la base fuente hacia la base destino';

    public function handle()
    {
        $this->info('▶️ Sincronización de Universidad iniciada...');

        try {
            // === 1. Alumnos ===
            $alumnos = DB::connection('pgsql_source')->table('sistema_alumnos')->get();

            foreach ($alumnos as $a) {
                DB::connection('pgsql')->table('alumno')->updateOrInsert(
                    ['rut_alumno' => $a->rut_alumno],
                    [
                        'rut_alumno' => $a->rut_alumno,
                        'nombre_alumno' => $a->nombre_alumno
                    ]
                );
            }
            $this->info('✅ Alumnos sincronizados.');

            // === 2. Profesores ===
            $profesores = DB::connection('pgsql_source')->table('sistema_profesores')->get();

            foreach ($profesores as $p) {
                DB::connection('pgsql')->table('profesor')->updateOrInsert(
                    ['rut_profesor' => $p->rut_profesor],
                    [
                        'rut_profesor' => $p->rut_profesor,
                        'nombre_profesor' => $p->nombre_profesor,
                        'departamento' => $p->departamento
                    ]
                );
            }
            $this->info('✅ Profesores sincronizados.');

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

            $this->info('🎉 Sincronización completada con éxito.');
        } catch (\Exception $e) {
            $this->error('🚨 Error en la sincronización: ' . $e->getMessage());
            $this->error('📍 Línea: ' . $e->getLine());
        }
    }
}
