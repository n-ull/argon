<?php

use Domain\EventManagement\Casts\LocationInfoJson;
use Domain\EventManagement\Models\Event;
use Domain\EventManagement\ValueObjects\LocationInfo;
use Illuminate\Database\Eloquent\Model;

describe('LocationInfoJson Cast', function () {
    beforeEach(function () {
        $this->cast = new LocationInfoJson();
        $this->model = new class extends Model {
            protected $table = 'events';
        };
    });

    it('casts json string to LocationInfo object', function () {
        $json = json_encode([
            'address' => '123 Main St',
            'city' => 'New York',
            'country' => 'USA',
            'mapLink' => 'https://maps.example.com',
            'site' => 'venue.com',
        ]);

        $result = $this->cast->get($this->model, 'location_info', $json, []);

        expect($result)->toBeInstanceOf(LocationInfo::class)
            ->and($result->address)->toBe('123 Main St')
            ->and($result->city)->toBe('New York')
            ->and($result->country)->toBe('USA')
            ->and($result->mapLink)->toBe('https://maps.example.com')
            ->and($result->site)->toBe('venue.com');
    });

    it('returns null when value is null', function () {
        $result = $this->cast->get($this->model, 'location_info', null, []);

        expect($result)->toBeNull();
    });

    it('handles json with null optional fields', function () {
        $json = json_encode([
            'address' => '456 Oak Ave',
            'city' => 'Boston',
            'country' => 'USA',
            'mapLink' => null,
            'site' => null,
        ]);

        $result = $this->cast->get($this->model, 'location_info', $json, []);

        expect($result)->toBeInstanceOf(LocationInfo::class)
            ->and($result->address)->toBe('456 Oak Ave')
            ->and($result->mapLink)->toBeNull()
            ->and($result->site)->toBeNull();
    });

    it('converts LocationInfo object to json string for storage', function () {
        $locationInfo = new LocationInfo(
            address: '789 Pine Rd',
            city: 'Seattle',
            country: 'USA',
            mapLink: 'https://maps.test.com',
            site: 'test-venue.com'
        );

        $result = $this->cast->set($this->model, 'location_info', $locationInfo, []);

        $decoded = json_decode($result, true);
        expect($decoded)->toBe([
            'address' => '789 Pine Rd',
            'city' => 'Seattle',
            'country' => 'USA',
            'mapLink' => 'https://maps.test.com',
            'site' => 'test-venue.com',
        ]);
    });

    it('converts array to json string for storage', function () {
        $array = [
            'address' => '321 Elm St',
            'city' => 'Chicago',
            'country' => 'USA',
            'mapLink' => 'https://maps.chicago.com',
            'site' => 'chicago-venue.com',
        ];

        $result = $this->cast->set($this->model, 'location_info', $array, []);

        $decoded = json_decode($result, true);
        expect($decoded)->toBe($array);
    });

    it('returns null when setting null value', function () {
        $result = $this->cast->set($this->model, 'location_info', null, []);

        expect($result)->toBeNull();
    });

    it('returns value unchanged if already a string', function () {
        $jsonString = '{"address":"Test","city":"City","country":"Country"}';

        $result = $this->cast->set($this->model, 'location_info', $jsonString, []);

        expect($result)->toBe($jsonString);
    });

    it('can roundtrip through get and set', function () {
        $original = new LocationInfo(
            address: 'Roundtrip Address',
            city: 'Roundtrip City',
            country: 'Roundtrip Country',
            mapLink: 'https://roundtrip.map',
            site: 'roundtrip.site'
        );

        $stored = $this->cast->set($this->model, 'location_info', $original, []);
        $retrieved = $this->cast->get($this->model, 'location_info', $stored, []);

        expect($retrieved)->toBeInstanceOf(LocationInfo::class)
            ->and($retrieved->address)->toBe($original->address)
            ->and($retrieved->city)->toBe($original->city)
            ->and($retrieved->country)->toBe($original->country)
            ->and($retrieved->mapLink)->toBe($original->mapLink)
            ->and($retrieved->site)->toBe($original->site);
    });

    it('handles special characters in json', function () {
        $json = json_encode([
            'address' => 'Straße & Gässchen',
            'city' => 'München',
            'country' => 'Deutschland',
            'mapLink' => 'https://maps.example.com?q=München&zoom=15',
            'site' => 'münchen.de',
        ]);

        $result = $this->cast->get($this->model, 'location_info', $json, []);

        expect($result)->toBeInstanceOf(LocationInfo::class)
            ->and($result->address)->toBe('Straße & Gässchen')
            ->and($result->city)->toBe('München');
    });
});