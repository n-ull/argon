<?php

namespace Domain\ProductCatalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    public function product_prices(): HasMany {
        return $this->hasMany(ProductPrice::class)->orderBy('sort_order');
    }
}
