<?php

namespace Domain\Ordering\Services;

use Domain\EventManagement\Enums\DisplayMode;
use Domain\EventManagement\Enums\TaxFeeType;
use Domain\EventManagement\Models\Event;
use Domain\Ordering\Data\OrderItemData;
use Domain\Ordering\Data\PriceBreakdown;
use Domain\Ordering\Enums\VoucherType;
use Domain\Ordering\Models\Voucher;

class PriceCalculationService
{
    /**
     * Calculate complete price breakdown for an order
     */
    public function calculate(
        array $items,
        Event $event,
        ?string $paymentGateway = null,
        ?string $voucherCode = null
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

        $integratedTaxes = $taxes->where('display_mode', DisplayMode::INTEGRATED);
        $separatedTaxes = $taxes->where('display_mode', DisplayMode::SEPARATED);

        // Calculate Integrated Tax Amount
        $integratedKeys = $integratedTaxes->pluck('id')->toArray();
        // Recalculate subtotal to include integrated taxes and voucher discount
        $baseSubtotal = $this->calculateSubtotal($items);

        // Voucher Logic
        $voucher = null;
        $voucherDiscount = 0.0;
        if ($voucherCode) {
            $voucher = Voucher::where('event_id', $event->id)
                ->where('code', strtoupper($voucherCode))
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                })
                ->first();

            if ($voucher) {
                if ($voucher->min_order_amount && $baseSubtotal < $voucher->min_order_amount) {
                    $voucher = null;
                } else {
                    $voucherDiscount = $this->calculateVoucherDiscount($voucher, $baseSubtotal);
                }
            }
        }

        $discountFactor = $baseSubtotal > 0 ? ($baseSubtotal - $voucherDiscount) / $baseSubtotal : 1.0;

        $subtotal = array_reduce(
            $items,
            function ($carry, OrderItemData $item) use ($integratedTaxes, $discountFactor) {
                // Effective unit price after discount
                $unitPrice = $item->unitPrice * $discountFactor;
                // Calculate tax for this item based on discounted price
                $integratedTaxAmount = $integratedTaxes->sum(fn ($tax) => $tax->calculateAmount($unitPrice));
                // Add to carry: (Discounted Base + Tax) * Quantity
                return $carry + (($unitPrice + $integratedTaxAmount) * $item->quantity);
            },
            0.0
        );

        // Calculate Separated Taxes and Fees on discounted base subtotal
        $discountedBaseSubtotal = $baseSubtotal - $voucherDiscount;

        $taxesTotal = $this->calculateTaxesAndFees($discountedBaseSubtotal, $separatedTaxes);
        $feesTotal = $this->calculateTaxesAndFees($discountedBaseSubtotal, $fees);

        // Calculate Organizer Service Fee (User requested: "integrated to the price of all stuff")
        // Implementation: Fee on the Effective Subtotal (which includes Integrated Taxes)
        $serviceFeePercentage = $event->organizer->settings->service_fee ?? 10.0;
        $serviceFeeAmount = $subtotal * ($serviceFeePercentage / 100);

        // Add Service Fee to totals
        $feesTotal += $serviceFeeAmount;

        // Create snapshots
        $itemsSnapshot = $this->createItemsSnapshot($items, $event, $discountFactor);
        // Note: taxesSnapshot should include ALL taxes for reference
        $taxesSnapshot = $this->createTaxFeeSnapshot($discountedBaseSubtotal, $taxes);
        $feesSnapshot = $this->createTaxFeeSnapshot($discountedBaseSubtotal, $fees);

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

        $voucherSnapshot = $voucher ? [
            'id' => $voucher->id,
            'code' => $voucher->code,
            'type' => $voucher->type->value,
            'value' => $voucher->value,
        ] : null;

        return new PriceBreakdown(
            subtotal: $subtotal, // Now includes integrated taxes
            taxesTotal: $taxesTotal, // Only separated taxes
            feesTotal: $feesTotal,
            totalBeforeAdditions: $totalBeforeAdditions,
            totalGross: $totalGross,
            itemsSnapshot: $itemsSnapshot,
            taxesSnapshot: $taxesSnapshot,
            feesSnapshot: $feesSnapshot,
            serviceFeeSnapshot: $serviceFeeAmount,
            voucherDiscount: $voucherDiscount,
            voucherId: $voucher?->id,
            voucherSnapshot: $voucherSnapshot,
        );
    }

    /**
     * Calculate discount amount for a voucher
     */
    private function calculateVoucherDiscount(Voucher $voucher, float $subtotal): float
    {
        if ($voucher->type === VoucherType::FIXED) {
            return min((float) $voucher->value, $subtotal);
        }

        if ($voucher->type === VoucherType::PERCENTAGE) {
            $discount = $subtotal * ($voucher->value / 100);
            if ($voucher->max_discount_amount) {
                $discount = min($discount, (float) $voucher->max_discount_amount);
            }
            return (float) $discount;
        }

        return 0.0;
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
     * Create snapshot of order items with integrated tax adjustments and discounts
     */
    private function createItemsSnapshot(array $items, Event $event, float $discountFactor = 1.0): array
    {
        // Get integrated taxes
        $integratedTaxes = $event->taxesAndFees()
            ->where('is_active', true)
            ->where('display_mode', DisplayMode::INTEGRATED)
            ->get();

        return array_map(
            function (OrderItemData $item) use ($integratedTaxes, $discountFactor) {
                // Effective unit price after discount
                $unitPrice = $item->unitPrice * $discountFactor;

                // Calculate total integrated tax amount per unit based on discounted price
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
