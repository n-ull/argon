<?php

namespace Domain\EventManagement\Actions;

use Domain\EventManagement\Models\Event;
use Domain\Ticketing\Models\Doormen;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveDoorman
{
    use AsAction;

    public function handle(Event $event, int $doormanId)
    {
        $doorman = Doormen::where('event_id', $event->id)
            ->where('id', $doormanId)
            ->firstOrFail();

        $doorman->delete();
    }
}
