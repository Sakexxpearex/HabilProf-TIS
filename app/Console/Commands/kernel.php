<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define los comandos de la aplicación.
     */
    protected function commands(): void
    {
        // Esto carga automáticamente todos los comandos que tengas en app/Console/Commands
        $this->load(__DIR__.'/Commands');

        // Si tienes rutas de consola personalizadas (no obligatorio)
        require base_path('routes/console.php');
    }

    /**
     * Define la programación de tareas automáticas.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Ejecutar sincronización cada hora (puedes cambiarlo por ->daily(), ->everyMinute(), etc)
        $schedule->command('sync:universidad')->everyMinute();
    }
}
