<?php

namespace App\Console\Commands;

use Domain\EventManagement\Models\Event;
use Domain\Ticketing\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class EventPeakHours extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:peak-hours {event_id : The ID of the event to analyze}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyzes and visualizes peak ticket scanning hours for a specific event';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $eventId = $this->argument('event_id');
        $event = Event::find($eventId);

        if (! $event) {
            $this->components->error("❌ Event with ID {$eventId} not found.");
            
            return 1;
        }

        $this->info("📊 Analyzing scan data for event: {$event->title} (ID: {$eventId})");

        // Get all scanned tickets
        $tickets = Ticket::where('event_id', $eventId)
            ->whereNotNull('used_at')
            ->get();

        if ($tickets->isEmpty()) {
            $this->components->warn('⚠️ No scanned tickets found for this event.');
            
            return 0;
        }

        $totalScans = $tickets->count();
        $this->info("Total scanned tickets: {$totalScans}");

        // Group by Date and Hour
        $hourlyData = $tickets->groupBy(function ($ticket) {
            return Carbon::parse($ticket->used_at)->format('Y-m-d H:00');
        })->map(function ($group) {
            return $group->count();
        })->sortKeys();

        if ($hourlyData->isEmpty()) {
            $this->components->warn('⚠️ Not enough data to generate peak hours.');
            
            return 0;
        }

        // Find the absolute peak for percentages/graphing
        $maxScansInAnHour = $hourlyData->max();

        // Prepare table data
        $tableData = [];
        foreach ($hourlyData as $hour => $count) {
            $percentage = ($count / $totalScans) * 100;
            $tableData[] = [
                $hour,
                $count,
                number_format($percentage, 2) . '%'
            ];
        }

        $this->newLine();
        $this->components->info('Peak Hours Data (Table)');
        $this->table(
            ['Date / Hour', 'Scans', '% of Total'],
            $tableData
        );

        $this->newLine();
        $this->components->info('Hourly Distribution Graph (Scans)');
        $this->drawGraph($hourlyData, $maxScansInAnHour);

        $this->newLine();
        // Busiest hour
        $busiestHour = $hourlyData->search($maxScansInAnHour);
        $this->components->info("🔥 The peak time was: {$busiestHour} with {$maxScansInAnHour} scans.");

        return 0;
    }

    /**
     * Draws an ASCII bar chart representation of the hourly data.
     *
     * @param \Illuminate\Support\Collection $hourlyData
     * @param int $maxScans
     */
    private function drawGraph($hourlyData, $maxScans)
    {
        $maxWidth = 50; // Max width of the bar in characters

        foreach ($hourlyData as $hour => $count) {
            // Calculate how many blocks to draw
            $barLength = ($maxScans > 0) ? (int) round(($count / $maxScans) * $maxWidth) : 0;
            
            // Build the bar using a solid block character
            $bar = str_repeat('█', $barLength);

            // Print the line
            $this->line(sprintf(" %-16s | %s %d", $hour, $bar, $count));
        }
    }
}
