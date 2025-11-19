<?php

namespace Domain\Ordering\Services;

use Domain\EventManagement\Enums\DisplayMode;
use Domain\EventManagement\Models\Event;

class PriceDisplayService
{
    /**
     * Get price display information for frontend
     */
    public function getDisplayPrice(Event $event, float $basePrice, ?string $gateway = null): array
    {
        $integratedTaxes = $event->taxesAndFees()
            ->where('display_mode', DisplayMode::INTEGRATED)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $separatedTaxes = $event->taxesAndFees()
            ->where('display_mode', DisplayMode::SEPARATED)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $integratedAmount = 0;
        foreach ($integratedTaxes as $tax) {
            if ($tax->isApplicableToGateway($gateway)) {
                $integratedAmount += $tax->calculateAmount($basePrice);
            }
        }

        $separatedItems = [];
        foreach ($separatedTaxes as $tax) {
            if ($tax->isApplicableToGateway($gateway)) {
                $separatedItems[] = [
                    'name' => $tax->name,
                    'type' => $tax->type->value,
                    'amount' => $tax->calculateAmount($basePrice),
                ];
            }
        }

        return [
            'base_price' => $basePrice,
            'integrated_amount' => $integratedAmount,
            'display_price' => $basePrice + $integratedAmount,
            'separated_items' => $separatedItems,
            'separated_total' => array_sum(array_column($separatedItems, 'amount')),
            'final_total' => $basePrice + $integratedAmount + array_sum(array_column($separatedItems, 'amount')),
        ];
    }
}
