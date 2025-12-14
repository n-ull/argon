<?php

namespace App\Modules\EventManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\EventManagement\Requests\StoreOrUpdateEventSettingsRequest;
use Domain\EventManagement\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Sleep;

class UpdateEventSettingsController extends Controller
{
    public function __invoke(StoreOrUpdateEventSettingsRequest $request, int $eventId)
    {
        $validated = $request->validated();

        dd($validated);
        $event = Event::findOrFail($eventId);

        $event->update($request->all());

        return redirect()->route('manage.event.settings', $event->id);
    }
}
