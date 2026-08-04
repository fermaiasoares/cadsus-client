<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Contracts;

use SolucaoInternet\Cadsus\DTO\CadsusSearchRequest;
use SolucaoInternet\Cadsus\DTO\CadsusSearchResult;

interface CredentialedCadsusClientInterface
{
    public function search(CadsusSearchRequest $request): CadsusSearchResult;
}
