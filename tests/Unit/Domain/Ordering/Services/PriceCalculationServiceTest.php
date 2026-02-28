<?php

use Domain\EventManagement\Models\Event;
use Domain\Ordering\Data\OrderItemData;
use Domain\Ordering\Enums\VoucherType;
use Domain\Ordering\Models\Voucher;
use Domain\Ordering\Services\PriceCalculationService;
use Domain\OrganizerManagement\Models\Organizer;
use Domain\OrganizerManagement\Models\OrganizerSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new PriceCalculationService();
    
    $this->organizer = Organizer::factory()->create();
    OrganizerSettings::factory()->create([
        'organizer_id' => $this->organizer->id,
        'service_fee' => 10, // 10% service fee
    ]);

    $this->event = Event::factory()->create([
        'organizer_id' => $this->organizer->id,
    ]);

    $this->items = [
        new OrderItemData(
            productId: 1,
            productPriceId: 1,
            quantity: 2,
            unitPrice: 50.0, // Subtotal = 100
            productPriceLabel: 'General',
            productName: 'Ticket'
        )
    ];
});

test('it applies a fixed amount voucher discount', function () {
    $voucher = Voucher::create([
        'event_id' => $this->event->id,
        'code' => 'FIXED10',
        'type' => VoucherType::FIXED,
        'value' => 10.0,
        'is_active' => true,
    ]);

    $breakdown = $this->service->calculate(
        items: $this->items,
        event: $this->event,
        voucherCode: 'FIXED10'
    );

    // Subtotal = 100
    // Discount = 10
    // Effective Subtotal = 90
    // Service Fee = 10% of 90 = 9
    // Total Gross = 90 + 9 = 99

    expect($breakdown->voucherDiscount)->toBe(10.0);
    expect($breakdown->totalGross)->toBe(99.0);
    expect($breakdown->voucherId)->toBe($voucher->id);
    expect($breakdown->voucherSnapshot['code'])->toBe('FIXED10');
});

test('it caps fixed amount discount at order subtotal', function () {
    Voucher::create([
        'event_id' => $this->event->id,
        'code' => 'HUGE500',
        'type' => VoucherType::FIXED,
        'value' => 500.0,
        'is_active' => true,
    ]);

    $breakdown = $this->service->calculate(
        items: $this->items,
        event: $this->event,
        voucherCode: 'HUGE500'
    );

    // Subtotal = 100
    // Discount = 500 -> capped at 100
    // Effective Subtotal = 0
    // Service Fee = 0
    // Total Gross = 0

    expect($breakdown->voucherDiscount)->toBe(100.0);
    expect($breakdown->totalGross)->toBe(0.0);
});

test('it does not apply voucher if subtotal is below minimum order amount', function () {
    Voucher::create([
        'event_id' => $this->event->id,
        'code' => 'MIN150',
        'type' => VoucherType::FIXED,
        'value' => 20.0,
        'min_order_amount' => 150.0,
        'is_active' => true,
    ]);

    $breakdown = $this->service->calculate(
        items: $this->items,
        event: $this->event,
        voucherCode: 'MIN150'
    );

    // Subtotal = 100 (below 150)
    // Discount = 0
    // Total Gross = 100 + 10% fee = 110

    expect($breakdown->voucherDiscount)->toBe(0.0);
    expect($breakdown->totalGross)->toBe(110.0);
    expect($breakdown->voucherId)->toBeNull();
});

test('it applies voucher if subtotal meet minimum order amount', function () {
    Voucher::create([
        'event_id' => $this->event->id,
        'code' => 'MIN50',
        'type' => VoucherType::FIXED,
        'value' => 20.0,
        'min_order_amount' => 50.0,
        'is_active' => true,
    ]);

    $breakdown = $this->service->calculate(
        items: $this->items,
        event: $this->event,
        voucherCode: 'MIN50'
    );

    // Subtotal = 100 (above 50)
    // Discount = 20
    // Effective Subtotal = 80
    // Fee = 8
    // Total = 88

    expect($breakdown->voucherDiscount)->toBe(20.0);
    expect($breakdown->totalGross)->toBe(88.0);
});

test('it applies a percentage discount', function () {
    Voucher::create([
        'event_id' => $this->event->id,
        'code' => 'PERC10',
        'type' => VoucherType::PERCENTAGE,
        'value' => 10.0,
        'is_active' => true,
    ]);

    $breakdown = $this->service->calculate(
        items: $this->items,
        event: $this->event,
        voucherCode: 'PERC10'
    );

    // Subtotal = 100
    // Discount = 10% of 100 = 10
    // Effective Subtotal = 90
    // Fee = 9
    // Total = 99

    expect($breakdown->voucherDiscount)->toBe(10.0);
    expect($breakdown->totalGross)->toBe(99.0);
});

