<?php

namespace Domain\Promoters\Models;

use Domain\Ordering\Models\Order;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = [
        'promoter_id',
        'order_id',
    ];

    public function promoter()
    {
        return $this->belongsTo(Promoter::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
