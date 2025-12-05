# Pricing and Tax System - Implementation Summary

## What Was Implemented

### 1. Database Structure

#### New Table: `taxes_and_fees`
- Stores customizable taxes and fees per event
- Supports percentage and fixed calculations
- Gateway-specific rules
- Display modes (integrated/separated)

#### Updated Table: `orders`
Added snapshot fields:
- `subtotal` - Items total
- `taxes_total` - Total taxes
- `fees_total` - Total fees  
- `items_snapshot` - JSON snapshot of items
- `taxes_snapshot` - JSON snapshot of taxes
- `fees_snapshot` - JSON snapshot of fees

#### Updated Table: `order_items`
Added:
- `product_price_id` - Links to specific price tier

### 2. Models

#### TaxAndFee Model
- `src/Domain/EventManagement/Models/TaxAndFee.php`
- Methods: `isApplicableToGateway()`, `calculateAmount()`
- Relationships: belongs to Event

#### Updated Models
- **Event**: Added `taxesAndFees()` relationship
- **Order**: Added fillable fields and casts for snapshots
- **OrderItem**: Added `productPrice()` relationship

### 3. Enums

- `TaxFeeType`: TAX, FEE
- `CalculationType`: PERCENTAGE, FIXED
- `DisplayMode`: SEPARATED, INTEGRATED

### 4. Services

#### PriceCalculationService
- `calculate()`: Complete price breakdown with snapshots
- `getPriceDisplay()`: Frontend display information
- Handles gateway-specific filtering
- Separates integrated vs separated taxes/fees

#### Updated OrderService
- Integrated with PriceCalculationService
- Creates complete snapshots on order creation
- Prepares order items with pricing

### 5. Data Transfer Objects

- **PriceBreakdown**: Complete pricing information
- **OrderItemData**: Item with pricing details

### 6. Migrations

1. `2025_11_18_183136_create_taxes_and_fees_table.php`
2. `2025_11_18_190000_add_pricing_snapshots_to_orders_table.php`
3. `2025_11_18_190001_add_product_price_id_to_order_items_table.php`

### 7. Seeders

- `TaxAndFeeSeeder`: Example taxes and fees

## Key Features

### ✅ Complete Price Snapshots
Every order stores complete pricing data at creation time, ensuring historical accuracy.

### ✅ Gateway-Specific Rules
Taxes/fees can be configured to apply only to specific payment gateways (MercadoPago, Modo, etc.).

### ✅ Flexible Display
- **Integrated**: Included in displayed price
- **Separated**: Shown as separate line items

### ✅ Multiple Calculation Types
- **Percentage**: e.g., 21% VAT
- **Fixed**: e.g., $50 processing fee

### ✅ Per-Event Configuration
Each event can have its own set of taxes and fees.

### ✅ Sort Order
Control the order of tax/fee application (important for compound calculations).

## Usage Flow

1. **Organizer creates event** with custom taxes/fees
2. **Customer adds items** to cart
3. **System calculates prices** using PriceCalculationService
   - Filters by selected gateway
   - Applies active taxes/fees
   - Separates integrated vs separated
4. **Order is created** with complete snapshots
5. **Frontend displays** prices using `getPriceDisplay()`

## Next Steps

To use the system:

1. Run migrations: `php artisan migrate`
2. Seed examples: `php artisan db:seed Domain\\EventManagement\\Database\\Seeders\\TaxAndFeeSeeder`
3. Create taxes/fees for your events
4. Orders will automatically calculate and snapshot pricing

## Documentation

See `docs/PRICING_AND_TAXES.md` for detailed usage examples and API documentation.
