<?php

namespace Domain\EventManagement\Actions;

use Domain\ProductCatalog\Models\Combo;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class SortEventCombo
{
    use AsAction;

    public function handle($comboId, $direction)
    {
        $combo = Combo::findOrFail($comboId);

        $combos = Combo::where('event_id', $combo->event_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // Ensure all combos have unique, sequential sort orders first
        foreach ($combos as $index => $p) {
            if ($p->sort_order !== $index) {
                $p->update(['sort_order' => $index]);
            }
        }

        $currentIndex = $combos->search(fn ($p) => $p->id == $combo->id);
        $targetIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;

        if (isset($combos[$targetIndex])) {
            $otherCombo = $combos[$targetIndex];

            // Perform the swap
            $combo->update(['sort_order' => $targetIndex]);
            $otherCombo->update(['sort_order' => $currentIndex]);
        }

        return $combo;
    }

    public function asController($eventId, $comboId, Request $request)
    {
        $validated = $request->validate([
            'direction' => 'required|in:up,down',
        ]);

        $this->handle($comboId, $validated['direction']);

        return back()->with('message', flash_success('Combo sorted successfully', 'Combo sorted successfully'));
    }
}
