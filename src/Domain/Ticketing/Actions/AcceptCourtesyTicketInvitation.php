<?php

namespace Domain\Ticketing\Actions;

use App\Actions\Auth;
use App\Models\User;
use Domain\Ticketing\Models\TicketInvitation;
use Domain\Ticketing\Jobs\GenerateCourtesyTickets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class AcceptCourtesyTicketInvitation
{
    use AsAction;

    public function handle(string $email)
    {
        return DB::transaction(function () use ($email) {
            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = Auth\RegisterGuestUser::run($email);
            }

            // Fetch all unaccepted, unexpired invitations for this email
            $invitations = TicketInvitation::where('email', $email)
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->get();

            if ($invitations->isEmpty()) {
                return $user;
            }

            foreach ($invitations as $invitation) {
                // Dispatch job to generate the actual ticket
                GenerateCourtesyTickets::dispatch(
                    $invitation->event_id,
                    $invitation->product_id,
                    $invitation->quantity,
                    [$user->id],
                    $invitation->given_by,
                    $invitation->ticket_type,
                    $invitation->transfers_left
                );

                // Mark as accepted
                $invitation->update(['accepted_at' => now()]);
            }

            return $user;
        });
    }

    public function asController(Request $request)
    {
        // Signature verification is performed on both GET (initial visit) and POST (acceptance)
        if (!$request->hasValidSignature()) {
            abort(403);
        }

        $email = $request->query('email');

        // IF GET: Show landing page with invitation details
        if ($request->isMethod('get')) {
            $invitations = TicketInvitation::with(['event', 'product'])
                ->where('email', $email)
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->get();

            if ($invitations->isEmpty()) {
                return redirect()->route('login')->with('info', 'No tienes invitaciones pendientes o ya han expirado.');
            }

            // Group by event for better UI display if needed, but for now we assume same event for most cases
            $events = $invitations->pluck('event')->unique('id')->values();
            $totalQuantity = $invitations->sum('quantity');
            $expiresAt = $invitations->min('expires_at');

            // Check if user already exists
            $isEmailRegistered = \App\Models\User::where('email', $email)->exists();

            return \Inertia\Inertia::render('auth/CourtesyInvitation', [
                'email' => $email,
                'events' => $events,
                'isEmailRegistered' => $isEmailRegistered,
                'totalQuantity' => $totalQuantity,
                'expiresAt' => $expiresAt->toISOString(),
            ]);
        }

        // IF POST: Accept invitation
        return DB::transaction(function () use ($email) {
            $user = \App\Models\User::where('email', $email)->first();

            if (!$user) {
                $user = \App\Actions\Auth\RegisterGuestUser::run($email);
            }

            // Fetch all unaccepted, unexpired invitations for this email
            $invitations = TicketInvitation::where('email', $email)
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->get();

            $provisionedCount = 0;
            foreach ($invitations as $invitation) {
                GenerateCourtesyTickets::dispatch(
                    $invitation->event_id,
                    $invitation->product_id,
                    $invitation->quantity,
                    [$user->id],
                    $invitation->given_by,
                    $invitation->ticket_type,
                    $invitation->transfers_left
                );

                $provisionedCount += $invitation->quantity;
                $invitation->update(['accepted_at' => now()]);
            }

            // Automatic login
            \Illuminate\Support\Facades\Auth::login($user);

            $message = $provisionedCount > 1 
                ? "Has recibido {$provisionedCount} tickets de cortesía." 
                : "Has recibido tu ticket de cortesía.";

            return redirect()->route('tickets.index')->with('message', flash_success('¡Invitación aceptada!', "Te has registrado exitosamente. {$message}"));
        });
    }
}
