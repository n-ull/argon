<?php

namespace Domain\Promoters\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Summary of PromoterInvitation
 * @property-read bool $hasActiveInvitation
 * @property-read int $id
 * @property-read int $promoter_id
 * @property-read string $email
 * @property-read string $status
 * @property-read string $token
 * @property-read \Illuminate\Support\Carbon $created_at
 * @property-read \Illuminate\Support\Carbon $updated_at
 */
class PromoterInvitation extends Model
{
    protected $fillable = [
        'promoter_id',
        'event_id',
        'email',
        'status',
        'token',
        'commission_type',
        'commission_value',
    ];

    public function getHasActiveInvitationAttribute()
    {
        return $this->where('email', $this->email)->where('status', 'pending')->exists();
    }

    public function promoter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Promoter::class);
    }

    public function event(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Domain\EventManagement\Models\Event::class);
    }
}
