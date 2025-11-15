<?php

namespace Domain\ProductCatalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPrice extends Model
{
    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }
}
