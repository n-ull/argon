<?php

namespace App\Mail;

use Domain\Promoters\Models\Commission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CommissionCompleted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $commission;

    public function __construct(Commission $commission)
    {
        $this->commission = $commission;
    }

    public function build()
    {
        return $this->subject('¡Tienes una nueva venta!')
            ->markdown('emails.promoters.commission_completed');
    }
}
