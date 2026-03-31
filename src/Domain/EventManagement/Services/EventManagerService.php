<?php

namespace Domain\EventManagement\Services;

use Domain\EventManagement\Models\Event;
use Illuminate\Support\Str;

class EventManagerService
{
    public function createEvent(array $data): Event
    {
        return Event::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'slug' => $this->generateUniqueSlug($data['title']),
            'organizer_id' => $data['organizer_id'],
        ]);
    }

    public function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title, '-', 'es');
        $originalSlug = $slug;
        $count = 1;

        while (Event::where('slug', $slug)
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
