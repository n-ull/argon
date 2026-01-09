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
            ->join('events', 'products.event_id', '=', 'events.id')
            ->where('product_prices.is_hidden', false)
            ->where(function (Builder $query) {
                $query->where(function ($q) {
                    // Show if: (start date is null OR start date <= now) OR we don't hide before start date
                    $q->where(function ($sub) {
                        $sub->whereRaw('COALESCE(product_prices.start_sale_date, products.start_sale_date, events.start_date) IS NULL')
                            ->orWhereRaw('COALESCE(product_prices.start_sale_date, products.start_sale_date, events.start_date) <= ?', [now()]);
                    })->orWhere('products.hide_before_sale_start_date', false);
                })->where(function ($q) {
                    // Show if: (end date is null OR end date >= now) OR we don't hide after end date
                    $q->where(function ($sub) {
                        $sub->whereRaw('COALESCE(product_prices.end_sale_date, products.end_sale_date, events.end_date) IS NULL')
                            ->orWhereRaw('COALESCE(product_prices.end_sale_date, products.end_sale_date, events.end_date) >= ?', [now()]);
                    })->orWhere('products.hide_after_sale_end_date', false);
                });
            })
            ->where(function (Builder $query) {
                // Stock Visibility Logic
                $query->where('products.hide_when_sold_out', false)
                    ->orWhere(function ($q) {
                    $q->whereNull('product_prices.stock')
                        ->orWhereRaw('product_prices.stock > product_prices.quantity_sold');
                });
            })
            ->select('product_prices.*');
    }
}
