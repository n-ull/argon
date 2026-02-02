<?php

namespace Domain\Promoters\Actions;

use Domain\EventManagement\Models\Event;
use Domain\OrganizerManagement\Models\Organizer;
use Domain\Promoters\Models\Promoter;
use Lorisleiva\Actions\Concerns\AsAction;

class RemovePromoter
{
    use AsAction;

    public function handle(int $organizerId, int $promoterId)
    {
        $organizer = Organizer::findOrFail($organizerId);
        $promoter = $organizer->promoters()->findOrFail($promoterId);

        // Check if promoter has any commissions for this organizer's events?
        // Commissions are typically linked to promoter_id.
        // If we want to check commissions specifically for this organizer...
        // We'd probably check Commission::where('promoter_id', $promoterId)->whereHas('event', fn($q) => $q->where('organizer_id', $organizerId))->exists();
        // But for now, let's keep it simple or assume similar logic to before if possible.
        // Previous code: $promoter->commissions()->exists(); (This checked ALL commissions) -> Maybe too broad if they work for multiple organizers now?
        // Ideally we only check commissions for THIS organizer.

        $hasCommissions = $promoter->commissions()
            ->whereHas('event', function ($query) use ($organizerId) {
                $query->where('organizer_id', $organizerId);
            })
            ->exists();

        if ($hasCommissions) {
            // Disable the promoter for this organizer
            $promoter->organizers()->updateExistingPivot($organizerId, ['enabled' => false]);
        } else {
            // Detach the promoter
            $promoter->organizers()->detach($organizerId);
        }
    }

    public function asController(int $organizerId, int $promoterId)
    {
        $this->handle($organizerId, $promoterId);

        return redirect()->back();
    }
}
