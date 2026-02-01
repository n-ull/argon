<?php

namespace App\Mail;

use Domain\Promoters\Models\PromoterInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PromoterInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $invitation;
    public $companyName;

    public function __construct(PromoterInvitation $invitation)
    {
        $this->invitation = $invitation;
        $this->companyName = $invitation->event->organizer->name;
    }

    public function build()
    {
        return $this->subject('Invitation to be a Promoter')
            ->markdown('emails.promoters.invitation');
    }
}
