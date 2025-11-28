<?php

namespace App\Modules\OrganizerManagement\Controllers;

use App\Http\Controllers\Controller;
use Domain\OrganizerManagement\Models\Organizer;
use Inertia\Inertia;

class ManageOrganizations extends Controller
{
    public function index()
    {
        $organizers = auth()->user()->organizers()->get();

        return Inertia::render('organizers/Index', [
            'organizers' => $organizers,
        ]);
    }

    public function show(Organizer $organizer)
    {
        return Inertia::render('organizers/Manage');
    }
}
