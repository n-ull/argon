<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\OrganizerManagement\Models;

use Domain\OrganizerManagement\Models\Organizer;
use Domain\OrganizerManagement\Models\OrganizerSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizerSettingsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fillable_attributes(): void
    {
        $expected = [
            'organizer_id',
            'raise_money_method',
            'raise_money_account',
            'is_modo_active',
            'is_mercadopago_active',
        ];

        $settings = new OrganizerSettings();
        $this->assertEquals($expected, $settings->getFillable());
    }

    /** @test */
    public function it_belongs_to_an_organizer(): void
    {
        $organizer = Organizer::factory()->create();
        $settings = OrganizerSettings::factory()->create(['organizer_id' => $organizer->id]);

        $this->assertInstanceOf(Organizer::class, $settings->organizer);
        $this->assertEquals($organizer->id, $settings->organizer->id);
    }

    /** @test */
    public function it_can_create_settings_with_all_fields(): void
    {
        $organizer = Organizer::factory()->create();
        
        $settings = OrganizerSettings::create([
            'organizer_id' => $organizer->id,
            'raise_money_method' => 'external',
            'raise_money_account' => 'account123',
            'is_modo_active' => true,
            'is_mercadopago_active' => true,
        ]);

        $this->assertInstanceOf(OrganizerSettings::class, $settings);
        $this->assertEquals('external', $settings->raise_money_method);
        $this->assertEquals('account123', $settings->raise_money_account);
        $this->assertTrue($settings->is_modo_active);
        $this->assertTrue($settings->is_mercadopago_active);
    }

    /** @test */
    public function it_can_toggle_payment_methods(): void
    {
        $settings = OrganizerSettings::factory()->create([
            'is_modo_active' => false,
            'is_mercadopago_active' => false,
        ]);

        $this->assertFalse($settings->is_modo_active);
        $this->assertFalse($settings->is_mercadopago_active);

        $settings->update([
            'is_modo_active' => true,
            'is_mercadopago_active' => true,
        ]);

        $this->assertTrue($settings->fresh()->is_modo_active);
        $this->assertTrue($settings->fresh()->is_mercadopago_active);
    }

    /** @test */
    public function it_can_store_raise_money_account(): void
    {
        $settings = OrganizerSettings::factory()->create([
            'raise_money_account' => 'test_account_123',
        ]);

        $this->assertEquals('test_account_123', $settings->raise_money_account);
    }

    /** @test */
    public function it_can_have_different_raise_money_methods(): void
    {
        $internalSettings = OrganizerSettings::factory()->create(['raise_money_method' => 'internal']);
        $externalSettings = OrganizerSettings::factory()->create(['raise_money_method' => 'external']);

        $this->assertEquals('internal', $internalSettings->raise_money_method);
        $this->assertEquals('external', $externalSettings->raise_money_method);
    }

    /** @test */
    public function it_has_timestamps(): void
    {
        $settings = OrganizerSettings::factory()->create();

        $this->assertNotNull($settings->created_at);
        $this->assertNotNull($settings->updated_at);
    }

    /** @test */
    public function one_organizer_can_have_only_one_settings(): void
    {
        $organizer = Organizer::factory()->create();
        
        OrganizerSettings::factory()->create(['organizer_id' => $organizer->id]);

        $settings = $organizer->settings;

        $this->assertInstanceOf(OrganizerSettings::class, $settings);
    }
}