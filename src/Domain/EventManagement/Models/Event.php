<?php

namespace Domain\EventManagement\Models;

use Domain\EventManagement\Casts\LocationInfoJson;
use Domain\EventManagement\Enums\EventStatus;
use Domain\EventManagement\Policies\EventPolicy;
use Domain\Ordering\Enums\OrderStatus;
use Domain\Ordering\Models\Order;
use Domain\Ordering\Models\OrderItem;
use Domain\OrganizerManagement\Models\Organizer;
use Domain\ProductCatalog\Models\Product;
use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Models\Ticket;
use Domain\Ticketing\Models\Doormen;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Event Model
 */
#[UsePolicy(EventPolicy::class)]
class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'events';

    protected $fillable = [
        'title',
        'description',
        'location_info',
        'status',
        'start_date',
        'end_date',
        'event_category_id',
        'organizer_id',
        'slug',
        'cover_image_path',
        'poster_image_path',
        'is_featured',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'status' => EventStatus::class,
        'location_info' => LocationInfoJson::class,
        'is_featured' => 'boolean',
    ];

    protected $appends = [
        'orders_count',
    ];

    public function getOrdersCountAttribute()
    {
        return $this->orders()->count();
    }

    public function getWidgetStatsAttribute()
    {
        return [
            'completed_orders_count' => $this->orders()->where('status', OrderStatus::COMPLETED)->count(),
            'total_revenue' => (float) $this->orders()->where('status', OrderStatus::COMPLETED)->sum('subtotal'),
            'unique_visitors' => $this->statistics->unique_visitors,
            'products_sold_count' => OrderItem::whereHas('order', function ($query) {
                $query->where('event_id', $this->id)
                    ->where('status', OrderStatus::COMPLETED);
            })->sum('quantity'),
            'scanned_tickets_count' => $this->tickets()->where('status', TicketStatus::USED)->count(),
            'courtesy_tickets_count' => $this->tickets()->where('is_courtesy', true)->count(),
            'generated_tickets_count' => $this->tickets()->count(),
        ];
    }

    public function statistics(): HasOne
    {
        return $this->hasOne(EventStatistics::class)->withDefault([
            'unique_visitors' => 0,
        ]);
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('sort_order');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function combos(): HasMany
    {
        return $this->hasMany(\Domain\ProductCatalog\Models\Combo::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function doormen(): HasMany
    {
        return $this->hasMany(Doormen::class);
    }

    public function ticketInvitations(): HasMany
    {
        return $this->hasMany(\Domain\Ticketing\Models\TicketInvitation::class);
    }

    public function courtesies()
    {
        return $this->tickets()->where('is_courtesy', true);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(EventFormQuestion::class);
    }
    
    public function taxesAndFees(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(TaxAndFee::class, 'event_tax_and_fee')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function newFactory()
    {
        return \Database\Factories\Domain\EventManagement\Models\EventFactory::new();
    }
}
