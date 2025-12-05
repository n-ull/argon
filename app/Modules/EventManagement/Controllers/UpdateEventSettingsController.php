<?php

namespace App\Modules\EventManagement\Controllers;

use App\Http\Controllers\Controller;
use Domain\EventManagement\Models\Event;
use Illuminate\Http\Request;

class UpdateEventSettingsController extends Controller
{
    public function __invoke(Request $request, int $eventId)
    {
        dd($request->all());
        $event = Event::findOrFail($eventId);

        $event->update($request->all());

        return redirect()->route('manage.event.settings', $event->id);
    }
}
