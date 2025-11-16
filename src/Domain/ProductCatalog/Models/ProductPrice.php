<?php

namespace Domain\ProductCatalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPrice extends Model
{
    protected $fillable = [
        "product_id",
        "price",
        "label",
        "start_sale_date",
        "end_sale_date",
        "stock",
        "quantity_sold",
        "is_hidden",
        "sort_order",
    ];
    
    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }
}
