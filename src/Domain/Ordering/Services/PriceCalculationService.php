<?php

namespace Domain\Ordering\Services;

use Domain\EventManagement\Enums\DisplayMode;
use Domain\EventManagement\Enums\TaxFeeType;
use Domain\EventManagement\Models\Event;
use Domain\Ordering\Data\OrderItemData;
use Domain\Ordering\Data\PriceBreakdown;

class PriceCalculationService
{
    /**
     * Calculate complete price breakdown for an order
     */
    public function calculate(
        array $items,
        Event $event,
        ?string $paymentGateway = null
    ): PriceBreakdown {
        // Calculate subtotal from items
        $subtotal = $this->calculateSubtotal($items);

        // Get active taxes and fees for this event
        $taxesAndFees = $event->taxesAndFees()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Filter by gateway if specified
        if ($paymentGateway) {
            $taxesAndFees = $taxesAndFees->filter(
                fn ($item) => $item->isApplicableToGateway($paymentGateway)
            );
        }

        // Separate taxes and fees
        $taxes = $taxesAndFees->where('type', TaxFeeType::TAX);
        $fees = $taxesAndFees->where('type', TaxFeeType::FEE);

        // Calculate totals
        $taxesTotal = $this->calculateTaxesAndFees($subtotal, $taxes);
        $feesTotal = $this->calculateTaxesAndFees($subtotal, $fees);

        // Create snapshots
        $itemsSnapshot = $this->createItemsSnapshot($items);
        $taxesSnapshot = $this->createTaxFeeSnapshot($subtotal, $taxes);
        $feesSnapshot = $this->createTaxFeeSnapshot($subtotal, $fees);

        // Calculate totals
        $totalBeforeAdditions = $subtotal;
        $totalGross = $subtotal + $taxesTotal + $feesTotal;

        return new PriceBreakdown(
            subtotal: $subtotal,
            taxesTotal: $taxesTotal,
            feesTotal: $feesTotal,
            totalBeforeAdditions: $totalBeforeAdditions,
            totalGross: $totalGross,
            itemsSnapshot: $itemsSnapshot,
            taxesSnapshot: $taxesSnapshot,
            feesSnapshot: $feesSnapshot,
        );
    }

    /**
     * Calculate subtotal from order items
     */
    private function calculateSubtotal(array $items): float
    {
        return array_reduce(
            $items,
            fn ($carry, OrderItemData $item) => $carry + $item->getSubtotal(),
            0.0
        );
    }

    /**
     * Calculate total amount for taxes or fees
     */
    private function calculateTaxesAndFees($baseAmount, $collection): float
    {
        return $collection->sum(fn ($item) => $item->calculateAmount($baseAmount));
    }

    /**
     * Create snapshot of order items
     */
    private function createItemsSnapshot(array $items): array
    {
        return array_map(
            fn (OrderItemData $item) => $item->toArray(),
            $items
        );
    }

    /**
     * Create snapshot of taxes or fees
     */
    private function createTaxFeeSnapshot(float $baseAmount, $collection): array
    {
        return $collection->map(function ($item) use ($baseAmount) {
            return [
                'id' => $item->id,
                'type' => $item->type->value,
                'name' => $item->name,
                'calculation_type' => $item->calculation_type->value,
                'value' => $item->value,
                'display_mode' => $item->display_mode->value,
                'calculated_amount' => $item->calculateAmount($baseAmount),
            ];
        })->toArray();
    }

    /**
     * Get price display information for frontend
     */
    public function getPriceDisplay(Event $event, float $basePrice, ?string $gateway = null): array
    {
        $taxesAndFees = $event->taxesAndFees()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($gateway) {
            $taxesAndFees = $taxesAndFees->filter(
                fn ($item) => $item->isApplicableToGateway($gateway)
            );
        }

        $integrated = $taxesAndFees->where('display_mode', DisplayMode::INTEGRATED);
        $separated = $taxesAndFees->where('display_mode', DisplayMode::SEPARATED);

        $integratedAmount = $this->calculateTaxesAndFees($basePrice, $integrated);
        $separatedAmount = $this->calculateTaxesAndFees($basePrice, $separated);

        return [
            'base_price' => $basePrice,
            'integrated_amount' => $integratedAmount,
            'display_price' => $basePrice + $integratedAmount,
            'separated_items' => $separated->map(fn ($item) => [
                'name' => $item->name,
                'type' => $item->type->value,
                'amount' => $item->calculateAmount($basePrice),
            ])->toArray(),
            'separated_total' => $separatedAmount,
            'final_total' => $basePrice + $integratedAmount + $separatedAmount,
        ];
    }
}
