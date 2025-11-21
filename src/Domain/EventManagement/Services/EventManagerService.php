<?php

namespace Domain\EventManagement\Services;

use Domain\EventManagement\Models\Event;

class EventManagerService
{
    public function createEvent(array $data): Event
    {
        return Event::create($data);
    }
}
