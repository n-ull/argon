<?php

namespace Domain\OrganizerManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MercadoPagoAccount extends Model
{
    protected $fillable = [
        'user_id',
        'access_token',
        'public_key',
        'code',
        'refresh_token',
        'expires_in',
        'mp_user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
