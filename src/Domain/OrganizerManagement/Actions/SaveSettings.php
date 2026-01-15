<?php

namespace Domain\OrganizerManagement\Actions;

use Domain\OrganizerManagement\Models\Organizer;
use Domain\OrganizerManagement\Models\OrganizerSettings;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class SaveSettings
{
    use AsAction;

    public function handle(int $organizerId, array $data)
    {
        $organizer = Organizer::findOrFail($organizerId);

        // Split data between Organizer and OrganizerSettings
        $organizerFields = ['name', 'email', 'phone', 'logo'];
        $organizerData = array_intersect_key($data, array_flip($organizerFields));

        if (! empty($organizerData)) {
            $organizer->update($organizerData);
        }

        $settingsFields = ['raise_money_method', 'raise_money_account', 'is_modo_active', 'is_mercadopago_active'];
        $settingsData = array_intersect_key($data, array_flip($settingsFields));

        if (! empty($settingsData)) {
            $organizerSettings = OrganizerSettings::firstOrCreate([
                'organizer_id' => $organizerId,
            ]);
            $organizerSettings->update($settingsData);
        }
    }

    public function asController(int $organizerId, Request $request)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|nullable|email|max:255',
            'phone' => 'sometimes|nullable|string|max:255',
            'logo' => 'sometimes|nullable|image|max:2048',
            'raise_money_method' => 'sometimes|required|in:internal,split',
            'raise_money_account' => 'sometimes|nullable|string|max:255',
            'is_modo_active' => 'sometimes|required|boolean',
            'is_mercadopago_active' => 'sometimes|required|boolean',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('organizers/logos', 'public');
        }

        $this->handle($organizerId, $data);

        return back()->with('message', flash_success('Settings updated successfully', 'Your settings have been updated.'));
    }
}
