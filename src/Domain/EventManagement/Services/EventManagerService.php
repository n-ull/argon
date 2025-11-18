<?php

namespace Domain\EventManagement\Services;

use Domain\EventManagement\Events\EventCreated;
use Domain\EventManagement\Models\Event;

class EventManagerService
{
    public function createEvent(array $data) {
        $event = Event::create($data);

        event(new EventCreated($event));

        return $event;
    }
}
