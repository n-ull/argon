<?php

use Domain\EventManagement\Casts\LocationInfoJson;
use Domain\EventManagement\Models\Event;
use Domain\EventManagement\ValueObjects\LocationInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('LocationInfoJson Cast', function () {
    test('casts json string to LocationInfo on get', function () {
        $cast = new LocationInfoJson;
        $model = new Event;

        $jsonValue = json_encode([
            'address' => '123 Test St',
            'city' => 'Test City',
            'country' => 'Test Country',
            'mapLink' => 'https://maps.test',
            'site' => 'https://site.test',
        ]);

        $result = $cast->get($model, 'location_info', $jsonValue, []);

        expect($result)->toBeInstanceOf(LocationInfo::class)
            ->and($result->address)->toBe('123 Test St')
            ->and($result->city)->toBe('Test City')
            ->and($result->country)->toBe('Test Country')
            ->and($result->mapLink)->toBe('https://maps.test')
            ->and($result->site)->toBe('https://site.test');
    });

    test('returns null when value is null on get', function () {
        $cast = new LocationInfoJson;
        $model = new Event;

        $result = $cast->get($model, 'location_info', null, []);

        expect($result)->toBeNull();
    });

    test('casts LocationInfo to json string on set', function () {
        $cast = new LocationInfoJson;
        $model = new Event;

        $locationInfo = new LocationInfo(
            address: '456 Oak Ave',
            city: 'Springfield',
            country: 'USA',
            mapLink: 'https://map.example.com',
            site: null
        );

        $result = $cast->set($model, 'location_info', $locationInfo, []);
        $decoded = json_decode($result, true);

        expect($result)->toBeString()
            ->and($decoded)->toBe([
                'address' => '456 Oak Ave',
                'city' => 'Springfield',
                'country' => 'USA',
                'mapLink' => 'https://map.example.com',
                'site' => null,
            ]);
    });

    test('casts array to json string on set', function () {
        $cast = new LocationInfoJson;
        $model = new Event;

        $array = [
            'address' => '789 Pine Rd',
            'city' => 'Portland',
            'country' => 'Canada',
            'mapLink' => null,
            'site' => 'https://venue.ca',
        ];

        $result = $cast->set($model, 'location_info', $array, []);
        $decoded = json_decode($result, true);

        expect($result)->toBeString()
            ->and($decoded)->toBe($array);
    });

    test('returns null when value is null on set', function () {
        $cast = new LocationInfoJson;
        $model = new Event;

        $result = $cast->set($model, 'location_info', null, []);

        expect($result)->toBeNull();
    });

    test('returns value as-is when already a string', function () {
        $cast = new LocationInfoJson;
        $model = new Event;

        $jsonString = '{"address":"test","city":"test","country":"test"}';

        $result = $cast->set($model, 'location_info', $jsonString, []);

        expect($result)->toBe($jsonString);
    });

    test('handles round-trip conversion', function () {
        $event = Event::factory()->create([
            'location_info' => [
                'address' => 'Round Trip Address',
                'city' => 'Round Trip City',
                'country' => 'Round Trip Country',
                'mapLink' => 'https://roundtrip.map',
                'site' => 'https://roundtrip.site',
            ],
        ]);

        $freshEvent = $event->fresh();

        expect($freshEvent->location_info)->toBeInstanceOf(LocationInfo::class)
            ->and($freshEvent->location_info->address)->toBe('Round Trip Address')
            ->and($freshEvent->location_info->city)->toBe('Round Trip City')
            ->and($freshEvent->location_info->country)->toBe('Round Trip Country');
    });
});
