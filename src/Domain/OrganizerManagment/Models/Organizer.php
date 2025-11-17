<?php

namespace Domain\OrganizerManagment\Models;

use Domain\EventManagment\Models\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $logo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Event> $events
 * @property-read int|null $events_count
 * @property-read \Domain\OrganizerManagment\Models\OrganizerSettings|null $settings
 *
 * @method static \Domain\OrganizerManagment\Database\Factories\OrganizerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Eventse\Eloquent\Builder<static>|Organizer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizer whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Organizer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'logo',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(OrganizerSettings::class);
    }
}
