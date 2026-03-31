<?php

use App\Models\User;
use Domain\EventManagement\Models\Event;
use Domain\ProductCatalog\Models\Product;
use Domain\Ticketing\Mail\CourtesyTicketInvitationGenerated;
use Domain\Ticketing\Models\Ticket;
use Domain\Ticketing\Models\TicketInvitation;
use Domain\Ticketing\Jobs\GenerateCourtesyTickets;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    Bus::fake([GenerateCourtesyTickets::class]);
});

test('it creates a ticket directly for registered users', function () {
    $event = Event::factory()->create();
    $product = Product::factory()->create(['event_id' => $event->id, 'product_type' => \Domain\ProductCatalog\Enums\ProductType::TICKET]);
    $producer = User::factory()->create();
    $user = User::factory()->create(['email' => 'registered@example.com']);

    $this->actingAs($producer)
        ->post(route('manage.event.courtesies.store', $event->id), [
            'emails' => ['registered@example.com'],
            'product_id' => $product->id,
            'quantity' => 1,
            'transfersLeft' => 0,
        ])->assertStatus(302);

    Bus::assertDispatched(GenerateCourtesyTickets::class);
    $this->assertDatabaseMissing('ticket_invitations', ['email' => 'registered@example.com']);
});

test('it creates an invitation for non registered emails', function () {
    $event = Event::factory()->create();
    $product = Product::factory()->create(['event_id' => $event->id, 'product_type' => \Domain\ProductCatalog\Enums\ProductType::TICKET]);
    $producer = User::factory()->create();

    $this->actingAs($producer)
        ->post(route('manage.event.courtesies.store', $event->id), [
            'emails' => ['unregistered@example.com'],
            'product_id' => $product->id,
            'quantity' => 2,
            'transfersLeft' => 1,
        ])->assertStatus(302);

    Bus::assertNotDispatched(GenerateCourtesyTickets::class);
    
    $this->assertDatabaseHas('ticket_invitations', [
        'email' => 'unregistered@example.com',
        'event_id' => $event->id,
        'quantity' => 2,
    ]);

    Mail::assertQueued(CourtesyTicketInvitationGenerated::class, function ($mail) {
        return $mail->hasTo('unregistered@example.com');
    });
});

test('it avoids duplicate invitation emails for the same unregistered user', function () {
    Mail::fake();
    Bus::fake([GenerateCourtesyTickets::class]);
    
    $event = Event::factory()->create();
    $product = Product::factory()->create(['event_id' => $event->id, 'product_type' => \Domain\ProductCatalog\Enums\ProductType::TICKET]);
    $producer = User::factory()->create();

    // Send first courtesy
    $this->actingAs($producer)
        ->post(route('manage.event.courtesies.store', $event->id), [
            'emails' => ['unregistered@example.com'],
            'product_id' => $product->id,
            'quantity' => 1,
            'transfersLeft' => 0,
        ])->assertStatus(302);

    // Send second courtesy
    $this->actingAs($producer)
        ->post(route('manage.event.courtesies.store', $event->id), [
            'emails' => ['unregistered@example.com'],
            'product_id' => $product->id,
            'quantity' => 1,
            'transfersLeft' => 0,
        ])->assertStatus(302);

    expect(TicketInvitation::where('email', 'unregistered@example.com')->count())->toBe(2);
    Mail::assertQueuedCount(1); // Only 1 email should be sent
});

test('it shows the invitation landing page', function () {
    $event = Event::factory()->create();
    $product = Product::factory()->create(['event_id' => $event->id, 'product_type' => \Domain\ProductCatalog\Enums\ProductType::TICKET]);
    $producer = User::factory()->create();

    $invitation = TicketInvitation::create([
        'email' => 'newuser@example.com',
        'event_id' => $event->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'transfers_left' => 0,
        'given_by' => $producer->id,
        'ticket_type' => $product->ticket_type->value,
        'expires_at' => now()->addDays(7),
    ]);

    $signedUrl = URL::signedRoute('courtesy-tickets.invitation.accept', ['email' => 'newuser@example.com']);

    $this->get($signedUrl)
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('auth/CourtesyInvitation')
            ->has('email')
            ->has('totalQuantity')
            ->where('totalQuantity', 2)
            ->has('events')
            ->where('events.0.id', $event->id)
        );
});

test('it registers user and provisions tickets when invitation is accepted via POST', function () {
    $event = Event::factory()->create();
    $product = Product::factory()->create(['event_id' => $event->id, 'product_type' => \Domain\ProductCatalog\Enums\ProductType::TICKET]);
    $producer = User::factory()->create();

    // Create invitation
    $invitation = TicketInvitation::create([
        'email' => 'newuser@example.com',
        'event_id' => $event->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'transfers_left' => 0,
        'given_by' => $producer->id,
        'ticket_type' => $product->ticket_type->value,
        'expires_at' => now()->addDays(7),
    ]);

    $signedUrl = URL::signedRoute('courtesy-tickets.invitation.accept', ['email' => 'newuser@example.com']);

    // Accept invitation via POST
    $this->post($signedUrl)
        ->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    
    $user = User::where('email', 'newuser@example.com')->first();
    
    // Check that the original invitation was marked as accepted
    $this->assertNotNull($invitation->fresh()->accepted_at);

    // Check auto-login
    $this->assertAuthenticatedAs($user);

    // Check that actual tickets generation was dispatched
    Bus::assertDispatched(GenerateCourtesyTickets::class, function ($job) use ($event, $product, $user) {
        return $job->eventId === $event->id && 
               $job->productId === $product->id && 
               in_array($user->id, $job->userIds);
    });
});

test('it cannot accept invitation with invalid signature', function () {
    $url = route('courtesy-tickets.invitation.accept', ['email' => 'hacker@example.com']);
    
    $this->get($url)
        ->assertStatus(403);
        
    $this->post($url)
        ->assertStatus(403);
});
