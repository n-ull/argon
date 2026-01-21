<?php

namespace Domain\Promoters\Actions;

use Domain\EventManagement\Models\Event;
use Domain\Promoters\Models\Promoter;
use Lorisleiva\Actions\Concerns\AsAction;

class RemovePromoterFromEvent
{
    use AsAction;

    public function handle(int $eventId, int $promoterId)
    {
        $event = Event::findOrFail($eventId);
        $promoter = Promoter::findOrFail($promoterId);

        // Detach the promoter from the event
        $event->promoters()->detach($promoter->id);
    }

    public function asController(int $eventId, int $promoterId)
    {
        $this->handle($eventId, $promoterId);

        return redirect()->back();
    }
}
