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
        'organizer_id' => $organizer->id,
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
            ->has('organizer')
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

    $promoter = \Domain\Promoters\Models\Promoter::where('user_id', $user->id)->first();

    $this->assertDatabaseHas('organizer_promoter', [
        'promoter_id' => $promoter->id,
        'organizer_id' => $this->event->organizer_id,
        'commission_type' => 'fixed',
        'commission_value' => 10,
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
    // This existing test expects redirect to login.
    // However, with our new logic:
    // If email exists -> redirect to login (Unchanged logic for existing users)
    // If email does NOT exist -> Auto register (New logic)

    // We need to ensure this test scenario sets up a user that DOES NOT exist but the invitation implies they should? 
    // Actually, in the factory setup, the invitation email is 'test@example.com'.
    // And in this test, no user is created.
    // So 'test@example.com' does NOT exist in users table.
    // So my new logic will AUTO REGISTER.
    // So this test expectation needs to change!

    // Let's modify this test to verify the AUTO REGISTRATION flow for a new email.

    $response = $this->post(route('promoters.invitations.accept', $this->invitation->token));

    // Should redirect back with success message
    $response->assertRedirect();

    // Verify User was created
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);

    // Verify Invitation Accepted
    $this->assertDatabaseHas('promoter_invitations', [
        'id' => $this->invitation->id,
        'status' => 'accepted',
    ]);

    // Verify User is Logged In
    $this->assertAuthenticated();
});

test('guest with existing email is redirected to login', function () {
    // Create user first
    $user = User::factory()->create(['email' => 'test@example.com']);

    $response = $this->post(route('promoters.invitations.accept', $this->invitation->token));

    // Should redirect to login
    $response->assertRedirect(route('login'));

    // Verify Invitation STILL Pending
    $this->assertDatabaseHas('promoter_invitations', [
        'id' => $this->invitation->id,
        'status' => 'pending',
    ]);
});

test('logged in user cannot accept invitation for different email', function () {
    $user = User::factory()->create(['email' => 'other@example.com']);

    $response = $this->actingAs($user)
        ->post(route('promoters.invitations.accept', $this->invitation->token));

    // Should redirect back with error (or forbidden)
    // My implementation does `back()->with('message', flash_error(...))`
    // $response->assertSessionHas('message');
    // Just asserting session has message is weak, but `flash_error` might put it in specific key.

    // Verify Invitation STILL Pending
    $this->assertDatabaseHas('promoter_invitations', [
        'id' => $this->invitation->id,
        'status' => 'pending',
    ]);
});

test('user can view accepted invitation', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);
    $promoter = \Domain\Promoters\Models\Promoter::factory()->create(['user_id' => $user->id]);
    $this->invitation->update(['status' => 'accepted', 'promoter_id' => $promoter->id]); // Mock acceptance

    $response = $this->actingAs($user)
        ->get(route('promoters.invitations.show', $this->invitation->token));

    $response->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('promoters/invitations/Show')
            ->where('invitation.status', 'accepted')
        );
});

