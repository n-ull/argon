<?php

namespace Domain\EventManagement\Actions;

use App\Modules\EventManagement\Requests\StoreEventRequest;
use Domain\EventManagement\Events\EventCreated;
use Domain\EventManagement\Models\Event;
use Domain\EventManagement\Services\EventManagerService;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateEvent
{
    use AsAction;

    public function __construct(
        private EventManagerService $eventManager
    ) {
    }

    public function handle(array $validatedData): Event
    {
        $event = $this->eventManager->createEvent($validatedData);

        event(new EventCreated($event));

        return $event;
    }
}
