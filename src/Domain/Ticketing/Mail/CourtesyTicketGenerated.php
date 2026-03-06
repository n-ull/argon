<?php

namespace Domain\Ticketing\Mail;

use Domain\Ticketing\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CourtesyTicketGenerated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $ticket;

    /**
     * Create a new message instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function build()
    {
        return $this->subject('Recibiste un ticket de cortesía')
            ->markdown('emails.courtesy-ticket-generated');
    }
}
