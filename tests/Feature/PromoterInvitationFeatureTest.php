<?php

use App\Models\User;
use Domain\EventManagement\Models\Event;
use Domain\Promoters\Models\Promoter;
use Domain\Promoters\Models\PromoterInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $organizer = \Domain\OrganizerManagement\Models\Organizer::factory()->create();
    $this->event = Event::factory()->create(['organizer_id' => $organizer->id]);
    $this->invitation = PromoterInvitation::create([
        'event_id' => $this->event->id,
        'email' => 'test@example.com',
        'token' => 'valid-token',
        'commission_type' => 'fixed',
        'commission_value' => 10,
        'status' => 'pending',
    ]);
});

test('guest can view invitation page', function () {
    $response = $this->get(route('promoters.invitations.show', $this->invitation->token));

    $response->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('promoters/invitations/Show')
            ->has('invitation')
            ->has('event')
        );
});

test('user can accept invitation', function () {
    $user = User::factory()->create(['email' => 'test@example.com', 'email_verified_at' => now()]);

    $response = $this->actingAs($user)
        ->post(route('promoters.invitations.accept', $this->invitation->token));

    $response->assertRedirect();

    $this->assertDatabaseHas('promoter_invitations', [
        'id' => $this->invitation->id,
        'status' => 'accepted',
    ]);

    $this->assertDatabaseHas('promoters', ['user_id' => $user->id]);
    $promoter = Promoter::where('user_id', $user->id)->first();

    $this->assertDatabaseHas('promoter_events', [
        'promoter_id' => $promoter->id,
        'event_id' => $this->event->id,
    ]);
});

test('user can decline invitation', function () {
    $user = User::factory()->create(['email' => 'test@example.com', 'email_verified_at' => now()]);

    $response = $this->actingAs($user)
        ->post(route('promoters.invitations.decline', $this->invitation->token));

    $response->assertRedirect();

    $this->assertDatabaseHas('promoter_invitations', [
        'id' => $this->invitation->id,
        'status' => 'declined',
    ]);
});

test('unauthenticated user cannot accept invitation', function () {
    $response = $this->post(route('promoters.invitations.accept', $this->invitation->token));

    $response->assertRedirect(route('login'));

    $this->assertDatabaseHas('promoter_invitations', [
        'id' => $this->invitation->id,
        'status' => 'pending',
    ]);
});
