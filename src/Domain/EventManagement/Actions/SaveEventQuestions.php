<?php

namespace Domain\EventManagement\Actions;

use Domain\EventManagement\Models\Event;
use Domain\EventManagement\Models\EventFormQuestion;

class SaveEventQuestions
{
    /**
     * Save (create or update) a question set for an event.
     *
     * @param  Event  $event
     * @param  array  $data  {applies_to, product_id, is_active, fields}
     * @return EventFormQuestion
     */
    public static function run(Event $event, array $data): EventFormQuestion
    {
        $questionSet = EventFormQuestion::firstOrNew([
            'event_id' => $event->id,
            'applies_to' => $data['applies_to'],
            'product_id' => $data['product_id'] ?? null,
        ]);

        $questionSet->fill([
            'is_active' => $data['is_active'] ?? false,
            'fields' => $data['fields'] ?? [],
        ]);

        $questionSet->save();

        return $questionSet;
    }
}
