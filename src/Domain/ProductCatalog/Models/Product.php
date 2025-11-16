<?php

namespace Domain\ProductCatalog\Models;

use Domain\ProductCatalog\Enums\ProductType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Product model representing items available for purchase
 *
 * @property string $name Product name
 * @property string $description Product description
 * @property int $max_per_order Maximum quantity per order
 * @property int $min_per_order Minimum quantity per order
 * @property ProductType $product_type Type of product
 * @property bool $hide_before_sale_start_date Whether to hide product before sale starts
 * @property bool $hide_after_sale_end_date Whether to hide product after sale ends
 * @property bool $hide_when_sold_out Whether to hide product when sold out
 * @property bool $show_stock Whether to display stock information
 * @property \Carbon\Carbon $start_sale_date When product sale begins
 * @property \Carbon\Carbon $end_sale_date When product sale ends
 * @property int $event_id Associated event ID
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\ProductCatalog\Models\ProductPrice> $product_prices
 * @property-read int|null $product_prices_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereEndSaleDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereHideAfterSaleEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereHideBeforeSaleStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereHideWhenSoldOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMaxPerOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMinPerOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereShowStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStartSaleDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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

    protected $casts = [
        "product_type" => ProductType::class
    ];

    public function product_prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class)->orderBy('sort_order');
    }
}
