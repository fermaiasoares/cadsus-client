<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\DTO;

final readonly class CadsusSearchResult
{
    /** @param list<CadsusCitizen> $citizens @param list<string> $warnings */
    public function __construct(
        public array $citizens,
        public int $total,
        public bool $hasMore,
        public ?string $continuationPointer,
        public string $correlationId,
        public array $warnings = [],
    ) {}

    public function isEmpty(): bool
    {
        return $this->citizens === [];
    }
}
