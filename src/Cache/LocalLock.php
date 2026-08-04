<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Cache;

use SolucaoInternet\Cadsus\Contracts\LockInterface;

final class LocalLock implements LockInterface
{
    public function synchronized(string $scope, callable $callback): mixed
    {
        return $callback();
    }
}
