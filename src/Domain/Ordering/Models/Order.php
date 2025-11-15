<?php

namespace Domain\Ordering\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public function order_items(): HasMany {
        return $this->hasMany(OrderItem::class);
    }
}
