<?php

namespace Domain\EventManagement\Services;

use Domain\EventManagement\Models\Event;

class EventValidatorService
{
    public function validate(Event $event): void
    {
        if ($event->start_date->gt($event->end_date)) {
            throw new \DomainException('Event start must be before end');
        }
    }
}
