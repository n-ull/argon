<?php

namespace Domain\OrganizerManagement\Actions;

use Domain\OrganizerManagement\Models\Organizer;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrganizer
{
    use AsAction;

    public function handle(array $data)
    {
        $organizer = Organizer::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'owner_id' => auth()->user()->id,
        ]);

        auth()->user()->organizers()->attach($organizer);

        return $organizer;
    }
}