test('it caps percentage discount at max_discount_amount', function () {
    Voucher::create([
        'event_id' => $this->event->id,
        'code' => 'PERCCAP',
        'type' => VoucherType::PERCENTAGE,
        'value' => 50.0, // 50% discount
        'max_discount_amount' => 15.0, // but capped at 15
        'is_active' => true,
    ]);

    $breakdown = $this->service->calculate(
        items: $this->items,
        event: $this->event,
        voucherCode: 'PERCCAP'
    );

    // Subtotal = 100
    // 50% = 50 -> capped at 15
    // Effective Subtotal = 85
    // Fee = 8.5
    // Total = 93.5

    expect($breakdown->voucherDiscount)->toBe(15.0);
    expect($breakdown->totalGross)->toBe(93.5);
});

test('it does not apply percentage voucher if subtotal is below min_order_amount', function () {
    Voucher::create([
        'event_id' => $this->event->id,
        'code' => 'PERCMIN',
        'type' => VoucherType::PERCENTAGE,
        'value' => 10.0,
        'min_order_amount' => 200.0,
        'is_active' => true,
    ]);

    $breakdown = $this->service->calculate(
        items: $this->items,
        event: $this->event,
        voucherCode: 'PERCMIN'
    );

    // Subtotal = 100 (below 200)
    // Discount = 0
    // Total = 110

    expect($breakdown->voucherDiscount)->toBe(0.0);
    expect($breakdown->totalGross)->toBe(110.0);
});

test('it does not apply voucher if inactive', function () {
    Voucher::create([
        'event_id' => $this->event->id,
        'code' => 'INACTIVE',
        'type' => VoucherType::FIXED,
        'value' => 10.0,
        'is_active' => false,
    ]);

    $breakdown = $this->service->calculate(
        items: $this->items,
        event: $this->event,
        voucherCode: 'INACTIVE'
    );

    expect($breakdown->voucherDiscount)->toBe(0.0);
});

test('it does not apply voucher if not yet started', function () {
    Voucher::create([
        'event_id' => $this->event->id,
        'code' => 'FUTURE',
        'type' => VoucherType::FIXED,
        'value' => 10.0,
        'starts_at' => now()->addDay(),
        'is_active' => true,
    ]);

    $breakdown = $this->service->calculate(
        items: $this->items,
        event: $this->event,
        voucherCode: 'FUTURE'
    );

    expect($breakdown->voucherDiscount)->toBe(0.0);
});

test('it does not apply voucher if already ended', function () {
    Voucher::create([
        'event_id' => $this->event->id,
        'code' => 'EXPIRED',
        'type' => VoucherType::FIXED,
        'value' => 10.0,
        'ends_at' => now()->subDay(),
        'is_active' => true,
    ]);

    $breakdown = $this->service->calculate(
        items: $this->items,
        event: $this->event,
        voucherCode: 'EXPIRED'
    );

    expect($breakdown->voucherDiscount)->toBe(0.0);
});

test('it does not apply voucher if usage limit reached', function () {
    Voucher::create([
        'event_id' => $this->event->id,
        'code' => 'LIMIT',
        'type' => VoucherType::FIXED,
        'value' => 10.0,
        'total_limit' => 5,
        'used_count' => 5,
        'is_active' => true,
    ]);

    $breakdown = $this->service->calculate(
        items: $this->items,
        event: $this->event,
        voucherCode: 'LIMIT'
    );

    expect($breakdown->voucherDiscount)->toBe(0.0);
});

test('it applies voucher code case-insensitively', function () {
    Voucher::create([
        'event_id' => $this->event->id,
        'code' => 'case-insensitive', // Will be stored as CASE-INSENSITIVE in Model boot
        'type' => VoucherType::FIXED,
        'value' => 10.0,
        'is_active' => true,
    ]);

    $breakdown = $this->service->calculate(
        items: $this->items,
        event: $this->event,
        voucherCode: 'CASE-INSENSITIVE'
    );

    expect($breakdown->voucherDiscount)->toBe(10.0);

    $breakdownLower = $this->service->calculate(
        items: $this->items,
        event: $this->event,
        voucherCode: 'case-insensitive'
    );

    expect($breakdownLower->voucherDiscount)->toBe(10.0);
});
