# Refactored: BelongsToMany Relationship for Taxes and Fees

## Changes Made

### Why BelongsToMany?

The relationship was changed from `HasMany` to `BelongsToMany` because:
- **Reusability**: Multiple events can share the same tax/fee (e.g., VAT 21% applies to all events)
- **Flexibility**: Organizers can create a library of taxes/fees and attach them to different events
- **Maintainability**: Update a tax once, and it applies to all events using it
- **Per-Event Customization**: Sort order can be different for each event via pivot table

### Database Structure

#### taxes_and_fees table
```sql
- id
- type (tax/fee)
- name
- calculation_type (percentage/fixed)
- value
- display_mode (separated/integrated)
- applicable_gateways (json, nullable)
- is_active
- timestamps
```

#### event_tax_and_fee pivot table
```sql
- id
- event_id (foreign key)
- tax_and_fee_id (foreign key)
- sort_order (per-event ordering)
- timestamps
- unique(event_id, tax_and_fee_id)
```

### Model Relationships

**Event Model:**
```php
public function taxesAndFees(): BelongsToMany
{
    return $this->belongsToMany(TaxAndFee::class, 'event_tax_and_fee')
        ->withPivot('sort_order')
        ->withTimestamps()
        ->orderByPivot('sort_order');
}
```

**TaxAndFee Model:**
```php
public function events(): BelongsToMany
{
    return $this->belongsToMany(Event::class, 'event_tax_and_fee')
        ->withPivot('sort_order')
        ->withTimestamps()
        ->orderByPivot('sort_order');
}
```

### Usage Examples

#### Create and Attach Tax to Multiple Events
```php
// Create a VAT tax once
$vat = TaxAndFee::create([
    'type' => TaxFeeType::TAX,
    'name' => 'VAT 21%',
    'calculation_type' => CalculationType::PERCENTAGE,
    'value' => 21.0,
    'display_mode' => DisplayMode::SEPARATED,
    'applicable_gateways' => null,
    'is_active' => true,
]);

// Attach to multiple events with different sort orders
$event1->taxesAndFees()->attach($vat->id, ['sort_order' => 1]);
$event2->taxesAndFees()->attach($vat->id, ['sort_order' => 2]);
$event3->taxesAndFees()->attach($vat->id, ['sort_order' => 1]);
```

#### Using ManageTaxAndFee Action
```php
$action = app(ManageTaxAndFee::class);

// Create and attach to event
$vat = $action->create(
    eventId: $event->id,
    type: TaxFeeType::TAX,
    name: 'VAT',
    calculationType: CalculationType::PERCENTAGE,
    value: 21.0,
    sortOrder: 1
);

// Attach existing tax to another event
$action->attachToEvent($event2->id, $vat->id, sortOrder: 1);

// Detach from event
$action->detachFromEvent($event->id, $vat->id);

// Reorder for specific event
$action->reorder($event->id, [$tax1->id, $tax2->id, $tax3->id]);
```

### Benefits

1. **DRY Principle**: Define VAT once, use everywhere
2. **Consistency**: Same tax rules across events
3. **Easy Updates**: Change tax rate once, affects all events
4. **Flexibility**: Each event can have different sort orders
5. **Scalability**: Add/remove taxes from events without duplication

### Testing

Run the Pest tests:
```bash
php artisan test tests/Unit/TaxAndFeeTest.php
```

Tests cover:
- ✅ Percentage calculation
- ✅ Fixed amount calculation
- ✅ Gateway applicability
- ✅ Attaching same tax to multiple events
- ✅ Multiple taxes per event with sort order

### Migration Path

If you have existing data with the old structure:
1. Run new migrations
2. Migrate existing taxes to new structure
3. Create pivot records for event-tax relationships
4. Update code to use new relationship methods
