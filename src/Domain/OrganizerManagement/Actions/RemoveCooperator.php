<?php

namespace Domain\OrganizerManagement\Actions;

use App\Models\User;
use Domain\OrganizerManagement\Models\Organizer;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveCooperator
{
    use AsAction;

    public function handle($user, $organizer)
    {
        if($organizer->owner_id == $user->id){
            throw new \Exception(__('organizer.you_cant_remove_owner'));
        }

        $organizer->users()->detach($user->id);
    }

    public function asController(Request $request, Organizer $organizer, User $user)
    {
        $this->handle($user, $organizer);

        return back();
    }
}
