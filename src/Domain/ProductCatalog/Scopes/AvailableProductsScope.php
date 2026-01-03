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
                $query->where(function (Builder $q) {
                    // Show if: no start date, OR start date has passed, OR we don't hide before start date
                    $q->whereRaw('COALESCE(products.start_sale_date, events.start_date) IS NULL')
                        ->orWhereRaw('COALESCE(products.start_sale_date, events.start_date) <= ?', [now()])
                        ->orWhere('products.hide_before_sale_start_date', false);
                });

                // End Date Logic
                $query->where(function (Builder $q) {
                    // Show if: (end date is null OR end date >= now) OR we don't hide after end date
                    $q->where(function ($sub) {
                        $sub->whereRaw('COALESCE(products.end_sale_date, events.end_date) IS NULL')
                            ->orWhereRaw('COALESCE(products.end_sale_date, events.end_date) >= ?', [now()]);
                    })->orWhere('products.hide_after_sale_end_date', false);
                });
            })
            ->where(function (Builder $query) {
                // Stock Logic
                $query->where('products.show_stock', false)
                    ->orWhere(function (Builder $q) {
                    $q->where('products.show_stock', true)
                        ->whereHas('product_prices', function (Builder $subQuery) {
                            $subQuery->where(function ($sq) {
                                $sq->whereNull('stock')
                                    ->orWhereRaw('stock > quantity_sold');
                            });
                        });
                });
            })
            ->where('products.is_hidden', false)
            ->select('products.*'); // Ensure we select products columns to avoid ambiguity or missing fields
    }
}
