<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\DTO;

use DateTimeImmutable;
use InvalidArgumentException;
use SolucaoInternet\Cadsus\ValueObjects\CadsusGender;

final readonly class CadsusSearchRequest
{
    private function __construct(
        public ?string $cpf = null,
        public ?string $cns = null,
        public ?string $nis = null,
        public ?string $name = null,
        public ?string $motherName = null,
        public ?DateTimeImmutable $birthDate = null,
        public ?CadsusGender $gender = null,
        public ?string $birthCityCode = null,
    ) {}

    public static function byCpf(string $cpf): self
    {
        return new self(cpf: self::digits($cpf, 11, 'CPF'));
    }

    public static function byCns(string $cns): self
    {
        return new self(cns: self::digits($cns, 15, 'CNS'));
    }

    public static function byNis(string $nis): self
    {
        return new self(nis: self::digits($nis, 11, 'NIS'));
    }

    public static function byDemographics(
        ?string $name = null,
        ?string $motherName = null,
        ?DateTimeImmutable $birthDate = null,
        ?CadsusGender $gender = null,
        ?string $birthCityCode = null,
    ): self {
        $values = array_filter([$name, $motherName, $birthDate, $gender, $birthCityCode], static fn(mixed $value): bool => $value !== null && $value !== '');

        if (count($values) < 2) {
            throw new InvalidArgumentException('A pesquisa demográfica exige pelo menos dois critérios.');
        }

        return new self(
            name: self::optionalText($name),
            motherName: self::optionalText($motherName),
            birthDate: $birthDate,
            gender: $gender,
            birthCityCode: $birthCityCode === null ? null : self::digits($birthCityCode, 6, 'Código do município'),
        );
    }

    public function type(): string
    {
        return match (true) {
            $this->cpf !== null => 'cpf',
            $this->cns !== null => 'cns',
            $this->nis !== null => 'nis',
            default => 'demographics',
        };
    }

    private static function digits(string $value, int $length, string $label): string
    {
        $normalized = preg_replace('/\D+/', '', $value) ?? '';

        if (strlen($normalized) !== $length) {
            throw new InvalidArgumentException(sprintf('%s deve conter %d dígitos.', $label, $length));
        }

        return $normalized;
    }

    private static function optionalText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
