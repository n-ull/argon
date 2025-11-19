# Implementation Checklist

## ✅ Completed

### Database
- [x] Created `taxes_and_fees` table migration
- [x] Added snapshot fields to `orders` table
- [x] Added `product_price_id` to `order_items` table

### Models
- [x] Created `TaxAndFee` model with methods
- [x] Updated `Event` model with `taxesAndFees()` relationship
- [x] Updated `Order` model with fillable fields and casts
- [x] Updated `OrderItem` model with `productPrice()` relationship
- [x] Verified `Organizer` has `settings()` relationship

### Enums
- [x] Created `TaxFeeType` enum
- [x] Created `CalculationType` enum
- [x] Created `DisplayMode` enum

### Services
- [x] Created `PriceCalculationService` with full logic
- [x] Updated `OrderService` to use pricing system
- [x] Verified `ReferenceIdService` exists

### Data Transfer Objects
- [x] Created `PriceBreakdown` DTO
- [x] Created `OrderItemData` DTO
- [x] Verified `CreateOrderData` structure
- [x] Verified `CreateOrderProductData` structure

### Actions
- [x] Created `ManageTaxAndFee` action for CRUD operations

### Seeders
- [x] Created `TaxAndFeeSeeder` with examples

### Documentation
- [x] Created comprehensive `PRICING_AND_TAXES.md`
- [x] Created `IMPLEMENTATION_SUMMARY.md`
- [x] Created `USAGE_EXAMPLES.md`
- [x] Created this checklist

## 🔄 To Do

### Database
- [ ] Run migrations: `php artisan migrate`
- [ ] (Optional) Run seeder: `php artisan db:seed Domain\\EventManagement\\Database\\Seeders\\TaxAndFeeSeeder`

### Testing
- [ ] Create unit tests for `PriceCalculationService`
- [ ] Create unit tests for `TaxAndFee` model methods
- [ ] Create integration tests for order creation with taxes
- [ ] Test gateway-specific filtering
- [ ] Test display mode logic
- [ ] Test snapshot accuracy

### API/Controllers
- [ ] Create API endpoints for managing taxes/fees
  - [ ] GET `/api/events/{event}/taxes-and-fees`
  - [ ] POST `/api/events/{event}/taxes-and-fees`
  - [ ] PUT `/api/taxes-and-fees/{id}`
  - [ ] DELETE `/api/taxes-and-fees/{id}`
  - [ ] POST `/api/taxes-and-fees/reorder`
- [ ] Create endpoint for price display
  - [ ] POST `/api/events/{event}/price-display`
- [ ] Add validation rules for tax/fee creation

### Frontend
- [ ] Create UI for managing taxes/fees in event settings
- [ ] Create price display component
- [ ] Show integrated vs separated charges
- [ ] Update checkout flow to show price breakdown
- [ ] Add gateway selector that updates prices

### Validation
- [ ] Add validation to `CreateOrderData`
- [ ] Validate product prices exist and are active
- [ ] Validate event has active products
- [ ] Validate quantities are within limits

### Additional Features (Optional)
- [ ] Add tax/fee templates for quick setup
- [ ] Add ability to copy taxes/fees from another event
- [ ] Add tax/fee history/audit log
- [ ] Add reporting for tax collection
- [ ] Add support for tax exemptions
- [ ] Add support for promotional discounts
- [ ] Add support for coupon codes

### Documentation
- [ ] Add API documentation (OpenAPI/Swagger)
- [ ] Add inline code comments where needed
- [ ] Create video tutorial or walkthrough
- [ ] Update main README with pricing system info

### Deployment
- [ ] Review migration rollback procedures
- [ ] Plan for existing orders (if any)
- [ ] Update deployment scripts
- [ ] Notify team of new features

## 📝 Notes

### Important Considerations

1. **Existing Orders**: If you have existing orders, you may need a data migration to populate the new snapshot fields.

2. **Gateway Names**: Ensure gateway names in `applicable_gateways` match exactly with your payment gateway identifiers ('mercadopago', 'modo', etc.).

3. **Decimal Precision**: The system uses `decimal(10, 2)` for money fields. Adjust if you need different precision.

4. **Sort Order**: The `sort_order` field is important for compound tax calculations. Lower numbers are applied first.

5. **Performance**: Consider caching event taxes/fees if you have high traffic, as they're loaded on every price calculation.

6. **Refunds**: When implementing refunds, use the snapshots to calculate correct refund amounts.

### Testing Scenarios

Make sure to test:
- Order with no taxes/fees
- Order with only taxes
- Order with only fees
- Order with both taxes and fees
- Different payment gateways
- Integrated vs separated display modes
- Percentage vs fixed calculations
- Inactive taxes/fees (should not apply)
- Multiple items with different prices
- Edge cases (zero prices, very large amounts)

### Security

- Validate that users can only manage taxes/fees for their own events
- Ensure prices cannot be manipulated client-side
- Log all tax/fee changes for audit purposes
- Validate gateway parameter to prevent injection
