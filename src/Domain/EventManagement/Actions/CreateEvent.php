<?php

namespace Domain\EventManagement\Actions;

use Domain\EventManagement\Events\EventCreated;
use Domain\EventManagement\Models\Event;
use Domain\EventManagement\Services\EventManagerService;
use Domain\OrganizerManagement\Models\Organizer;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateEvent
{
    use AsAction;

    public function __construct(
        private EventManagerService $eventManager
    ) {}

    public function handle(Organizer $organizer, array $validatedData): Event
    {
        $validatedData['organizer_id'] = $organizer->id;
        $event = $this->eventManager->createEvent($validatedData);

        event(new EventCreated($event));

        return $event;
    }

    public function asController(Organizer $organizer, Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
        ]);

        $event = $this->handle($organizer, $data);

        return redirect()->route('manage.event.dashboard', $event->id)
            ->with('message', flash_success('Event created successfully', 'Your event has been created.'));
    }
}
