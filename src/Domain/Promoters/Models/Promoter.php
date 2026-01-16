<?php

namespace Domain\Promoters\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Promoter extends Model
{
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
}
