<?php

namespace Domain\Ticketing\Support;

use Domain\Ticketing\Enums\TicketType;
use Domain\Ticketing\Models\Ticket;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TokenGenerator
{
    public function generate(TicketType $type): string
    {
        do {
            $token = $this->generateToken($type);
        } while (Ticket::where('token', '=', $token, 'and')->exists());

        return $token;
    }

    protected function generateToken(TicketType $type): string
    {
        if ($type === TicketType::DYNAMIC) {
            return app(Google2FA::class)->generateSecretKey(16);
        }

        // Static: A-######
        $letter = Str::upper(Str::random(1));
        // Ensure it is a letter, though Str::random(1) is alphanumeric.
        // Let's force a letter to be safe or use chr(rand(65, 90));
        $letter = chr(rand(65, 90));
        $numbers = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        return "{$letter}-{$numbers}";
    }
}
