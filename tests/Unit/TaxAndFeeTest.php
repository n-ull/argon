<?php

use Domain\EventManagement\Enums\CalculationType;
use Domain\EventManagement\Models\Event;
use Domain\EventManagement\Models\TaxAndFee;

test('calculates percentage-based tax correctly', function () {
    $tax = TaxAndFee::factory()->make([
        'calculation_type' => CalculationType::PERCENTAGE,
        'value' => 21.0,
    ]);

    expect($tax->calculateAmount(100))->toBe(21.0)
        ->and($tax->calculateAmount(1000))->toBe(210.0);
});

test('calculates fixed fee correctly', function () {
    $fee = TaxAndFee::factory()->make([
        'calculation_type' => CalculationType::FIXED,
        'value' => 50.0,
    ]);

    expect($fee->calculateAmount(100))->toBe(50.0)
        ->and($fee->calculateAmount(1000))->toBe(50.0);
});

test('checks gateway applicability correctly', function () {
    $allGateways = TaxAndFee::factory()->make(['applicable_gateways' => null]);
    $mpOnly = TaxAndFee::factory()->make(['applicable_gateways' => ['mercadopago']]);

    expect($allGateways->isApplicableToGateway('mercadopago'))->toBeTrue()
        ->and($allGateways->isApplicableToGateway('modo'))->toBeTrue()
        ->and($allGateways->isApplicableToGateway(null))->toBeTrue()
        ->and($mpOnly->isApplicableToGateway('mercadopago'))->toBeTrue()
        ->and($mpOnly->isApplicableToGateway('modo'))->toBeFalse();
});

test('tax can be attached to multiple events', function () {
    $event1 = Event::factory()->create();
    $event2 = Event::factory()->create();

    $vat = TaxAndFee::factory()->create([
        'name' => 'VAT 21%',
        'value' => 21.0,
    ]);

    $event1->taxesAndFees()->attach($vat->id, ['sort_order' => 1]);
    $event2->taxesAndFees()->attach($vat->id, ['sort_order' => 1]);

    expect($event1->taxesAndFees)->toHaveCount(1)
        ->and($event2->taxesAndFees)->toHaveCount(1)
        ->and($event1->taxesAndFees->first()->id)->toBe($vat->id)
        ->and($event2->taxesAndFees->first()->id)->toBe($vat->id);
});

test('event can have multiple taxes and fees with sort order', function () {
    $event = Event::factory()->create();

    $vat = TaxAndFee::factory()->vat(21.0)->create();
    $serviceFee = TaxAndFee::factory()->mercadoPagoFee(3.5)->create();
    $processingFee = TaxAndFee::factory()->modoFee(50.0)->create();

    $event->taxesAndFees()->attach([
        $vat->id => ['sort_order' => 1],
        $serviceFee->id => ['sort_order' => 2],
        $processingFee->id => ['sort_order' => 3],
    ]);

    $taxesAndFees = $event->taxesAndFees;

    expect($taxesAndFees)->toHaveCount(3)
        ->and($taxesAndFees->first()->id)->toBe($vat->id)
        ->and($taxesAndFees->last()->id)->toBe($processingFee->id);
});
