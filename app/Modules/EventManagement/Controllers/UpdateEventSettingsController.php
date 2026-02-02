<?php

namespace App\Modules\EventManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\EventManagement\Requests\StoreOrUpdateEventSettingsRequest;
use Domain\EventManagement\Models\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class UpdateEventSettingsController extends Controller
{
    public function __invoke(StoreOrUpdateEventSettingsRequest $request, int $eventId)
    {

        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['title'], '-', 'es');

        $event = Event::findOrFail($eventId);

        Gate::authorize('update', $event);

        $event->update($validated);

        if (isset($validated['taxes_and_fees'])) {
            $event->taxesAndFees()->sync($validated['taxes_and_fees']);
        }

        return redirect()->route('manage.event.settings', $event->id)->with('message', flash_success(
            'Event settings updated.',
            'Your event settings has been updated successfully.'
        ));
    }
}
