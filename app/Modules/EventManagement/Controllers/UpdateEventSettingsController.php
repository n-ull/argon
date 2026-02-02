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

        if ($request->hasFile('cover_image')) {
            $validated['cover_image_path'] = $request->file('cover_image')->store('events/covers', 'public');
        }

        if ($request->hasFile('poster_image')) {
            $validated['poster_image_path'] = $request->file('poster_image')->store('events/posters', 'public');
        }

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
