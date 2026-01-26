<?php

namespace Domain\Promoters\Models;

use Domain\EventManagement\Models\Event;
use Domain\Ordering\Models\Order;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = [
        'promoter_id',
        'order_id',
        'event_id',
        'amount',
        'status',
    ];

    public function promoter()
    {
        return $this->belongsTo(Promoter::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    #[Scope]
    public function completed($query)
    {
        return $query->whereHas('order', function ($query) {
            $query->where('status', 'completed');
        });
    }
}
