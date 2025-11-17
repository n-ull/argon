<?php

namespace Domain\EventManagment\Actions;

use App\Modules\EventManagment\Requests\CreateEventRequest;
use Domain\EventManagment\Services\EventManagerService;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateEvent
{
    use AsAction;

    public function __construct(
        private EventManagerService $eventManager
    ) {}

    public function handle(CreateEventRequest $request)
    {
        $this->eventManager->createEvent();
    }
}
