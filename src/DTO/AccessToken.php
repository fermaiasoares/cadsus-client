<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\DTO;

use DateTimeImmutable;

final readonly class AccessToken
{
    public function __construct(
        private string $value,
        public DateTimeImmutable $expiresAt,
        public string $type = 'jwt',
    ) {}

    public function authorizationHeader(): string
    {
        return $this->type . ' ' . $this->value;
    }

    public function isUsableAt(DateTimeImmutable $now, int $safetyMarginSeconds = 60): bool
    {
        return $this->expiresAt->getTimestamp() - $safetyMarginSeconds > $now->getTimestamp();
    }
}
