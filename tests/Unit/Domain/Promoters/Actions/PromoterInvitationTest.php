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

    InvitePromoter::run($this->event->id, $data);

    $this->assertDatabaseHas('promoter_invitations', [
        'event_id' => $this->event->id,
        'email' => $email,
        'status' => 'pending',
        'promoter_id' => null,
    ]);
});

test('it links invitation to existing promoter', function () {
    $user = User::factory()->create();
    $promoter = Promoter::create(['user_id' => $user->id, 'referral_code' => 'ABC', 'enabled' => true]);

    $data = [
        'email' => $user->email,
        'commission_type' => 'fixed',
        'commission_value' => 10,
    ];

    InvitePromoter::run($this->event->id, $data);

    $this->assertDatabaseHas('promoter_invitations', [
        'event_id' => $this->event->id,
        'email' => $user->email,
        'promoter_id' => $promoter->id,
    ]);
});

test('it accepts invitation and creates promoter for user', function () {
    $user = User::factory()->create();
    $invitation = PromoterInvitation::create([
        'event_id' => $this->event->id,
        'email' => $user->email,
        'token' => 'test-token',
        'commission_type' => 'fixed',
        'commission_value' => 10,
        'status' => 'pending',
    ]);

    $this->assertDatabaseMissing('promoters', ['user_id' => $user->id]);

    AcceptPromoterInvitation::run($invitation->token, $user);

    $this->assertDatabaseHas('promoter_invitations', [
        'id' => $invitation->id,
        'status' => 'accepted',
    ]);

    $this->assertDatabaseHas('promoters', ['user_id' => $user->id]);
    $promoter = Promoter::where('user_id', $user->id)->first();

    // Check invitation linked to promoter now
    expect($invitation->fresh()->promoter_id)->toBe($promoter->id);

    $this->assertDatabaseHas('promoter_events', [
        'promoter_id' => $promoter->id,
        'event_id' => $this->event->id,
        'commission_type' => 'fixed',
        'commission_value' => 10,
    ]);
});

test('it declines invitation', function () {
    $invitation = PromoterInvitation::create([
        'event_id' => $this->event->id,
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
