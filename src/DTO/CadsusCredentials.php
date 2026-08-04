<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\DTO;

use InvalidArgumentException;
use SolucaoInternet\Cadsus\Environment\CadsusEnvironment;

final readonly class CadsusCredentials
{
    public function __construct(
        public string $identifier,
        public string $certificateContents,
        public ?string $certificatePassword = null,
        public CadsusEnvironment $environment = CadsusEnvironment::PRODUCTION,
    ) {
        if (trim($identifier) === '') {
            throw new InvalidArgumentException('O identificador da credencial não pode ser vazio.');
        }

        if ($certificateContents === '') {
            throw new InvalidArgumentException('O conteúdo do certificado não pode ser vazio.');
        }
    }

    public function cacheScope(): string
    {
        return hash('sha256', $this->environment->value . "\0" . $this->identifier);
    }
}
