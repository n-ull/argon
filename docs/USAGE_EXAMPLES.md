# Usage Examples

## Complete Workflow Example

### 1. Setting Up an Event with Taxes and Fees

```php
use Domain\EventManagement\Actions\ManageTaxAndFee;
use Domain\EventManagement\Enums\{TaxFeeType, CalculationType, DisplayMode};

$manageTaxFee = app(ManageTaxAndFee::class);

// Add VAT 21% - shown separately
$vat = $manageTaxFee->create(
    eventId: $event->id,
    type: TaxFeeType::TAX,
    name: 'VAT (21%)',
    calculationType: CalculationType::PERCENTAGE,
    value: 21.0,
    displayMode: DisplayMode::SEPARATED,
    applicableGateways: null, // All gateways
    isActive: true,
    sortOrder: 1
);

// Add MercadoPago service fee - integrated in price
$mpFee = $manageTaxFee->create(
    eventId: $event->id,
    type: TaxFeeType::FEE,
    name: 'Payment Processing',
    calculationType: CalculationType::PERCENTAGE,
    value: 3.5,
    displayMode: DisplayMode::INTEGRATED,
    applicableGateways: ['mercadopago'],
    isActive: true,
    sortOrder: 2
);

// Add Modo fixed fee - shown separately
$modoFee = $manageTaxFee->create(
    eventId: $event->id,
    type: TaxFeeType::FEE,
    name: 'Transaction Fee',
    calculationType: CalculationType::FIXED,
    value: 25.0,
    displayMode: DisplayMode::SEPARATED,
    applicableGateways: ['modo'],
    isActive: true,
    sortOrder: 3
);
```

### 2. Displaying Prices to Customers

```php
use Domain\Ordering\Services\PriceCalculationService;

$priceService = app(PriceCalculationService::class);
$event = Event::with('taxesAndFees')->find($eventId);

// For a ticket priced at $1000
$basePrice = 1000.00;

// When customer selects MercadoPago
$mercadoPagoDisplay = $priceService->getPriceDisplay(
    event: $event,
    basePrice: $basePrice,
    gateway: 'mercadopago'
);

/*
Result:
[
    'base_price' => 1000.00,
    'integrated_amount' => 35.00,      // 3.5% MP fee
    'display_price' => 1035.00,        // Price shown to user
    'separated_items' => [
        ['name' => 'VAT (21%)', 'type' => 'tax', 'amount' => 210.00]
    ],
    'separated_total' => 210.00,
    'final_total' => 1245.00           // Total to pay
]
*/

// When customer selects Modo
$modoDisplay = $priceService->getPriceDisplay(
    event: $event,
    basePrice: $basePrice,
    gateway: 'modo'
);

/*
Result:
[
    'base_price' => 1000.00,
    'integrated_amount' => 0.00,       // No integrated fees for Modo
    'display_price' => 1000.00,
    'separated_items' => [
        ['name' => 'VAT (21%)', 'type' => 'tax', 'amount' => 210.00],
        ['name' => 'Transaction Fee', 'type' => 'fee', 'amount' => 25.00]
    ],
    'separated_total' => 235.00,
    'final_total' => 1235.00
]
*/
```

### 3. Creating an Order

```php
use Domain\Ordering\Data\{CreateOrderData, CreateOrderProductData};
use Domain\Ordering\Actions\CreateOrder;

// Customer adds 2 VIP tickets and 1 General ticket
$orderData = new CreateOrderData(
    eventId: $event->id,
    items: [
        new CreateOrderProductData(
            productId: 1,              // VIP Ticket
            selectedPriceId: 1,        // Early Bird Price
            quantity: 2
        ),
        new CreateOrderProductData(
            productId: 2,              // General Ticket
            selectedPriceId: 3,        // Regular Price
            quantity: 1
        ),
    ],
    gateway: 'mercadopago'
);

$order = app(CreateOrder::class)->handle($orderData);

// Order now contains:
// - subtotal: Sum of all items
// - taxes_total: Calculated VAT
// - fees_total: Calculated MercadoPago fee
// - total_gross: Final amount to charge
// - items_snapshot: Complete item details
// - taxes_snapshot: Applied taxes with amounts
// - fees_snapshot: Applied fees with amounts
```

### 4. Viewing Order Details

