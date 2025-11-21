<?php

namespace Domain\ProductCatalog\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class AvailableProductsScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->leftJoin('events', 'products.event_id', '=', 'events.id')
            ->where(function (Builder $query) {
                // Start Date Logic
                $query->whereRaw('COALESCE(products.start_sale_date, events.start_date) <= ?', [now()]);

                // End Date Logic
                $query->where(function (Builder $q) {
                    $q->whereRaw('COALESCE(products.end_sale_date, events.end_date) >= ?', [now()])
                      ->orWhereRaw('COALESCE(products.end_sale_date, events.end_date) IS NULL');
                });
            })
            ->where(function (Builder $query) {
                // Stock Logic
                $query->where('products.show_stock', false)
                      ->orWhere(function (Builder $q) {
                          $q->where('products.show_stock', true)
                            ->whereHas('product_prices', function (Builder $subQuery) {
                                $subQuery->whereRaw('COALESCE(stock, 999999999) > quantity_sold');
                            });
                      });
            })
            ->select('products.*'); // Ensure we select products columns to avoid ambiguity or missing fields
    }
}
