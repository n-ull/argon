<?php

namespace Domain\Promoters\Models;

use App\Models\User;
use Domain\EventManagement\Models\Event;
use Domain\Promoters\Exceptions\ActiveInvitationAlreadyExists;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promoter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'referral_code',
        'enabled',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'promoter_events', 'promoter_id', 'event_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(PromoterInvitation::class);
    }

    public function invite(string $email): void
    {
        if ($this->invitations()->hasActiveInvitation()) {
            throw new ActiveInvitationAlreadyExists();
        }

        $this->invitations()->create([
            'email' => $email,
            'status' => 'pending',
            'token' => \Str::random(60),
        ]);
    }

    protected static function newFactory()
    {
        return \Database\Factories\Domain\Promoters\Models\PromoterFactory::new();
    }
}
