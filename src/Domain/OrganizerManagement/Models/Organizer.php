<?php

namespace Domain\OrganizerManagement\Models;

use App\Models\User;
use Domain\EventManagement\Models\Event;
use Domain\EventManagement\Models\TaxAndFee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $logo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Event> $events
 * @property-read int|null $events_count
 * @property-read \Domain\OrganizerManagement\Models\OrganizerSettings|null $settings
 *
 * @method static \Domain\OrganizerManagement\Database\Factories\OrganizerFactory factory($count = null, $state = [])
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
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'logo',
        'owner_id',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(OrganizerSettings::class)->withDefault([
            'service_fee' => 10,
            'raise_money_method' => 'split',
            'is_modo_active' => false,
            'is_mercadopago_active' => false,
        ]);
    }

    public function taxesAndFees(): HasMany
    {
        return $this->hasMany(TaxAndFee::class);
    }

    protected static function newFactory()
    {
        return \Database\Factories\Domain\OrganizerManagement\Models\OrganizerFactory::new();
    }
}
