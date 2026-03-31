<?php

namespace Domain\Ticketing\Models;

use App\Models\User;
use Domain\EventManagement\Models\Event;
use Domain\ProductCatalog\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'event_id',
        'product_id',
        'quantity',
        'transfers_left',
        'given_by',
        'ticket_type',
        'expires_at',
        'accepted_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'transfers_left' => 'integer',
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function givenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'given_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }
}
