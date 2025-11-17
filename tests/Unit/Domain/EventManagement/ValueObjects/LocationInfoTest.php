<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\EventManagement\ValueObjects;

use Domain\EventManagement\ValueObjects\LocationInfo;
use PHPUnit\Framework\TestCase;

class LocationInfoTest extends TestCase
{
    /** @test */
    public function it_can_be_created_with_all_parameters(): void
    {
        $locationInfo = new LocationInfo(
            address: '123 Main St',
            city: 'New York',
            country: 'USA',
            mapLink: 'https://maps.google.com',
            site: 'https://venue.com'
        );

        $this->assertEquals('123 Main St', $locationInfo->address);
        $this->assertEquals('New York', $locationInfo->city);
        $this->assertEquals('USA', $locationInfo->country);
        $this->assertEquals('https://maps.google.com', $locationInfo->mapLink);
        $this->assertEquals('https://venue.com', $locationInfo->site);
    }

    /** @test */
    public function it_can_be_created_with_required_parameters_only(): void
    {
        $locationInfo = new LocationInfo(
            address: '456 Oak Ave',
            city: 'Boston',
            country: 'USA'
        );

        $this->assertEquals('456 Oak Ave', $locationInfo->address);
        $this->assertEquals('Boston', $locationInfo->city);
        $this->assertEquals('USA', $locationInfo->country);
        $this->assertNull($locationInfo->mapLink);
        $this->assertNull($locationInfo->site);
    }

    /** @test */
    public function it_can_convert_to_array(): void
    {
        $locationInfo = new LocationInfo(
            address: '789 Test Rd',
            city: 'Test City',
            country: 'Test Country',
            mapLink: 'https://map.test',
            site: 'https://site.test'
        );

        $array = $locationInfo->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('789 Test Rd', $array['address']);
        $this->assertEquals('Test City', $array['city']);
        $this->assertEquals('Test Country', $array['country']);
        $this->assertEquals('https://map.test', $array['mapLink']);
        $this->assertEquals('https://site.test', $array['site']);
    }

    /** @test */
    public function it_can_be_created_from_array(): void
    {
        $data = [
            'address' => '321 Array St',
            'city' => 'Array City',
            'country' => 'Array Country',
            'mapLink' => 'https://array.map',
            'site' => 'https://array.site',
        ];

        $locationInfo = LocationInfo::fromArray($data);

        $this->assertEquals('321 Array St', $locationInfo->address);
        $this->assertEquals('Array City', $locationInfo->city);
        $this->assertEquals('Array Country', $locationInfo->country);
        $this->assertEquals('https://array.map', $locationInfo->mapLink);
        $this->assertEquals('https://array.site', $locationInfo->site);
    }

    /** @test */
    public function it_can_be_serialized_to_json(): void
    {
        $locationInfo = new LocationInfo(
            address: 'JSON Address',
            city: 'JSON City',
            country: 'JSON Country'
        );

        $json = json_encode($locationInfo);
        $decoded = json_decode($json, true);

        $this->assertEquals('JSON Address', $decoded['address']);
        $this->assertEquals('JSON City', $decoded['city']);
        $this->assertEquals('JSON Country', $decoded['country']);
    }

    /** @test */
    public function to_array_includes_null_values(): void
    {
        $locationInfo = new LocationInfo(
            address: '999 Min St',
            city: 'Min City',
            country: 'Min Country'
        );

        $array = $locationInfo->toArray();

        $this->assertArrayHasKey('mapLink', $array);
        $this->assertArrayHasKey('site', $array);
        $this->assertNull($array['mapLink']);
        $this->assertNull($array['site']);
    }

    /** @test */
    public function it_handles_international_characters(): void
    {
        $locationInfo = new LocationInfo(
            address: 'Straße 123',
            city: 'München',
            country: 'Deutschland'
        );

        $this->assertEquals('Straße 123', $locationInfo->address);
        $this->assertEquals('München', $locationInfo->city);
        $this->assertEquals('Deutschland', $locationInfo->country);
    }

    /** @test */
    public function it_handles_long_address_strings(): void
    {
        $longAddress = str_repeat('Long Address Line ', 50);

        $locationInfo = new LocationInfo(
            address: $longAddress,
            city: 'City',
            country: 'Country'
        );

        $this->assertEquals($longAddress, $locationInfo->address);
    }

    /** @test */
    public function it_handles_url_formats_in_optional_fields(): void
    {
        $locationInfo = new LocationInfo(
            address: 'URL Test',
            city: 'URL City',
            country: 'URL Country',
            mapLink: 'https://maps.google.com/place?id=123&name=venue',
            site: 'https://example.com/page?param=value#section'
        );

        $this->assertStringContainsString('maps.google.com', $locationInfo->mapLink);
        $this->assertStringContainsString('example.com', $locationInfo->site);
    }
}
