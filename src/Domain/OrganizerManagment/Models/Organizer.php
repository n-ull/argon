<?php

namespace Domain\OrganizerManagment\Models;

use Domain\EventManagment\Models\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
