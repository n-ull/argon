<?php

namespace Domain\EventManagement\Actions;

use Domain\EventManagement\Enums\EventStatus;
use Domain\EventManagement\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateEventStatus
{
    use AsAction;

    public function handle(Event $event, EventStatus $status): Event
    {
        $event->update([
            'status' => $status,
        ]);

        return $event;
    }

    public function asController($eventId, Request $request)
    {
        $event = Event::where('slug', $eventId)->first() ?? Event::findOrFail($eventId);

        $data = $request->validate([
            'status' => ['required', new Enum(EventStatus::class)],
        ]);

        $this->handle($event, EventStatus::from($data['status']));

        return redirect()->back()->with('message', flash_success(
            'Event status updated.',
            "The event status has been changed to {$data['status']}."
        ));
    }
}
