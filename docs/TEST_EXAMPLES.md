# Test Examples

## Unit Tests

### Testing TaxAndFee Model

```php
<?php

use Domain\EventManagement\Models\{Event, TaxAndFee};
use Domain\EventManagement\Enums\{TaxFeeType, CalculationType, DisplayMode};

test('calculates percentage-based tax correctly', function () {
    $tax = TaxAndFee::factory()->vat(21.0)->make();
    
    expect($tax->calculateAmount(100))->toBe(21.0);
    expect($tax->calculateAmount(1000))->toBe(210.0);
});

test('calculates fixed fee correctly', function () {
    $fee = TaxAndFee::factory()->fixed(50.0)->make();
    
    expect($fee->calculateAmount(100))->toBe(50.0);
    expect($fee->calculateAmount(1000))->toBe(50.0);
});

test('checks gateway applicability correctly', function () {
    $allGateways = TaxAndFee::factory()->make(['applicable_gateways' => null]);
    $mpOnly = TaxAndFee::factory()->forGateways(['mercadopago'])->make();
    
    expect($allGateways->isApplicableToGateway('mercadopago'))->toBeTrue();
    expect($allGateways->isApplicableToGateway('modo'))->toBeTrue();
    expect($allGateways->isApplicableToGateway(null))->toBeTrue();
    
    expect($mpOnly->isApplicableToGateway('mercadopago'))->toBeTrue();
    expect($mpOnly->isApplicableToGateway('modo'))->toBeFalse();
});
```

### Testing PriceCalculationService

```php
<?php

use Domain\EventManagement\Models\{Event, TaxAndFee};
use Domain\Ordering\Data\OrderItemData;
use Domain\Ordering\Services\PriceCalculationService;

test('calculates order total with taxes and fees', function () {
    $event = Event::factory()->create();
    
    // Add 21% VAT
    TaxAndFee::factory()->vat(21.0)->create(['event_id' => $event->id]);
    
    // Add 3.5% service fee
    TaxAndFee::factory()
        ->fee()
        ->percentage(3.5)
        ->integrated()
        ->create(['event_id' => $event->id]);
    
    $items = [
        new OrderItemData(
            productId: 1,
            productPriceId: 1,
            quantity: 2,
            unitPrice: 100.0
        ),
    ];
    
    $priceService = app(PriceCalculationService::class);
    $breakdown = $priceService->calculate($items, $event->fresh());
    
    expect($breakdown->subtotal)->toBe(200.0);
    expect($breakdown->taxesTotal)->toBe(42.0);  // 21% of 200
    expect($breakdown->feesTotal)->toBe(7.0);    // 3.5% of 200
    expect($breakdown->totalGross)->toBe(249.0);
});

test('filters taxes by gateway', function () {
    $event = Event::factory()->create();
    
    // VAT for all gateways
    TaxAndFee::factory()->vat(21.0)->create(['event_id' => $event->id]);
    
    // MercadoPago-specific fee
    TaxAndFee::factory()
        ->mercadoPagoFee(3.5)
        ->create(['event_id' => $event->id]);
    
    // Modo-specific fee
    TaxAndFee::factory()
        ->modoFee(50.0)
        ->create(['event_id' => $event->id]);
    
    $items = [
        new OrderItemData(
            productId: 1,
            productPriceId: 1,
            quantity: 1,
            unitPrice: 1000.0
        ),
    ];
    
    $priceService = app(PriceCalculationService::class);
    
    // With MercadoPago
    $mpBreakdown = $priceService->calculate($items, $event->fresh(), 'mercadopago');
    expect($mpBreakdown->taxesTotal)->toBe(210.0);  // VAT
    expect($mpBreakdown->feesTotal)->toBe(35.0);    // MP fee
    expect($mpBreakdown->totalGross)->toBe(1245.0);
    
    // With Modo
    $modoBreakdown = $priceService->calculate($items, $event->fresh(), 'modo');
    expect($modoBreakdown->taxesTotal)->toBe(210.0);  // VAT
    expect($modoBreakdown->feesTotal)->toBe(50.0);    // Modo fee
    expect($modoBreakdown->totalGross)->toBe(1260.0);
});

test('creates correct snapshots', function () {
    $event = Event::factory()->create();
    
    TaxAndFee::factory()->vat(21.0)->create([
        'event_id' => $event->id,
        'sort_order' => 1,
    ]);
    
    $items = [
        new OrderItemData(
            productId: 1,
            productPriceId: 1,
            quantity: 2,
            unitPrice: 100.0
        ),
    ];
    
    $priceService = app(PriceCalculationService::class);
    $breakdown = $priceService->calculate($items, $event->fresh());
    
    expect($breakdown->itemsSnapshot)->toHaveCount(1);
    expect($breakdown->itemsSnapshot[0])->toHaveKeys([
        'product_id',
        'product_price_id',
        'quantity',
        'unit_price',
        'subtotal'
    ]);
    
    expect($breakdown->taxesSnapshot)->toHaveCount(1);
    expect($breakdown->taxesSnapshot[0])->toHaveKeys([
        'id',
        'type',
        'name',
        'calculation_type',
        'value',
        'display_mode',
        'calculated_amount'
    ]);
});

test('handles inactive taxes correctly', function () {
    $event = Event::factory()->create();
    
    // Active tax
    TaxAndFee::factory()->vat(21.0)->create(['event_id' => $event->id]);
    
    // Inactive tax
    TaxAndFee::factory()->vat(10.0)->inactive()->create(['event_id' => $event->id]);
    
    $items = [
        new OrderItemData(
            productId: 1,
            productPriceId: 1,
            quantity: 1,
            unitPrice: 100.0
        ),
    ];
    
    $priceService = app(PriceCalculationService::class);
    $breakdown = $priceService->calculate($items, $event->fresh());
    
    // Should only apply the active 21% tax
    expect($breakdown->taxesTotal)->toBe(21.0);
    expect($breakdown->taxesSnapshot)->toHaveCount(1);
});
```

