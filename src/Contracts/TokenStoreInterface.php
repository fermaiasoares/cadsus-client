<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Contracts;

use SolucaoInternet\Cadsus\DTO\AccessToken;

interface TokenStoreInterface
{
    public function get(string $scope): ?AccessToken;

    public function put(string $scope, AccessToken $token): void;

    public function forget(string $scope): void;
}
