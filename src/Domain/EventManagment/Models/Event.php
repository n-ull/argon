<?php

namespace Domain\EventManagment\Models;

use Domain\OrganizerManagment\Models\Organizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $guarded = [];

    public function organizer() : BelongsTo {
        return $this->belongsTo(Organizer::class);
    }
}
