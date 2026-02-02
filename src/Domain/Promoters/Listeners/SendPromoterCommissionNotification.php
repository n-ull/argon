<?php

namespace Domain\Promoters\Listeners;

use App\Mail\CommissionCompleted as CommissionCompletedMail;
use Domain\Promoters\Events\CommissionCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendPromoterCommissionNotification implements ShouldQueue
{
    public function handle(CommissionCompleted $event): void
    {
        $promoter = $event->commission->promoter;
        // Invitations have the email. If the promoter model has a direct user, we use that.
        // Assuming promoter is linked to user as per method 'user()' in Promoter model.

        $email = $promoter->user->email ?? null;

        if ($email) {
            Mail::to($email)->send(new CommissionCompletedMail($event->commission));
        }
    }
}
