<?php

use App\Models\User;
use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Enums\TicketType;
use Domain\Ticketing\Models\Doormen;

test('doormen can scan a static ticket', function () {
    $ticket = \Domain\Ticketing\Models\Ticket::factory()->create([
        'type' => TicketType::STATIC ,
        'token' => 'N-431958'
    ]);

    $eventId = $ticket->event_id;
    $user = User::factory()->create();

    Doormen::create([
        'user_id' => $user->id,
        'event_id' => $eventId,
        'is_enabled' => true
    ]);

    $response = $this->actingAs($user)->post(route('manage.event.scan', [$eventId]), [
        'type' => 'static',
        'token' => $ticket->token
    ]);

    $response->assertJsonFragment([
        'status' => TicketStatus::ACTIVE->value,
    ]);
});

test('reused ticket show as used', function () {
    $ticket = \Domain\Ticketing\Models\Ticket::factory()->create([
        'type' => TicketType::STATIC ,
        'token' => 'N-431958'
    ]);

    $eventId = $ticket->event_id;

    $user = User::factory()->create();

    Doormen::create([
        'user_id' => $user->id,
        'event_id' => $eventId,
        'is_enabled' => true
    ]);

    $response = $this->actingAs($user)->post(route('manage.event.scan', [$eventId]), [
        'type' => 'static',
        'token' => $ticket->token
    ]);

    $response->assertJsonFragment([
        'status' => TicketStatus::ACTIVE->value,
    ]);

    $response = $this->actingAs($user)->post(route('manage.event.scan', [$eventId]), [
        'type' => 'static',
        'token' => $ticket->token
    ]);

    $response->assertJsonFragment([
        'status' => TicketStatus::USED->value,
    ]);
});


test('user cant scan a ticket if is not doormen', function () {
    $ticket = \Domain\Ticketing\Models\Ticket::factory()->create([
        'type' => TicketType::STATIC ,
        'token' => 'N-431958'
    ]);

    $eventId = $ticket->event_id;

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('manage.event.scan', [$eventId]), [
        'type' => 'static',
        'token' => $ticket->token
    ]);

    $response->assertJsonFragment([
        'message' => __('tickets.doormen_not_registered')
    ]);
});

test('doormen can scan a dynamic ticket', function () {
    $ticket = \Domain\Ticketing\Models\Ticket::factory()->create([
        'type' => TicketType::STATIC ,
        'token' => 'N-431958'
    ]);

    $eventId = $ticket->event_id;

    $user = User::factory()->create();

    Doormen::create([
        'user_id' => $user->id,
        'event_id' => $eventId,
        'is_enabled' => true
    ]);

    $response = $this->actingAs($user)->post(route('manage.event.scan', [$eventId]), [
        'type' => 'dynamic',
        'token' => $ticket->token
    ]);

    $response->assertJsonFragment([
        'status' => TicketStatus::ACTIVE->value
    ]);
});