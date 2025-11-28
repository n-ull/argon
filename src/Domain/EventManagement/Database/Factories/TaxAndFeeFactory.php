<?php

namespace Domain\EventManagement\Database\Factories;

use Domain\EventManagement\Enums\CalculationType;
use Domain\EventManagement\Enums\DisplayMode;
use Domain\EventManagement\Enums\TaxFeeType;
use Domain\EventManagement\Models\TaxAndFee;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxAndFeeFactory extends Factory
{
    protected $model = TaxAndFee::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement([TaxFeeType::TAX, TaxFeeType::FEE]),
            'name' => $this->faker->randomElement(['VAT', 'Service Fee', 'Processing Fee', 'Platform Fee']),
            'calculation_type' => $this->faker->randomElement([CalculationType::PERCENTAGE, CalculationType::FIXED]),
            'value' => $this->faker->randomFloat(2, 1, 25),
            'display_mode' => $this->faker->randomElement([DisplayMode::SEPARATED, DisplayMode::INTEGRATED]),
            'applicable_gateways' => null,
            'is_active' => true,
        ];
    }

    /**
     * Create a tax (VAT, Sales Tax, etc.)
     */
    public function tax(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TaxFeeType::TAX,
            'name' => $this->faker->randomElement(['VAT', 'Sales Tax', 'GST']),
        ]);
    }

    /**
     * Create a fee
     */
    public function fee(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TaxFeeType::FEE,
            'name' => $this->faker->randomElement(['Service Fee', 'Processing Fee', 'Platform Fee']),
        ]);
    }

    /**
     * Create a percentage-based charge
     */
    public function percentage(?float $value = null): static
    {
        return $this->state(fn (array $attributes) => [
            'calculation_type' => CalculationType::PERCENTAGE,
            'value' => $value ?? $this->faker->randomFloat(2, 1, 25),
        ]);
    }

    /**
     * Create a fixed amount charge
     */
    public function fixed(?float $value = null): static
    {
        return $this->state(fn (array $attributes) => [
            'calculation_type' => CalculationType::FIXED,
            'value' => $value ?? $this->faker->randomFloat(2, 10, 100),
        ]);
    }

    /**
     * Create a separated display charge
     */
    public function separated(): static
    {
        return $this->state(fn (array $attributes) => [
            'display_mode' => DisplayMode::SEPARATED,
        ]);
    }

    /**
     * Create an integrated display charge
     */
    public function integrated(): static
    {
        return $this->state(fn (array $attributes) => [
            'display_mode' => DisplayMode::INTEGRATED,
        ]);
    }

    /**
     * Make it apply to specific gateways
     */
    public function forGateways(array $gateways): static
    {
        return $this->state(fn (array $attributes) => [
            'applicable_gateways' => $gateways,
        ]);
    }

    /**
     * Make it inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a typical VAT tax
     */
    public function vat(float $rate = 21.0): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TaxFeeType::TAX,
            'name' => "VAT ({$rate}%)",
            'calculation_type' => CalculationType::PERCENTAGE,
            'value' => $rate,
            'display_mode' => DisplayMode::SEPARATED,
            'applicable_gateways' => null,
        ]);
    }

    /**
     * Create a typical MercadoPago fee
     */
    public function mercadoPagoFee(float $rate = 3.5): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TaxFeeType::FEE,
            'name' => 'MercadoPago Fee',
            'calculation_type' => CalculationType::PERCENTAGE,
            'value' => $rate,
            'display_mode' => DisplayMode::INTEGRATED,
            'applicable_gateways' => ['mercadopago'],
        ]);
    }

    /**
     * Create a typical Modo fee
     */
    public function modoFee(float $amount = 50.0): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TaxFeeType::FEE,
            'name' => 'Modo Transaction Fee',
            'calculation_type' => CalculationType::FIXED,
            'value' => $amount,
            'display_mode' => DisplayMode::SEPARATED,
            'applicable_gateways' => ['modo'],
        ]);
    }
}
