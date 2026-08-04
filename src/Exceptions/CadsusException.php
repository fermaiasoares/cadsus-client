<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Exceptions;

use RuntimeException;
use Throwable;

class CadsusException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $category = 'unexpected',
        public readonly bool $retryable = false,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
