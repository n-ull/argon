<?php

namespace Domain\Ordering\Models;

use Domain\EventManagement\Models\Event;
use Domain\Ordering\Enums\VoucherType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    protected $fillable = [
        'event_id',
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'total_limit',
        'used_count',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'type' => VoucherType::class,
        'value' => 'float',
        'min_order_amount' => 'float',
        'max_discount_amount' => 'float',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saving(function (Voucher $voucher) {
            $voucher->code = strtoupper($voucher->code);
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
