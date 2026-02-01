<?php

namespace App\Modules\OrganizerManagement\Controllers;

use App\Http\Controllers\Controller;
use Domain\EventManagement\Models\TaxAndFee;
use Domain\OrganizerManagement\Models\Organizer;
use Illuminate\Http\Request;

class TaxAndFeeController extends Controller
{
    public function store(Request $request, Organizer $organizer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:tax,fee',
            'calculation_type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'display_mode' => 'required|in:separated,integrated',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $organizer->taxesAndFees()->create($validated);

        return redirect()->back()->with('success', 'Tax/Fee created successfully.');
    }

    public function update(Request $request, Organizer $organizer, TaxAndFee $taxAndFee)
    {
        // Ensure the tax belongs to the organizer
        if ($taxAndFee->organizer_id !== $organizer->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:tax,fee',
            'calculation_type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'display_mode' => 'required|in:separated,integrated',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $taxAndFee->update($validated);

        return redirect()->back()->with('success', 'Tax/Fee updated successfully.');
    }

    public function destroy(Organizer $organizer, TaxAndFee $taxAndFee)
    {
        // Ensure the tax belongs to the organizer
        if ($taxAndFee->organizer_id !== $organizer->id) {
            abort(403);
        }

        $taxAndFee->delete();

        return redirect()->back()->with('success', 'Tax/Fee deleted successfully.');
    }
}
