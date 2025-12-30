<?php

namespace Domain\ProductCatalog\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AvailableProductPricesScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->join('products', 'product_prices.product_id', '=', 'products.id')
            ->where('product_prices.is_hidden', false)
            ->where(function (Builder $query) {
                $query->where(function ($q) {
                    // Show if: no start date, OR start date has passed, OR we don't hide before start date
                    $q->whereNull('product_prices.start_sale_date')
                        ->orWhere('product_prices.start_sale_date', '<=', now())
                        ->orWhere('products.hide_before_sale_start_date', false);
                })->where(function ($q) {
                    // Show if: no end date, OR end date hasn't passed, OR we don't hide after end date
                    $q->whereNull('product_prices.end_sale_date')
                        ->orWhere('product_prices.end_sale_date', '>=', now())
                        ->orWhere('products.hide_after_sale_end_date', false);
                });
            })
            ->where(function (Builder $query) {
                $query->whereNull('product_prices.stock')
                    ->orWhereRaw('product_prices.stock > product_prices.quantity_sold')
                    ->orWhere('products.show_stock', true)
                    ->orWhereRaw('products.max_per_order > product_prices.stock');
            })
            ->where(function (Builder $query) {
                $query->where('products.hide_when_sold_out', false)
                    ->orWhereRaw('product_prices.stock > product_prices.quantity_sold');
            })
            ->select('product_prices.*');
    }
}
