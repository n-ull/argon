<?php

namespace App\Modules\EventManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\EventManagement\Requests\StoreOrUpdateEventSettingsRequest;
use Domain\EventManagement\Models\Event;

class UpdateEventSettingsController extends Controller
{
    public function __invoke(StoreOrUpdateEventSettingsRequest $request, int $eventId)
    {
        $validated = $request->validated();

        $event = Event::findOrFail($eventId);

        $event->update($validated);

        return redirect()->route('manage.event.settings', $event->id)->with('message', flash_success(
            'Event settings updated.',
            'Your event settings has been updated successfully.'
        ));
    }
}
