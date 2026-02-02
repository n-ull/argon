<?php

namespace App\Modules\OrganizerManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\OrganizerManagement\Requests\CreateOrganizerRequest;
use Domain\OrganizerManagement\Actions\CreateOrganizer;
use Inertia\Inertia;

class CreateOrganizerController extends Controller
{
    public function create()
    {
        return Inertia::render('organizers/CreateOrganizer');
    }

    public function store(CreateOrganizerRequest $request, CreateOrganizer $createOrganizer)
    {
        $createOrganizer->handle($request->validated());

        return redirect()->route('dashboard');
    }
}
