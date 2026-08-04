<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\DTO;

final readonly class CadsusIdentifier
{
    public function __construct(
        public string $type,
        public string $value,
        public string $oid,
        public ?string $assigningAuthorityName = null,
    ) {}
}
