<?php

use App\Models\User;
use Domain\EventManagement\Models\Event;
use Domain\OrganizerManagement\Models\Organizer;
use Domain\ProductCatalog\Models\Product;
use Domain\Ticketing\Models\Ticket;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertDatabaseHas;

test('can delete product with no tickets', function () {
    $user = User::factory()->create();
    $organizer = Organizer::factory()->create(['owner_id' => $user->id]);
    $organizer->users()->attach($user);
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);
    $product = Product::factory()->create(['event_id' => $event->id]);



    $response = actingAs($user)->delete(route('manage.event.products.delete', ['event' => $event, 'product' => $product]));

    $response->assertRedirect();
    $response->assertSessionHas('message.type', 'success');
    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

test('cannot delete product with tickets', function () {
    $user = User::factory()->create();
    $organizer = Organizer::factory()->create(['owner_id' => $user->id]);
    $organizer->users()->attach($user);
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);
    $product = Product::factory()->create(['event_id' => $event->id]);

    Ticket::factory()->create([
        'event_id' => $event->id,
        'product_id' => $product->id,
        'token' => 'T-'.uniqid(),
    ]);

    $response = actingAs($user)->delete(route('manage.event.products.delete', ['event' => $event, 'product' => $product]));

    $response->assertRedirect();
    $response->assertSessionHas('message.type', 'error');
    assertDatabaseHas('products', ['id' => $product->id]);
});
