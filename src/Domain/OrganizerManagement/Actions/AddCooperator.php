<?php

namespace Domain\OrganizerManagement\Actions;

use App\Models\User;
use Domain\OrganizerManagement\Models\Organizer;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class AddCooperator
{
    use AsAction;

    public function handle(string $email, Organizer $organizer)
    {
        $user = User::where('email', $email)->first();

        if ($organizer->users()->where('user_id', $user->id)->exists()) {
            throw new \Exception(__('organizer.cooperator_already_exists'));
        }

        $organizer->users()->attach($user->id);

        return $user;
    }

    public function asController(Request $request, Organizer $organizer)
    {
        $data = $request->validate([
            "email" => "required|email|exists:users,email"
        ]);

        $this->handle($data['email'], $organizer);

        return back();
    }
}
