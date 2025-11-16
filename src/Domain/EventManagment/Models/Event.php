<?php

namespace Domain\EventManagment\Models;

use Domain\EventManagment\Casts\LocationInfoJson;
use Domain\EventManagment\Enums\EventStatus;
use Domain\OrganizerManagment\Models\Organizer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        "title",
        "description",
        "location_info",
        "status",
        "start_date",
        "end_date",
        "organizer_id",
        "is_featured",
        "slug",
    ];

    protected $casts = [
        "start_date" => "datetime",
        "end_date" => "datetime",
        "status" => EventStatus::class,
        "location_info" => LocationInfoJson::class,
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }
}
