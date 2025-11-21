<?php

namespace Domain\EventManagement\Database\Seeders;

use Domain\EventManagement\Enums\CalculationType;
use Domain\EventManagement\Enums\DisplayMode;
use Domain\EventManagement\Enums\TaxFeeType;
use Domain\EventManagement\Models\Event;
use Domain\EventManagement\Models\TaxAndFee;
use Illuminate\Database\Seeder;

class TaxAndFeeSeeder extends Seeder
{
    public function run(): void
    {
        $event = Event::first();

        if (! $event) {
            $this->command->warn('No events found. Please create an event first.');

            return;
        }

        // Example: VAT 21% - separated display
        $vat = TaxAndFee::create([
            'type' => TaxFeeType::TAX,
            'name' => 'VAT',
            'calculation_type' => CalculationType::PERCENTAGE,
            'value' => 21.0,
            'display_mode' => DisplayMode::SEPARATED,
            'applicable_gateways' => null, // Applies to all gateways
            'is_active' => true,
        ]);

        // Example: Service Fee 2.5% - integrated display (only for MercadoPago)
        $serviceFee = TaxAndFee::create([
            'type' => TaxFeeType::FEE,
            'name' => 'Service Fee',
            'calculation_type' => CalculationType::PERCENTAGE,
            'value' => 2.5,
            'display_mode' => DisplayMode::INTEGRATED,
            'applicable_gateways' => ['mercadopago'],
            'is_active' => true,
        ]);

        // Example: Fixed Processing Fee - separated display (only for Modo)
        $processingFee = TaxAndFee::create([
            'type' => TaxFeeType::FEE,
            'name' => 'Processing Fee',
            'calculation_type' => CalculationType::FIXED,
            'value' => 50.0,
            'display_mode' => DisplayMode::SEPARATED,
            'applicable_gateways' => ['modo'],
            'is_active' => true,
        ]);

        // Attach to event with sort order
        $event->taxesAndFees()->attach([
            $vat->id => ['sort_order' => 1],
            $serviceFee->id => ['sort_order' => 2],
            $processingFee->id => ['sort_order' => 3],
        ]);

        $this->command->info('Tax and Fee examples created successfully!');
    }
}
