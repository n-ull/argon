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
        // Separate taxes and fees
        $taxes = $taxesAndFees->where('type', TaxFeeType::TAX);
        $fees = $taxesAndFees->where('type', TaxFeeType::FEE);

        $integratedTaxes = $taxes->where('display_mode', DisplayMode::INTEGRATED);
        $separatedTaxes = $taxes->where('display_mode', DisplayMode::SEPARATED);

        // Calculate Integrated Tax Amount
        $integratedKeys = $integratedTaxes->pluck('id')->toArray();
        // Recalculate subtotal to include integrated taxes
        $subtotal = array_reduce(
            $items,
            function ($carry, OrderItemData $item) use ($integratedTaxes) {
                // Base unit price
                $unitPrice = $item->unitPrice;
                // Calculate tax for this item
                $integratedTaxAmount = $integratedTaxes->sum(fn ($tax) => $tax->calculateAmount($unitPrice));
                // Add to carry: (Base + Tax) * Quantity
                return $carry + (($unitPrice + $integratedTaxAmount) * $item->quantity);
            },
            0.0
        );

        // Calculate Separated Taxes (on Base Price? Or New Price? Usually Base Price for additional taxes)
        // Re-calculating base subtotal for separated calculation if needed, 
        // BUT assuming separated taxes are on the BASE value (standard), not tax-on-tax.
        // We need the original base subtotal for separated tax calculation.
        $baseSubtotal = $this->calculateSubtotal($items);

        $taxesTotal = $this->calculateTaxesAndFees($baseSubtotal, $separatedTaxes);
        $feesTotal = $this->calculateTaxesAndFees($baseSubtotal, $fees); // Fees usually on base? Or subtotal? Standard is base.

        // Calculate Organizer Service Fee (User requested: "integrated to the price of all stuff")
        // Implementation: Fee on the Effective Subtotal (which includes Integrated Taxes)
        $serviceFeePercentage = $event->organizer->settings->service_fee ?? 10.0;
        $serviceFeeAmount = $subtotal * ($serviceFeePercentage / 100);

        // Add Service Fee to totals
        $feesTotal += $serviceFeeAmount;

        // Create snapshots
        $itemsSnapshot = $this->createItemsSnapshot($items, $event);
        // Note: taxesSnapshot should include ALL taxes for reference
        $taxesSnapshot = $this->createTaxFeeSnapshot($baseSubtotal, $taxes);
        $feesSnapshot = $this->createTaxFeeSnapshot($baseSubtotal, $fees);

        // Add Service Fee to Fees Snapshot
        $feesSnapshot[] = [
            'id' => null, // No ID for predefined service fee
            'type' => 'fee',
            'name' => __('argon.service_fee'),
            'calculation_type' => 'percentage',
            'value' => (float) $serviceFeePercentage,
            'display_mode' => 'separated',
            'calculated_amount' => $serviceFeeAmount,
            'is_service_fee' => true,
        ];

        // Calculate totals
        $totalBeforeAdditions = $baseSubtotal; // Preservation of pure base
        $totalGross = $subtotal + $taxesTotal + $feesTotal; // Subtotal (inc integrated) + Separated Taxes + Fees

        return new PriceBreakdown(
            subtotal: $subtotal, // Now includes integrated taxes
            taxesTotal: $taxesTotal, // Only separated taxes
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
    /**
     * Create snapshot of order items with integrated tax adjustments
     */
    private function createItemsSnapshot(array $items, Event $event): array
    {
        // Get integrated taxes
        $integratedTaxes = $event->taxesAndFees()
            ->where('is_active', true)
            ->where('display_mode', DisplayMode::INTEGRATED)
            ->get();

        return array_map(
            function (OrderItemData $item) use ($integratedTaxes) {
                // Base unit price
                $unitPrice = $item->unitPrice;

                // Calculate total integrated tax amount per unit
                $integratedAmountPerUnit = $integratedTaxes->sum(
                    fn ($tax) => $tax->calculateAmount($unitPrice)
                );

                $itemArray = $item->toArray();

                // Add integrated tax info to snapshot
                $itemArray['unit_price_breakdown'] = [
                    'base_price' => $unitPrice,
                    'integrated_tax_amount' => $integratedAmountPerUnit,
                    'final_price' => $unitPrice + $integratedAmountPerUnit,
                ];

                // Update display unit price to include integrated taxes
                $itemArray['unit_price'] = $unitPrice + $integratedAmountPerUnit;
                $itemArray['subtotal'] = ($unitPrice + $integratedAmountPerUnit) * $item->quantity;

                return $itemArray;
            },
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
