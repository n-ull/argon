<?php

namespace Domain\Ticketing\Commands;

use Domain\Ticketing\Models\Ticket;
use Illuminate\Console\Command;

class GenerateTOTP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:totp {ticketId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ticket = Ticket::findOrFail($this->argument('ticketId'));
        $google2fa = new \PragmaRX\Google2FA\Google2FA();

        return $this->info($google2fa->getCurrentOtp($ticket->token));
    }
}
