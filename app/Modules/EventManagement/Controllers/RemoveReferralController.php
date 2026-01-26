<?php

namespace App\Modules\EventManagement\Controllers;

use App\Http\Controllers\Controller;
use Domain\EventManagement\Models\Event;

class RemoveReferralController extends Controller
{
    public function __invoke(Event $event)
    {
        session()->forget('referral_code_'.$event->id);

        return redirect()->back();
    }
}
