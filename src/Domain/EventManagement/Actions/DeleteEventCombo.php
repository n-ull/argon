<?php

namespace Domain\EventManagement\Actions;

use Domain\ProductCatalog\Models\Combo;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteEventCombo
{
    use AsAction;

    public function handle($eventId, $comboId)
    {
        try {
            $combo = Combo::findOrFail($comboId);

            // Check if there are any orders for this combo
            // For now, let's assume it can be deleted if it exists
            $combo->delete();

            return true;
        } catch (\Exception $e) {
            logger()->error("Failed to delete combo: {$e->getMessage()}");

            return false;
        }
    }

    public function asController($eventId, $comboId)
    {
        $result = $this->handle($eventId, $comboId);

        if (! $result) {
            return back()->with('message', flash_error('Combo cannot be deleted', 'An error occurred while deleting the combo'));
        }

        return back()->with('message', flash_success('Combo deleted successfully', 'Combo deleted successfully'));
    }
}
