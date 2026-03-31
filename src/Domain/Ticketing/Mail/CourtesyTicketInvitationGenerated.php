<?php

namespace Domain\Ticketing\Mail;

use Domain\Ticketing\Models\TicketInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class CourtesyTicketInvitationGenerated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $invitation;
    public $url;

    /**
     * Create a new message instance.
     */
    public function __construct(TicketInvitation $invitation)
    {
        $this->invitation = $invitation;
        $this->url = new \Illuminate\Support\HtmlString(URL::signedRoute('courtesy-tickets.invitation.accept', ['email' => $invitation->email]));
    }

    public function build()
    {
        return $this->subject('Has recibido una invitación para obtener tu ticket de cortesía')
            ->markdown('emails.courtesy-ticket-invitation');
    }
}
