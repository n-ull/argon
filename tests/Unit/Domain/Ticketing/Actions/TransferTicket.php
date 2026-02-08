<?php

use App\Models\User;
use Domain\Ticketing\Actions\TransferTicket;
use Domain\Ticketing\Models\Ticket;

describe('TransferTicket', function () {
    it('should transfer a ticket to another user', function () {
        $ticket = Ticket::factory()->create([
            'transfers_left' => 1,
            'token' => 'PLACEHOLDERTOKEN'
        ]);

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        TransferTicket::run($ticket->id, $user->email);

        expect($ticket->fresh()->user_id)->toBe($user->id);
        expect($ticket->fresh()->transfers_left)->toBe(0);
        expect($ticket->fresh()->token)->not()->toBe('PLACEHOLDERTOKEN');
    });

    it('shouldnt transfer a ticket if the ticket has no transfers left', function () {
        $ticket = Ticket::factory()->create([
            'transfers_left' => 0,
            'token' => 'PLACEHOLDERTOKEN'
        ]);

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->expectExceptionMessage(__('tickets.no_transfers_left'));

        TransferTicket::run($ticket->id, $user->email);
    });
});