<?php

use App\Models\User;
use Domain\EventManagement\Models\Event;
use Domain\Promoters\Actions\AcceptPromoterInvitation;
use Domain\Promoters\Actions\DeclinePromoterInvitation;
use Domain\Promoters\Actions\InvitePromoter;
use Domain\Promoters\Models\Promoter;
use Domain\Promoters\Models\PromoterInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create an organizer and event setup
    $organizer = \Domain\OrganizerManagement\Models\Organizer::factory()->create();
    $this->event = Event::factory()->create(['organizer_id' => $organizer->id]);
});

test('it creates invitation for new email', function () {
    $email = 'new@example.com';
    $data = [
        'email' => $email,
        'commission_type' => 'fixed',
        'commission_value' => 10,
    ];

    InvitePromoter::run($this->event->organizer_id, $data);

    $this->assertDatabaseHas('promoter_invitations', [
        'organizer_id' => $this->event->organizer_id,
        'email' => $email,
        'status' => 'pending',
        'promoter_id' => null,
    ]);
});

test('it links invitation to existing promoter for different organizer', function () {
    // User is promoter for Organizer A
    $user = User::factory()->create();
    $organizerA = \Domain\OrganizerManagement\Models\Organizer::factory()->create();
    $promoter = Promoter::factory()->withOrganizer($organizerA)->create(['user_id' => $user->id]);

    // Invite to Organizer B (this->event->organizer)
    $email = $user->email;
    $data = [
        'email' => $email,
        'commission_type' => 'fixed',
        'commission_value' => 10,
    ];

    InvitePromoter::run($this->event->organizer_id, $data);

    $this->assertDatabaseHas('promoter_invitations', [
        'organizer_id' => $this->event->organizer_id,
        'email' => $email,
        'status' => 'pending',
        'promoter_id' => $promoter->id, // Should link to existing promoter model
    ]);
});

test('it prevents inviting an existing active promoter', function () {
    $user = User::factory()->create();
    // Create promoter properly using factory and withOrganizer state
    $promoter = Promoter::factory()
        ->withOrganizer(
            \Domain\OrganizerManagement\Models\Organizer::find($this->event->organizer_id),
            [
                'commission_type' => 'percentage',
                'commission_value' => 10,
                'enabled' => true,
            ]
        )
        ->create([
            'user_id' => $user->id,
            'referral_code' => 'P1',
        ]);

    $data = [
        'email' => $user->email,
        'commission_type' => 'fixed',
        'commission_value' => 10,
    ];

    expect(fn () => InvitePromoter::run($this->event->organizer_id, $data))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('it accepts invitation and creates promoter for user', function () {
    $user = User::factory()->create();
    $invitation = PromoterInvitation::create([
        'organizer_id' => $this->event->organizer_id,
        'email' => $user->email,
        'token' => 'test-token',
        'commission_type' => 'fixed',
        'commission_value' => 10,
        'status' => 'pending',
    ]);

    // Check that user is NOT linked to organizer yet
    // We can't check 'promoters' table for organizer_id anymore.
    // Use relation check or pivot check.
    $promoter = Promoter::where('user_id', $user->id)->first();
    if ($promoter) {
        $this->assertDatabaseMissing('organizer_promoter', [
            'promoter_id' => $promoter->id,
            'organizer_id' => $this->event->organizer_id
        ]);
    } else {
        $this->assertDatabaseMissing('promoters', ['user_id' => $user->id]);
    }

    AcceptPromoterInvitation::run($invitation->token, $user);

    $this->assertDatabaseHas('promoter_invitations', [
        'id' => $invitation->id,
        'status' => 'accepted',
    ]);

    // Verify Promoter created/found
    $this->assertDatabaseHas('promoters', [
        'user_id' => $user->id,
    ]);

    $promoter = Promoter::where('user_id', $user->id)->first();

    // Verify Pivot
    $this->assertDatabaseHas('organizer_promoter', [
        'promoter_id' => $promoter->id,
        'organizer_id' => $this->event->organizer_id,
        'commission_type' => 'fixed',
        'commission_value' => 10,
    ]);

    // Check invitation linked to promoter now
    expect($invitation->fresh()->promoter_id)->toBe($promoter->id);
});

test('it declines invitation', function () {
    $invitation = PromoterInvitation::create([
        'organizer_id' => $this->event->organizer_id,
        'email' => 'test@example.com',
        'token' => 'test-token',
        'commission_type' => 'fixed',
        'commission_value' => 10,
        'status' => 'pending',
    ]);


    DeclinePromoterInvitation::run($invitation->token);

    $this->assertDatabaseHas('promoter_invitations', [
        'id' => $invitation->id,
        'status' => 'declined',
    ]);
});

test('it prevents inviting an email with pending invitation', function () {
    $email = 'pending@example.com';
    PromoterInvitation::create([
        'organizer_id' => $this->event->organizer_id,
        'email' => $email,
        'token' => 'pending-token',
        'commission_type' => 'fixed',
        'commission_value' => 10,
        'status' => 'pending',
    ]);

    $data = [
        'email' => $email,
        'commission_type' => 'fixed',
        'commission_value' => 20,
    ];

    expect(fn () => InvitePromoter::run($this->event->organizer_id, $data))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('it sends an invitation email', function () {
    \Illuminate\Support\Facades\Mail::fake();

    $email = 'invite@example.com';
    $data = [
        'email' => $email,
        'commission_type' => 'fixed',
        'commission_value' => 10,
    ];

    InvitePromoter::run($this->event->organizer_id, $data);

    \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\PromoterInvitationMail::class, function ($mail) use ($email) {
        return $mail->hasTo($email);
    });
});
