<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\DTO;

final readonly class CadsusAddress
{
    public function __construct(
        public ?string $street = null,
        public ?string $number = null,
        public ?string $complement = null,
        public ?string $district = null,
        public ?string $city = null,
        public ?string $cityCode = null,
        public ?string $state = null,
        public ?string $postalCode = null,
        public ?string $countryCode = null,
        public ?string $use = null,
        public ?string $streetTypeCode = null,
    ) {}
}
