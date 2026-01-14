<?php

namespace Domain\Ticketing\Models;

use App\Models\User;
use Domain\EventManagement\Models\Event;
use Domain\Ordering\Models\Order;
use Domain\ProductCatalog\Models\Product;
use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Enums\TicketType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'token',
        'type',
        'event_id',
        'user_id',
        'order_id',
        'product_id',
        'status',
        'transfers_left',
        'is_courtesy',
        'used_at',
        'expired_at',
    ];

    protected $casts = [
        'transfers_left' => 'integer',
        'is_courtesy' => 'boolean',
        'used_at' => 'datetime',
        'expired_at' => 'datetime',
        'type' => TicketType::class,
        'status' => TicketStatus::class,
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function givenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'given_by');
    }
}