### Testing Price Display

```php
<?php

use Domain\EventManagement\Models\{Event, TaxAndFee};
use Domain\Ordering\Services\PriceCalculationService;

test('separates integrated and separated charges', function () {
    $event = Event::factory()->create();
    
    // Integrated fee
    TaxAndFee::factory()
        ->fee()
        ->percentage(3.5)
        ->integrated()
        ->create(['event_id' => $event->id]);
    
    // Separated tax
    TaxAndFee::factory()
        ->vat(21.0)
        ->separated()
        ->create(['event_id' => $event->id]);
    
    $priceService = app(PriceCalculationService::class);
    $display = $priceService->getPriceDisplay($event->fresh(), 1000.0);
    
    expect($display['base_price'])->toBe(1000.0);
    expect($display['integrated_amount'])->toBe(35.0);
    expect($display['display_price'])->toBe(1035.0);
    expect($display['separated_items'])->toHaveCount(1);
    expect($display['separated_total'])->toBe(210.0);
    expect($display['final_total'])->toBe(1245.0);
});
```

## Integration Tests

### Testing Order Creation

```php
<?php

use Domain\EventManagement\Models\{Event, TaxAndFee};
use Domain\Ordering\Actions\CreateOrder;
use Domain\Ordering\Data\{CreateOrderData, CreateOrderProductData};
use Domain\Ordering\Models\Order;
use Domain\ProductCatalog\Models\{Product, ProductPrice};

test('creates order with complete snapshots', function () {
    $event = Event::factory()->create();
    
    $product = Product::factory()->create(['event_id' => $event->id]);
    $price = ProductPrice::factory()->create([
        'product_id' => $product->id,
        'price' => 500.0,
    ]);
    
    TaxAndFee::factory()->vat(21.0)->create(['event_id' => $event->id]);
    TaxAndFee::factory()->mercadoPagoFee(3.5)->create(['event_id' => $event->id]);
    
    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            new CreateOrderProductData(
                productId: $product->id,
                selectedPriceId: $price->id,
                quantity: 2
            ),
        ],
        gateway: 'mercadopago'
    );
    
    $order = app(CreateOrder::class)->handle($orderData);
    
    expect($order)->toBeInstanceOf(Order::class);
    expect($order->subtotal)->toBe(1000.0);
    expect($order->taxes_total)->toBe(210.0);
    expect($order->fees_total)->toBe(35.0);
    expect($order->total_gross)->toBe(1245.0);
    expect($order->items_snapshot)->toBeArray();
    expect($order->taxes_snapshot)->toBeArray();
    expect($order->fees_snapshot)->toBeArray();
    expect($order->used_payment_gateway_snapshot)->toBe('mercadopago');
});

test('order snapshots remain unchanged when taxes are modified', function () {
    $event = Event::factory()->create();
    
    $product = Product::factory()->create(['event_id' => $event->id]);
    $price = ProductPrice::factory()->create([
        'product_id' => $product->id,
        'price' => 100.0,
    ]);
    
    $tax = TaxAndFee::factory()->vat(21.0)->create(['event_id' => $event->id]);
    
    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            new CreateOrderProductData(
                productId: $product->id,
                selectedPriceId: $price->id,
                quantity: 1
            ),
        ],
        gateway: 'mercadopago'
    );
    
    $order = app(CreateOrder::class)->handle($orderData);
    
    $originalTaxTotal = $order->taxes_total;
    $originalSnapshot = $order->taxes_snapshot;
    
    // Change the tax rate
    $tax->update(['value' => 19.0]);
    
    // Refresh order from database
    $order->refresh();
    
    // Snapshots should remain unchanged
    expect($order->taxes_total)->toBe($originalTaxTotal);
    expect($order->taxes_snapshot)->toBe($originalSnapshot);
});
```

