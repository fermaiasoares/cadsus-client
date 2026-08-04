<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\DTO;

final readonly class CadsusContact
{
    public function __construct(public string $type, public string $value, public ?string $use = null) {}
}
