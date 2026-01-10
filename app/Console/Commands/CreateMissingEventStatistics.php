<?php

namespace App\Console\Commands;

use Domain\EventManagement\Models\Event;
use Domain\EventManagement\Models\EventStatistics;
use Illuminate\Console\Command;

class CreateMissingEventStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:create-missing-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create missing event statistics for events that don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting missing event statistics creation...');

        // 1. Obtener todos los IDs de Eventos existentes
        $eventIds = Event::pluck('id');

        // 2. Obtener los IDs de Eventos que YA tienen una estadística
        // Asumiendo que la relación en EventStatistic es 'event_id'
        $eventStatsIds = EventStatistics::pluck('event_id');

        // 3. Calcular la diferencia (Eventos que NO tienen una estadística)
        $missingEventIds = $eventIds->diff($eventStatsIds);

        $count = $missingEventIds->count();

        if ($count === 0) {
            $this->info('✅ There are no missing event statistics to create.');

            return 0;
        }

        $this->warn("⚠️ Found **{$count}** events without their statistics. Creating...");

        // Usar una barra de progreso para una mejor visualización
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        // 4. Iterar y crear el registro EventStatistic para cada ID faltante
        // Se recomienda usar transacciones para evitar inconsistencias
        \DB::transaction(function () use ($missingEventIds, $progressBar) {
            foreach ($missingEventIds as $eventId) {
                // Aquí usamos create() para asegurar que el fillable/mass assignment esté configurado.
                // Ajusta los valores predeterminados según la configuración de tu tabla EventStatistic.
                EventStatistics::create([
                    'event_id' => $eventId,
                    'views_count' => 0, // o el valor inicial que desees
                    'likes_count' => 0,
                    // ... cualquier otro campo con valor inicial
                ]);
                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine();
        $this->info('🎉 Process finished. Created '.$count.' missing event statistics.');

        return 0; // Código de salida exitoso
    }
}