### Testing ManageTaxAndFee Action

```php
<?php

use Domain\EventManagement\Actions\ManageTaxAndFee;
use Domain\EventManagement\Enums\{TaxFeeType, CalculationType, DisplayMode};
use Domain\EventManagement\Models\{Event, TaxAndFee};

test('creates tax successfully', function () {
    $event = Event::factory()->create();
    $action = app(ManageTaxAndFee::class);
    
    $tax = $action->create(
        eventId: $event->id,
        type: TaxFeeType::TAX,
        name: 'VAT',
        calculationType: CalculationType::PERCENTAGE,
        value: 21.0
    );
    
    expect($tax)->toBeInstanceOf(TaxAndFee::class);
    expect($tax->event_id)->toBe($event->id);
    expect($tax->name)->toBe('VAT');
    expect($tax->value)->toBe(21.0);
});

test('updates tax successfully', function () {
    $tax = TaxAndFee::factory()->create(['value' => 21.0]);
    $action = app(ManageTaxAndFee::class);
    
    $updated = $action->update($tax->id, ['value' => 19.0]);
    
    expect($updated->value)->toBe(19.0);
});

test('toggles active status', function () {
    $tax = TaxAndFee::factory()->create(['is_active' => true]);
    $action = app(ManageTaxAndFee::class);
    
    $toggled = $action->toggleActive($tax->id);
    expect($toggled->is_active)->toBeFalse();
    
    $toggled = $action->toggleActive($tax->id);
    expect($toggled->is_active)->toBeTrue();
});

test('reorders taxes correctly', function () {
    $event = Event::factory()->create();
    
    $tax1 = TaxAndFee::factory()->create(['event_id' => $event->id, 'sort_order' => 0]);
    $tax2 = TaxAndFee::factory()->create(['event_id' => $event->id, 'sort_order' => 1]);
    $tax3 = TaxAndFee::factory()->create(['event_id' => $event->id, 'sort_order' => 2]);
    
    $action = app(ManageTaxAndFee::class);
    $action->reorder([$tax3->id, $tax1->id, $tax2->id]);
    
    expect($tax3->fresh()->sort_order)->toBe(0);
    expect($tax1->fresh()->sort_order)->toBe(1);
    expect($tax2->fresh()->sort_order)->toBe(2);
});
```

## Feature Tests

```php
<?php

test('complete checkout flow with taxes', function () {
    // Setup
    $event = Event::factory()->create();
    $product = Product::factory()->create(['event_id' => $event->id]);
    $price = ProductPrice::factory()->create([
        'product_id' => $product->id,
        'price' => 1000.0,
    ]);
    
    TaxAndFee::factory()->vat(21.0)->create(['event_id' => $event->id]);
    TaxAndFee::factory()->mercadoPagoFee(3.5)->create(['event_id' => $event->id]);
    
    // Get price display
    $response = $this->postJson("/api/events/{$event->id}/price-display", [
        'base_price' => 1000.0,
        'gateway' => 'mercadopago',
    ]);
    
    $response->assertOk();
    $response->assertJson([
        'base_price' => 1000.0,
        'final_total' => 1245.0,
    ]);
    
    // Create order
    $response = $this->postJson('/api/orders', [
        'event_id' => $event->id,
        'items' => [
            [
                'product_id' => $product->id,
                'selected_price_id' => $price->id,
                'quantity' => 1,
            ],
        ],
        'gateway' => 'mercadopago',
    ]);
    
    $response->assertCreated();
    $response->assertJsonStructure([
        'id',
        'subtotal',
        'taxes_total',
        'fees_total',
        'total_gross',
    ]);
});
```

## Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/OrderCreationTest.php

# Run tests with coverage
php artisan test --coverage

# Run specific test
php artisan test --filter="calculates order total with taxes and fees"
```
