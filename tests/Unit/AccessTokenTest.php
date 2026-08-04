<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SolucaoInternet\Cadsus\DTO\AccessToken;

final class AccessTokenTest extends TestCase
{
    public function testItHonorsTheSafetyMargin(): void
    {
        $now = new DateTimeImmutable('2026-01-01T12:00:00Z');
        $token = new AccessToken('secret', $now->modify('+50 seconds'));

        self::assertFalse($token->isUsableAt($now, 60));
        self::assertTrue($token->isUsableAt($now, 30));
    }
}
