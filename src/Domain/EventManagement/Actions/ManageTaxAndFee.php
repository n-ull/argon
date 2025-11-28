<?php

namespace Domain\EventManagement\Actions;

use Domain\EventManagement\Enums\CalculationType;
use Domain\EventManagement\Enums\DisplayMode;
use Domain\EventManagement\Enums\TaxFeeType;
use Domain\EventManagement\Models\Event;
use Domain\EventManagement\Models\TaxAndFee;
use Lorisleiva\Actions\Concerns\AsAction;

class ManageTaxAndFee
{
    use AsAction;

    /**
     * Create a new tax or fee and attach to event
     */
    public function create(
        int $eventId,
        TaxFeeType $type,
        string $name,
        CalculationType $calculationType,
        float $value,
        DisplayMode $displayMode = DisplayMode::SEPARATED,
        ?array $applicableGateways = null,
        bool $isActive = true,
        int $sortOrder = 0
    ): TaxAndFee {
        $event = Event::findOrFail($eventId);

        $taxFee = TaxAndFee::create([
            'type' => $type,
            'name' => $name,
            'calculation_type' => $calculationType,
            'value' => $value,
            'display_mode' => $displayMode,
            'applicable_gateways' => $applicableGateways,
            'is_active' => $isActive,
        ]);

        $event->taxesAndFees()->attach($taxFee->id, ['sort_order' => $sortOrder]);

        return $taxFee;
    }

    /**
     * Attach existing tax/fee to an event
     */
    public function attachToEvent(int $eventId, int $taxFeeId, int $sortOrder = 0): void
    {
        $event = Event::findOrFail($eventId);
        $event->taxesAndFees()->attach($taxFeeId, ['sort_order' => $sortOrder]);
    }

    /**
     * Detach tax/fee from an event
     */
    public function detachFromEvent(int $eventId, int $taxFeeId): void
    {
        $event = Event::findOrFail($eventId);
        $event->taxesAndFees()->detach($taxFeeId);
    }

    /**
     * Update an existing tax or fee
     */
    public function update(int $taxFeeId, array $data): TaxAndFee
    {
        $taxFee = TaxAndFee::findOrFail($taxFeeId);
        $taxFee->update($data);

        return $taxFee->fresh();
    }

    /**
     * Toggle active status
     */
    public function toggleActive(int $taxFeeId): TaxAndFee
    {
        $taxFee = TaxAndFee::findOrFail($taxFeeId);
        $taxFee->update(['is_active' => ! $taxFee->is_active]);

        return $taxFee->fresh();
    }

    /**
     * Delete a tax or fee
     */
    public function delete(int $taxFeeId): bool
    {
        $taxFee = TaxAndFee::findOrFail($taxFeeId);

        return $taxFee->delete();
    }

    /**
     * Reorder taxes and fees for a specific event
     */
    public function reorder(int $eventId, array $orderedIds): void
    {
        $event = Event::findOrFail($eventId);

        foreach ($orderedIds as $index => $taxFeeId) {
            $event->taxesAndFees()->updateExistingPivot($taxFeeId, ['sort_order' => $index]);
        }
    }

    /**
     * Get all taxes and fees for an event
     */
    public function getForEvent(int $eventId, ?bool $activeOnly = null): \Illuminate\Database\Eloquent\Collection
    {
        $event = Event::findOrFail($eventId);
        $query = $event->taxesAndFees();

        if ($activeOnly !== null) {
            $query->where('is_active', $activeOnly);
        }

        return $query->get();
    }
}
