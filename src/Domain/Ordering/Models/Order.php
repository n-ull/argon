<?php

namespace Domain\Ordering\Models;

use Domain\EventManagement\Models\Event;
use Domain\Ordering\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $event_id
 * @property string $total_before_additions
 * @property string $total_gross
 * @property string $status
 * @property string $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Ordering\Models\OrderItem> $order_items
 * @property-read int|null $order_items_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalBeforeAdditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalGross($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id',
        'subtotal',
        'taxes_total',
        'fees_total',
        'items_snapshot',
        'taxes_snapshot',
        'fees_snapshot',
        'status',
        'reference_id',
        'organizer_raise_method_snapshot',
        'used_payment_gateway_snapshot',
        'user_id',
        'expires_at',
    ];

    protected $casts = [
        'items_snapshot' => 'array',
        'taxes_snapshot' => 'array',
        'fees_snapshot' => 'array',
        'expires_at' => 'datetime',
        'status' => OrderStatus::class,
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    protected static function newFactory()
    {
        return \Database\Factories\Domain\Ordering\Models\OrderFactory::new();
    }
}
