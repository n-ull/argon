<?php

namespace Domain\OrganizerManagement\Policies;

use App\Models\User;
use Domain\OrganizerManagement\Models\Organizer;

class OrganizerPolicy
{
    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Organizer $organizer)
    {
        return $organizer->users->contains($user->id);
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Organizer $organizer)
    {
        return $organizer->users->contains($user->id);
    }

    public function delete(User $user, Organizer $organizer)
    {
        return $organizer->owner_id === $user->id;
    }

    public function update_owner_settings(User $user, Organizer $organizer)
    {
        return $organizer->owner_id === $user->id;
    }

    public function restore(User $user, Organizer $organizer)
    {
        return $organizer->owner_id === $user->id;
    }

    public function forceDelete(User $user, Organizer $organizer)
    {
        return $organizer->owner_id === $user->id;
    }
}
