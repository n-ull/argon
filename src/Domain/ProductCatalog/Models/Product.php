<?php

namespace Domain\ProductCatalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        "name",
        "description",
        "max_per_order",
        "min_per_order",
        "product_type",
        "hide_before_sale_start_date",
        "hide_after_sale_end_date",
        "hide_when_sold_out",
        "show_stock",
        "start_sale_date",
        "end_sale_date",
        "event_id",
    ];

    public function product_prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class)->orderBy('sort_order');
    }
}
