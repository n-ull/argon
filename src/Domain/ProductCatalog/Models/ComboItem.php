<?php

namespace Domain\ProductCatalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComboItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'combo_id',
        'product_price_id',
        'quantity',
    ];

    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class);
    }

    public function productPrice(): BelongsTo
    {
        return $this->belongsTo(ProductPrice::class);
    }
}
