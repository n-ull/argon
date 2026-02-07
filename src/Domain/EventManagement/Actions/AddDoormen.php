<?php

namespace Domain\EventManagement\Actions;

use Domain\EventManagement\Models\Event;
use Domain\Ticketing\Models\Doormen;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class AddDoormen
{
    use AsAction;

    public function handle(Event $event, array $emails)
    {
        $users = User::whereIn('email', $emails)->get();

        foreach ($users as $user) {
            // Check if already exists
            $exists = Doormen::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->exists();

            if (! $exists) {
                Doormen::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'is_active' => true,
                ]);
            }
        }
    }
}
