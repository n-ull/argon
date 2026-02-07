<?php

namespace Domain\EventManagement\Actions;

use Domain\EventManagement\Models\Event;
use Domain\Ticketing\Models\Doormen;
use Lorisleiva\Actions\Concerns\AsAction;

class SwitchDoormanStatus
{
    use AsAction;

    public function handle(Event $event, int $doormanId, bool $isActive)
    {
        $doorman = Doormen::where('event_id', $event->id)
            ->where('id', $doormanId)
            ->firstOrFail();

        $doorman->update(['is_active' => $isActive]);
    }
}
