<?php

namespace Domain\EventManagement\Actions;

use Domain\EventManagement\Models\Event;
use Inertia\Inertia;
use Lorisleiva\Actions\Concerns\AsAction;

class ManageCourtesies
{
    use AsAction;

    public function handle()
    {
        //
    }

    public function asController(int $eventId)
    {
        $event = Event::findOrFail($eventId)->load('organizer');
        return Inertia::render('organizers/event/Courtesies', [
            'event' => $event,
            'courtesies' => $event->courtesies()->get()
        ]);
    }
}
