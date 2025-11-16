<?php

namespace Domain\ProductCatalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductPrice model represents pricing information for products.
 * 
 * This model handles product pricing data including regular prices,
 * sale dates, stock quantities, and visibility settings.
 *
 * @property-read int $product_id
 * @property-read double $price
 * @property-read string $label
 * @property-read \Illuminate\Support\Carbon|null $start_sale_date
 * @property-read \Illuminate\Support\Carbon|null $end_sale_date
 * @property-read int|null $stock
 * @property-read int $quantity_sold
 * @property-read bool $is_hidden
 * @property-read int $sort_order
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read Product $product
 * @property int $id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereEndSaleDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereIsHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereQuantitySold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereStartSaleDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPrice whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
