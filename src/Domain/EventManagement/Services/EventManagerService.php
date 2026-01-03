<?php

namespace Domain\EventManagement\Services;

use Domain\EventManagement\Models\Event;

class EventManagerService
{
    public function createEvent(array $data): Event
    {
        return Event::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'slug' => \Str::slug($data['title']),
            'organizer_id' => $data['organizer_id'],
        ]);
    }
}
