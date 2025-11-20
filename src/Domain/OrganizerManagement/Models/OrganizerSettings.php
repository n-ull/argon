<?php

namespace Domain\OrganizerManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Domain\OrganizerManagement\Models\Organizer|null $organizer
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizerSettings query()
 *
 * @mixin \Eloquent
 */
class OrganizerSettings extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'organizer_id',
        'raise_money_method',
        'raise_money_account',
        'is_modo_active',
        'is_mercadopago_active',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }
}
