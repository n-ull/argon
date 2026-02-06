<?php

namespace Domain\Ticketing\Models;

use App\Models\User;
use Domain\EventManagement\Models\Event;
use Illuminate\Database\Eloquent\Model;

class Doormen extends Model
{
    protected $fillable = [
        'user_id',
        'event_id',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
