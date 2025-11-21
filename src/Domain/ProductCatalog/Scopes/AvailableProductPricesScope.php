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
                // Date Logic (assuming null means always available for prices, or we could implement inheritance if needed)
                // For now, simple valid range check
                $query->where(function ($q) {
                    $q->whereNull('product_prices.start_sale_date')
                      ->orWhere('product_prices.start_sale_date', '<=', now());
                })->where(function ($q) {
                    $q->whereNull('product_prices.end_sale_date')
                      ->orWhere('product_prices.end_sale_date', '>=', now());
                });
            })
            ->where(function (Builder $query) {
                // Stock Logic based on Product's show_stock
                $query->where('products.show_stock', false)
                      ->orWhere(function (Builder $q) {
                          $q->where('products.show_stock', true)
                            ->whereRaw('COALESCE(product_prices.stock, 999999999) > product_prices.quantity_sold');
                      });
            })
            ->select('product_prices.*');
    }
}
