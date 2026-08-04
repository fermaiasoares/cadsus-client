<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Contracts;

interface LockInterface
{
    public function synchronized(string $scope, callable $callback): mixed;
}
