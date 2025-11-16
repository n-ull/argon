<?php

namespace Domain\EventManagment\Models;

use Domain\EventManagment\Casts\LocationInfoJson;
use Domain\EventManagment\Enums\EventStatus;
use Domain\OrganizerManagment\Models\Organizer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Event Model
 * 
 * Represents an event in the event management system.
 * 
 * @property int $id
 * @property string $title The title of the event
 * @property string|null $description The description of the event
 * @property array $location_info JSON object containing location information
 * @property EventStatus $status The current status of the event
 * @property \Carbon\Carbon $start_date The start date and time of the event
 * @property \Carbon\Carbon $end_date The end date and time of the event
 * @property int $organizer_id The ID of the organizer
 * @property bool $is_featured Whether the event is featured
 * @property string $slug The URL slug for the event
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * 
 * @property-read Organizer $organizer The organizer of this event
 */
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
