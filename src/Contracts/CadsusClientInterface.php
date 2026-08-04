<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Contracts;

use SolucaoInternet\Cadsus\DTO\CadsusCredentials;

interface CadsusClientInterface
{
    public function forCredentials(CadsusCredentials $credentials): CredentialedCadsusClientInterface;
}
