# Pricing and Tax System

## Overview

This system provides flexible pricing calculation with support for customizable taxes and fees per event, with gateway-specific rules and display options.

## Architecture

### Models

#### TaxAndFee
Located at: `src/Domain/EventManagement/Models/TaxAndFee.php`

Represents a tax or fee that can be applied to an event's orders.

**Fields:**
- `event_id`: The event this tax/fee belongs to
- `type`: Either 'tax' or 'fee'
- `name`: Display name (e.g., "VAT", "Service Fee")
- `calculation_type`: Either 'percentage' or 'fixed'
- `value`: The amount (e.g., 21.5 for 21.5% or a fixed amount)
- `display_mode`: Either 'separated' or 'integrated'
- `applicable_gateways`: JSON array of gateway names (null = all gateways)
- `is_active`: Whether this tax/fee is currently active
- `sort_order`: Order of application (important for compound calculations)

**Methods:**
- `isApplicableToGateway(?string $gateway)`: Check if tax/fee applies to a gateway
- `calculateAmount(float $baseAmount)`: Calculate the tax/fee amount

### Services

#### PriceCalculationService
Located at: `src/Domain/Ordering/Services/PriceCalculationService.php`

Handles all pricing calculations including taxes and fees.

**Main Methods:**
- `calculate(array $items, Event $event, ?string $gateway)`: Calculate complete price breakdown
- `getPriceDisplay(Event $event, float $basePrice, ?string $gateway)`: Get display information for frontend

**Returns:** `PriceBreakdown` object with:
- `subtotal`: Total of all items
- `taxesTotal`: Sum of all applicable taxes
- `feesTotal`: Sum of all applicable fees
- `totalBeforeAdditions`: Subtotal (same as subtotal currently)
- `totalGross`: Final total including everything
- `itemsSnapshot`: Array of items with prices
- `taxesSnapshot`: Array of applied taxes with calculated amounts
- `feesSnapshot`: Array of applied fees with calculated amounts

### Data Transfer Objects

#### OrderItemData
Located at: `src/Domain/Ordering/Data/OrderItemData.php`

Represents an item in an order with pricing.

#### PriceBreakdown
Located at: `src/Domain/Ordering/Data/PriceBreakdown.php`

Contains complete pricing information for an order.

### Order Snapshots

Orders store complete snapshots of pricing at the time of creation:

**Order Fields:**
- `subtotal`: Items total
- `taxes_total`: Total taxes applied
- `fees_total`: Total fees applied
- `total_before_additions`: Subtotal before taxes/fees
- `total_gross`: Final total
- `items_snapshot`: JSON snapshot of items with prices
- `taxes_snapshot`: JSON snapshot of applied taxes
- `fees_snapshot`: JSON snapshot of applied fees
- `organizer_raise_method_snapshot`: Payment method at order time
- `used_payment_gateway_snapshot`: Gateway used

## Usage Examples

### Creating Taxes and Fees

```php
use Domain\EventManagement\Models\TaxAndFee;
use Domain\EventManagement\Enums\{TaxFeeType, CalculationType, DisplayMode};

// VAT 21% - shown separately, applies to all gateways
TaxAndFee::create([
    'event_id' => $event->id,
    'type' => TaxFeeType::TAX,
    'name' => 'VAT',
    'calculation_type' => CalculationType::PERCENTAGE,
    'value' => 21.0,
    'display_mode' => DisplayMode::SEPARATED,
    'applicable_gateways' => null,
    'is_active' => true,
    'sort_order' => 1,
]);

// Service fee 2.5% - integrated in price, only for MercadoPago
TaxAndFee::create([
    'event_id' => $event->id,
    'type' => TaxFeeType::FEE,
    'name' => 'Service Fee',
    'calculation_type' => CalculationType::PERCENTAGE,
    'value' => 2.5,
    'display_mode' => DisplayMode::INTEGRATED,
    'applicable_gateways' => ['mercadopago'],
    'is_active' => true,
    'sort_order' => 2,
]);

// Fixed processing fee - shown separately, only for Modo
TaxAndFee::create([
    'event_id' => $event->id,
    'type' => TaxFeeType::FEE,
    'name' => 'Processing Fee',
    'calculation_type' => CalculationType::FIXED,
    'value' => 50.0,
    'display_mode' => DisplayMode::SEPARATED,
    'applicable_gateways' => ['modo'],
    'is_active' => true,
    'sort_order' => 3,
]);
```

### Creating an Order

```php
use Domain\Ordering\Data\CreateOrderData;
use Domain\Ordering\Actions\CreateOrder;

$orderData = new CreateOrderData(
    eventId: 1,
    items: [
        new CreateOrderProductData(
            productId: 1,
            selectedPriceId: 1,
            quantity: 2
        ),
    ],
    gateway: 'mercadopago'
);

$order = app(CreateOrder::class)->handle($orderData);
```

### Getting Price Display for Frontend

```php
use Domain\Ordering\Services\PriceCalculationService;

$priceService = app(PriceCalculationService::class);
$event = Event::with('taxesAndFees')->find(1);

$display = $priceService->getPriceDisplay(
    event: $event,
    basePrice: 100.00,
    gateway: 'mercadopago'
);

// Returns:
// [
//     'base_price' => 100.00,
//     'integrated_amount' => 2.50,  // 2.5% service fee
//     'display_price' => 102.50,     // Price shown to user
//     'separated_items' => [
//         ['name' => 'VAT', 'type' => 'tax', 'amount' => 21.00]
//     ],
//     'separated_total' => 21.00,
//     'final_total' => 123.50
// ]
```

## Display Modes

### Integrated
Taxes/fees with `display_mode = 'integrated'` are included in the displayed price. Users see one price that includes these charges.

**Use case:** Platform fees, payment processing fees that you want to absorb into the price.

### Separated
Taxes/fees with `display_mode = 'separated'` are shown as separate line items.

**Use case:** VAT, government taxes, explicit service charges.

## Gateway-Specific Rules

Set `applicable_gateways` to control which payment gateways apply specific taxes/fees:

- `null`: Applies to all gateways
- `['mercadopago']`: Only applies when using MercadoPago
- `['mercadopago', 'modo']`: Applies to both gateways

## Database Migrations

Run migrations to set up the system:

```bash
php artisan migrate
```

Seed example data:

```bash
php artisan db:seed Domain\\EventManagement\\Database\\Seeders\\TaxAndFeeSeeder
```

## Testing

The system includes comprehensive snapshots to ensure:
- Price history is preserved
- Orders remain accurate even if taxes/fees change
- Auditing and reporting have complete data
- Refunds can be calculated correctly

All pricing data at order creation time is stored in JSON snapshots.
