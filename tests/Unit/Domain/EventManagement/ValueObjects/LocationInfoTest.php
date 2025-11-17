<?php

use Domain\EventManagement\ValueObjects\LocationInfo;

describe('LocationInfo ValueObject', function () {
    it('can be instantiated with all properties', function () {
        $locationInfo = new LocationInfo(
            address: '123 Main Street',
            city: 'New York',
            country: 'USA',
            mapLink: 'https://maps.google.com/example',
            site: 'venue.com'
        );

        expect($locationInfo->address)->toBe('123 Main Street')
            ->and($locationInfo->city)->toBe('New York')
            ->and($locationInfo->country)->toBe('USA')
            ->and($locationInfo->mapLink)->toBe('https://maps.google.com/example')
            ->and($locationInfo->site)->toBe('venue.com');
    });

    it('can be instantiated without optional properties', function () {
        $locationInfo = new LocationInfo(
            address: '456 Oak Avenue',
            city: 'Los Angeles',
            country: 'USA'
        );

        expect($locationInfo->address)->toBe('456 Oak Avenue')
            ->and($locationInfo->city)->toBe('Los Angeles')
            ->and($locationInfo->country)->toBe('USA')
            ->and($locationInfo->mapLink)->toBeNull()
            ->and($locationInfo->site)->toBeNull();
    });

    it('can be created from array with all fields', function () {
        $data = [
            'address' => '789 Pine Road',
            'city' => 'Chicago',
            'country' => 'USA',
            'mapLink' => 'https://maps.example.com',
            'site' => 'example-venue.com',
        ];

        $locationInfo = LocationInfo::fromArray($data);

        expect($locationInfo->address)->toBe('789 Pine Road')
            ->and($locationInfo->city)->toBe('Chicago')
            ->and($locationInfo->country)->toBe('USA')
            ->and($locationInfo->mapLink)->toBe('https://maps.example.com')
            ->and($locationInfo->site)->toBe('example-venue.com');
    });

    it('can be created from array with only required fields', function () {
        $data = [
            'address' => '321 Elm Street',
            'city' => 'Boston',
            'country' => 'USA',
            'mapLink' => null,
            'site' => null,
        ];

        $locationInfo = LocationInfo::fromArray($data);

        expect($locationInfo->address)->toBe('321 Elm Street')
            ->and($locationInfo->city)->toBe('Boston')
            ->and($locationInfo->country)->toBe('USA')
            ->and($locationInfo->mapLink)->toBeNull()
            ->and($locationInfo->site)->toBeNull();
    });

    it('converts to array correctly with all fields', function () {
        $locationInfo = new LocationInfo(
            address: '555 Broadway',
            city: 'Seattle',
            country: 'USA',
            mapLink: 'https://maps.google.com/seattle',
            site: 'seattle-venue.com'
        );

        $array = $locationInfo->toArray();

        expect($array)->toBe([
            'address' => '555 Broadway',
            'city' => 'Seattle',
            'country' => 'USA',
            'mapLink' => 'https://maps.google.com/seattle',
            'site' => 'seattle-venue.com',
        ]);
    });

    it('converts to array correctly with null optional fields', function () {
        $locationInfo = new LocationInfo(
            address: '777 Market Street',
            city: 'San Francisco',
            country: 'USA'
        );

        $array = $locationInfo->toArray();

        expect($array)->toBe([
            'address' => '777 Market Street',
            'city' => 'San Francisco',
            'country' => 'USA',
            'mapLink' => null,
            'site' => null,
        ]);
    });

    it('is json serializable', function () {
        $locationInfo = new LocationInfo(
            address: '999 Tech Drive',
            city: 'Austin',
            country: 'USA',
            mapLink: 'https://maps.example.com/austin',
            site: 'tech-center.com'
        );

        $json = json_encode($locationInfo);
        $decoded = json_decode($json, true);

        expect($decoded)->toBe([
            'address' => '999 Tech Drive',
            'city' => 'Austin',
            'country' => 'USA',
            'mapLink' => 'https://maps.example.com/austin',
            'site' => 'tech-center.com',
        ]);
    });

    it('handles special characters in properties', function () {
        $locationInfo = new LocationInfo(
            address: '123 Straße & Gasse',
            city: 'München',
            country: 'Deutschland',
            mapLink: 'https://maps.example.com?q=München&zoom=15',
            site: 'münchen-venue.de'
        );

        expect($locationInfo->address)->toBe('123 Straße & Gasse')
            ->and($locationInfo->city)->toBe('München')
            ->and($locationInfo->country)->toBe('Deutschland');
    });

    it('can roundtrip through array conversion', function () {
        $original = new LocationInfo(
            address: '456 Test Avenue',
            city: 'Test City',
            country: 'Test Country',
            mapLink: 'https://test.map',
            site: 'test.site'
        );

        $array = $original->toArray();
        $restored = LocationInfo::fromArray($array);

        expect($restored->address)->toBe($original->address)
            ->and($restored->city)->toBe($original->city)
            ->and($restored->country)->toBe($original->country)
            ->and($restored->mapLink)->toBe($original->mapLink)
            ->and($restored->site)->toBe($original->site);
    });

    it('handles empty strings correctly', function () {
        $locationInfo = new LocationInfo(
            address: '',
            city: '',
            country: '',
            mapLink: '',
            site: ''
        );

        expect($locationInfo->address)->toBe('')
            ->and($locationInfo->city)->toBe('')
            ->and($locationInfo->country)->toBe('')
            ->and($locationInfo->mapLink)->toBe('')
            ->and($locationInfo->site)->toBe('');
    });
});