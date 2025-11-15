<?php

namespace Domain\OrganizerManagment\Models;

use Domain\EventManagment\Models\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organizer extends Model
{
    protected $guarded = [];

    public function Events() : HasMany {
        return $this->hasMany(Event::class);
    }
}
