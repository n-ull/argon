<?php

use Domain\EventManagement\ValueObjects\LocationInfo;

describe('LocationInfo ValueObject', function () {
    test('can be instantiated with required fields', function () {
        $location = new LocationInfo(
            address: '123 Main St',
            city: 'New York',
            country: 'USA'
        );

        expect($location->address)->toBe('123 Main St')
            ->and($location->city)->toBe('New York')
            ->and($location->country)->toBe('USA')
            ->and($location->mapLink)->toBeNull()
            ->and($location->site)->toBeNull();
    });

    test('can be instantiated with all fields', function () {
        $location = new LocationInfo(
            address: '456 Oak Ave',
            city: 'Los Angeles',
            country: 'USA',
            mapLink: 'https://maps.google.com/venue',
            site: 'https://venue.com'
        );

        expect($location->address)->toBe('456 Oak Ave')
            ->and($location->city)->toBe('Los Angeles')
            ->and($location->country)->toBe('USA')
            ->and($location->mapLink)->toBe('https://maps.google.com/venue')
            ->and($location->site)->toBe('https://venue.com');
    });

    test('can be created from array', function () {
        $data = [
            'address' => '789 Pine Rd',
            'city' => 'Chicago',
            'country' => 'USA',
            'mapLink' => 'https://maps.example.com',
            'site' => 'https://site.example.com',
        ];

        $location = LocationInfo::fromArray($data);

        expect($location)->toBeInstanceOf(LocationInfo::class)
            ->and($location->address)->toBe('789 Pine Rd')
            ->and($location->city)->toBe('Chicago')
            ->and($location->country)->toBe('USA')
            ->and($location->mapLink)->toBe('https://maps.example.com')
            ->and($location->site)->toBe('https://site.example.com');
    });

    test('fromArray handles null optional fields', function () {
        $data = [
            'address' => '321 Elm St',
            'city' => 'Boston',
            'country' => 'USA',
            'mapLink' => null,
            'site' => null,
        ];

        $location = LocationInfo::fromArray($data);

        expect($location->mapLink)->toBeNull()
            ->and($location->site)->toBeNull();
    });

    test('can be converted to array', function () {
        $location = new LocationInfo(
            address: '111 Maple Dr',
            city: 'Seattle',
            country: 'USA',
            mapLink: 'https://map.test',
            site: 'https://site.test'
        );

        $array = $location->toArray();

        expect($array)->toBe([
            'address' => '111 Maple Dr',
            'city' => 'Seattle',
            'country' => 'USA',
            'mapLink' => 'https://map.test',
            'site' => 'https://site.test',
        ]);
    });

    test('toArray includes null values', function () {
        $location = new LocationInfo(
            address: '222 Birch Ln',
            city: 'Portland',
            country: 'USA'
        );

        $array = $location->toArray();

        expect($array)->toHaveKey('mapLink')
            ->and($array)->toHaveKey('site')
            ->and($array['mapLink'])->toBeNull()
            ->and($array['site'])->toBeNull();
    });

    test('implements Arrayable interface', function () {
        $location = new LocationInfo('address', 'city', 'country');

        expect($location)->toBeInstanceOf(\Illuminate\Contracts\Support\Arrayable::class);
    });

    test('implements JsonSerializable interface', function () {
        $location = new LocationInfo('address', 'city', 'country');

        expect($location)->toBeInstanceOf(\JsonSerializable::class);
    });

    test('can be json serialized', function () {
        $location = new LocationInfo(
            address: '333 Cedar Ave',
            city: 'Denver',
            country: 'USA',
            mapLink: 'https://map.example.com',
            site: null
        );

        $json = json_encode($location);
        $decoded = json_decode($json, true);

        expect($decoded)->toBe([
            'address' => '333 Cedar Ave',
            'city' => 'Denver',
            'country' => 'USA',
            'mapLink' => 'https://map.example.com',
            'site' => null,
        ]);
    });

    test('jsonSerialize returns array', function () {
        $location = new LocationInfo('test address', 'test city', 'test country');

        expect($location->jsonSerialize())->toBeArray()
            ->and($location->jsonSerialize())->toBe($location->toArray());
    });
});

describe('LocationInfo Edge Cases', function () {
    test('handles empty strings', function () {
        $location = new LocationInfo('', '', '');

        expect($location->address)->toBe('')
            ->and($location->city)->toBe('')
            ->and($location->country)->toBe('');
    });

    test('handles special characters', function () {
        $location = new LocationInfo(
            address: '123 O\'Brien St & Co.',
            city: 'São Paulo',
            country: 'Brasil'
        );

        expect($location->address)->toBe('123 O\'Brien St & Co.')
            ->and($location->city)->toBe('São Paulo')
            ->and($location->country)->toBe('Brasil');
    });

    test('handles very long strings', function () {
        $longAddress = str_repeat('a', 500);
        $location = new LocationInfo($longAddress, 'city', 'country');

        expect(strlen($location->address))->toBe(500);
    });
});