<?php

namespace App\Modules\OrganizerManagement\Controllers;

use App\Http\Controllers\Controller;
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
}
