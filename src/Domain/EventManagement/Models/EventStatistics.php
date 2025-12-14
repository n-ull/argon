<?php

namespace Domain\EventManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventStatistics extends Model
{
    protected $fillable = [
        'event_id',
        'unique_visitors',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
