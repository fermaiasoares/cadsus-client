<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\DTO;

use DateTimeImmutable;
use SolucaoInternet\Cadsus\ValueObjects\CadsusGender;

final readonly class CadsusCitizen
{
    /** @param list<CadsusIdentifier> $identifiers @param list<CadsusAddress> $addresses @param list<CadsusContact> $contacts */
    public function __construct(
        public array $identifiers,
        public ?string $name,
        public ?string $socialName,
        public ?string $motherName,
        public ?DateTimeImmutable $birthDate,
        public ?CadsusGender $gender,
        public array $addresses = [],
        public array $contacts = [],
        public ?string $fatherName = null,
        public ?string $raceCode = null,
        public ?string $birthCityCode = null,
        public ?string $birthCountryCode = null,
        public ?string $language = null,
        public ?bool $preferredLanguage = null,
        public ?string $status = null,
        public ?string $confidentialityCode = null,
        public ?int $matchScore = null,
        /** @var list<CadsusIdentifier> */
        public array $sourceIdentifiers = [],
    ) {}
}
