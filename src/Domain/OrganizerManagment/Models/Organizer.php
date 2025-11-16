<?php

namespace Domain\OrganizerManagment\Models;

use Domain\EventManagment\Models\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Organizer extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
        "email",
        "phone",
        "logo",
    ];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function settings(): HasOne {
        return $this->hasOne(OrganizerSettings::class);
    }
}
