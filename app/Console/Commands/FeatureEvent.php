<?php

namespace App\Console\Commands;

use Domain\EventManagement\Models\Event;
use Illuminate\Console\Command;

class FeatureEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:feature {event_id : The ID of the event}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Feature an event';

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

        $event->is_featured = true;
        $event->save();

        $this->components->info("✅ Event with ID {$eventId} has been featured.");

        return 0;
    }
}
