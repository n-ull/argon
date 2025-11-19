<?php

namespace Domain\Ordering\Data;

class PriceBreakdown
{
    public function __construct(
        public readonly float $subtotal,
        public readonly float $taxesTotal,
        public readonly float $feesTotal,
        public readonly float $totalBeforeAdditions,
        public readonly float $totalGross,
        public readonly array $itemsSnapshot,
        public readonly array $taxesSnapshot,
        public readonly array $feesSnapshot,
    ) {}

    public function toArray(): array
    {
        return [
            'subtotal' => $this->subtotal,
            'taxes_total' => $this->taxesTotal,
            'fees_total' => $this->feesTotal,
            'total_before_additions' => $this->totalBeforeAdditions,
            'total_gross' => $this->totalGross,
            'items_snapshot' => $this->itemsSnapshot,
            'taxes_snapshot' => $this->taxesSnapshot,
            'fees_snapshot' => $this->feesSnapshot,
        ];
    }
}
