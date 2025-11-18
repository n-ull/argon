<?php

namespace Domain\EventManagement\Actions;

use App\Modules\EventManagement\Requests\StoreEventRequest;
use Domain\EventManagement\Services\EventManagerService;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateEvent
{
    use AsAction;

    public function __construct(
        private EventManagerService $eventManager
    ) {}

    public function handle(array $data)
    {
        return $this->eventManager->createEvent($data);
    }
}
