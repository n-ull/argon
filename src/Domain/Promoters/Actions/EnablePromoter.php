<?php

namespace Domain\Promoters\Actions;

use Domain\EventManagement\Models\Event;
use Lorisleiva\Actions\Concerns\AsAction;

class EnablePromoter
{
    use AsAction;

    public function handle(int $organizerId, int $promoterId)
    {
        // verify promoter belongs to organizer
        $organizer = \Domain\OrganizerManagement\Models\Organizer::findOrFail($organizerId);
        $promoter = $organizer->promoters()->findOrFail($promoterId);

        $promoter->organizers()->updateExistingPivot($organizerId, ['enabled' => true]);
    }

    public function asController(int $organizerId, int $promoterId)
    {
        $this->handle($organizerId, $promoterId);

        return redirect()->back()->with('success', 'Promoter enabled successfully');
    }
}
