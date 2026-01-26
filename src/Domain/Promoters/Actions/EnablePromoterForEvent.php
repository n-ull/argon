<?php

namespace Domain\Promoters\Actions;

use Domain\EventManagement\Models\Event;
use Lorisleiva\Actions\Concerns\AsAction;

class EnablePromoterForEvent
{
    use AsAction;

    public function handle(int $eventId, int $promoterId)
    {
        $event = Event::findOrFail($eventId);

        // Update the pivot to enable the promoter
        $event->promoters()->updateExistingPivot($promoterId, ['enabled' => true]);
    }

    public function asController(int $eventId, int $promoterId)
    {
        $this->handle($eventId, $promoterId);

        return redirect()->back()->with('success', 'Promoter enabled successfully');
    }
}
