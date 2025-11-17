<?php

namespace Domain\EventManagement\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class LocationInfo implements Arrayable, JsonSerializable
{
    public function __construct(
        public string $address,
        public string $city,
        public string $country,
        public ?string $mapLink = null,
        public ?string $site = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['address'],
            $data['city'],
            $data['country'],
            $data['mapLink'],
            $data['site'],
        );
    }

    public function toArray(): array
    {
        return [
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'mapLink' => $this->mapLink,
            'site' => $this->site,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