```php
$order = Order::with('order_items.product')->find($orderId);

// Access snapshots
$itemsSnapshot = $order->items_snapshot;
/*
[
    [
        'product_id' => 1,
        'product_price_id' => 1,
        'quantity' => 2,
        'unit_price' => 500.00,
        'subtotal' => 1000.00
    ],
    [
        'product_id' => 2,
        'product_price_id' => 3,
        'quantity' => 1,
        'unit_price' => 300.00,
        'subtotal' => 300.00
    ]
]
*/

$taxesSnapshot = $order->taxes_snapshot;
/*
[
    [
        'id' => 1,
        'type' => 'tax',
        'name' => 'VAT (21%)',
        'calculation_type' => 'percentage',
        'value' => 21.0,
        'display_mode' => 'separated',
        'calculated_amount' => 273.00  // 21% of 1300
    ]
]
*/

$feesSnapshot = $order->fees_snapshot;
/*
[
    [
        'id' => 2,
        'type' => 'fee',
        'name' => 'Payment Processing',
        'calculation_type' => 'percentage',
        'value' => 3.5,
        'display_mode' => 'integrated',
        'calculated_amount' => 45.50  // 3.5% of 1300
    ]
]
*/

// Totals
echo "Subtotal: $" . $order->subtotal;           // 1300.00
echo "Taxes: $" . $order->taxes_total;           // 273.00
echo "Fees: $" . $order->fees_total;             // 45.50
echo "Total: $" . $order->total_gross;           // 1618.50
```

### 5. Managing Taxes and Fees

```php
$manageTaxFee = app(ManageTaxAndFee::class);

// Update a tax
$manageTaxFee->update($vat->id, [
    'value' => 19.0,  // Change VAT from 21% to 19%
    'name' => 'VAT (19%)'
]);

// Disable a fee temporarily
$manageTaxFee->toggleActive($mpFee->id);

// Reorder (change application order)
$manageTaxFee->reorder([
    $mpFee->id,   // Apply fee first
    $vat->id,     // Then tax
    $modoFee->id
]);

// Get all active taxes/fees for an event
$activeTaxesFees = $manageTaxFee->getForEvent(
    eventId: $event->id,
    activeOnly: true
);

// Delete a tax/fee
$manageTaxFee->delete($modoFee->id);
```

## Frontend Integration Example

### React/Vue Component

```javascript
// Fetch price display
const response = await fetch(`/api/events/${eventId}/price-display`, {
  method: 'POST',
  body: JSON.stringify({
    base_price: 1000,
    gateway: 'mercadopago'
  })
});

const priceDisplay = await response.json();

// Display to user
<div class="price-breakdown">
  <div class="base-price">
    Ticket Price: ${priceDisplay.display_price}
    {priceDisplay.integrated_amount > 0 && (
      <small>(includes ${priceDisplay.integrated_amount} in fees)</small>
    )}
  </div>
  
  {priceDisplay.separated_items.map(item => (
    <div class="additional-charge">
      {item.name}: ${item.amount}
    </div>
  ))}
  
  <div class="total">
    Total: ${priceDisplay.final_total}
  </div>
</div>
```

## API Endpoint Example

```php
// routes/api.php or controller
Route::post('/events/{event}/price-display', function (Event $event, Request $request) {
    $priceService = app(PriceCalculationService::class);
    
    $display = $priceService->getPriceDisplay(
        event: $event->load('taxesAndFees'),
        basePrice: $request->input('base_price'),
        gateway: $request->input('gateway')
    );
    
    return response()->json($display);
});
```

## Testing Example

```php
use Domain\EventManagement\Models\{Event, TaxAndFee};
use Domain\Ordering\Services\PriceCalculationService;

test('calculates price with gateway-specific fees', function () {
    $event = Event::factory()->create();
    
    // Add VAT for all gateways
    TaxAndFee::factory()->create([
        'event_id' => $event->id,
        'type' => TaxFeeType::TAX,
        'value' => 21.0,
        'calculation_type' => CalculationType::PERCENTAGE,
        'applicable_gateways' => null,
    ]);
    
    // Add MercadoPago-specific fee
    TaxAndFee::factory()->create([
        'event_id' => $event->id,
        'type' => TaxFeeType::FEE,
        'value' => 3.5,
        'calculation_type' => CalculationType::PERCENTAGE,
        'applicable_gateways' => ['mercadopago'],
    ]);
    
    $priceService = app(PriceCalculationService::class);
    
    // With MercadoPago
    $mpDisplay = $priceService->getPriceDisplay($event, 100, 'mercadopago');
    expect($mpDisplay['final_total'])->toBe(124.50); // 100 + 21 (VAT) + 3.50 (fee)
    
    // With Modo (no fee)
    $modoDisplay = $priceService->getPriceDisplay($event, 100, 'modo');
    expect($modoDisplay['final_total'])->toBe(121.00); // 100 + 21 (VAT)
});
```
