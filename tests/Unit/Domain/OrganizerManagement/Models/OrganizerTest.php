<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\OrganizerManagement\Models;

use Domain\EventManagement\Models\Event;
use Domain\OrganizerManagement\Models\Organizer;
use Domain\OrganizerManagement\Models\OrganizerSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fillable_attributes(): void
    {
        $expected = [
            'name',
            'email',
            'phone',
            'logo',
        ];

        $organizer = new Organizer;
        $this->assertEquals($expected, $organizer->getFillable());
    }

    /** @test */
    public function it_has_events_relationship(): void
    {
        $organizer = Organizer::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $organizer->events()
        );
    }

    /** @test */
    public function it_has_settings_relationship(): void
    {
        $organizer = Organizer::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasOne::class,
            $organizer->settings()
        );
    }

    /** @test */
    public function it_returns_default_settings_when_no_settings_exist(): void
    {
        $organizer = Organizer::factory()->create();

        $settings = $organizer->settings;

        $this->assertInstanceOf(OrganizerSettings::class, $settings);
        $this->assertEquals('internal', $settings->raise_money_method);
        $this->assertFalse($settings->is_modo_active);
        $this->assertFalse($settings->is_mercadopago_active);
    }

    /** @test */
    public function it_can_create_organizer_with_all_fields(): void
    {
        $organizer = Organizer::create([
            'name' => 'Test Organizer',
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'logo' => '/path/to/logo.png',
        ]);

        $this->assertInstanceOf(Organizer::class, $organizer);
        $this->assertEquals('Test Organizer', $organizer->name);
        $this->assertEquals('test@example.com', $organizer->email);
        $this->assertEquals('+1234567890', $organizer->phone);
        $this->assertEquals('/path/to/logo.png', $organizer->logo);
    }

    /** @test */
    public function it_can_create_organizer_with_only_required_fields(): void
    {
        $organizer = Organizer::create([
            'name' => 'Minimal Organizer',
        ]);

        $this->assertEquals('Minimal Organizer', $organizer->name);
        $this->assertNull($organizer->email);
        $this->assertNull($organizer->phone);
        $this->assertNull($organizer->logo);
    }

    /** @test */
    public function it_can_have_multiple_events(): void
    {
        $organizer = Organizer::factory()->create();

        Event::factory()->count(3)->create(['organizer_id' => $organizer->id]);

        $this->assertEquals(3, $organizer->events()->count());
    }

    /** @test */
    public function it_has_timestamps(): void
    {
        $organizer = Organizer::factory()->create();

        $this->assertNotNull($organizer->created_at);
        $this->assertNotNull($organizer->updated_at);
    }

    /** @test */
    public function it_can_update_organizer_details(): void
    {
        $organizer = Organizer::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $organizer->update([
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $this->assertEquals('Updated Name', $organizer->fresh()->name);
        $this->assertEquals('updated@example.com', $organizer->fresh()->email);
    }

    /** @test */
    public function it_can_have_null_optional_fields(): void
    {
        $organizer = Organizer::factory()->create([
            'email' => null,
            'phone' => null,
            'logo' => null,
        ]);

        $this->assertNull($organizer->email);
        $this->assertNull($organizer->phone);
        $this->assertNull($organizer->logo);
    }
}
