<?php

namespace Domain\Promoters\Models;

use Domain\EventManagement\Models\Event;
use Domain\Promoters\Models\Promoter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromoterEvent extends Model
{
    protected $table = 'promoter_events';

    protected $fillable = [
        'promoter_id',
        'event_id',
        'commission_type',
        'commission_value',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function promoter(): BelongsTo
    {
        return $this->belongsTo(Promoter::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
