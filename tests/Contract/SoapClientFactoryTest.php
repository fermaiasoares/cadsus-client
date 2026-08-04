<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Tests\Contract;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SolucaoInternet\Cadsus\DTO\AccessToken;
use SolucaoInternet\Cadsus\Environment\CadsusEnvironment;
use SolucaoInternet\Cadsus\Soap\SoapClientFactory;

final class SoapClientFactoryTest extends TestCase
{
    public function testItCreatesAClientWithoutMakingANetworkRequest(): void
    {
        $client = (new SoapClientFactory())->create(
            CadsusEnvironment::HOMOLOGATION,
            new AccessToken('not-a-real-token', new DateTimeImmutable('+30 minutes')),
        );

        self::assertStringContainsString('PDQSupplier_PRPA_IN201305UV02', implode("\n", $client->__getFunctions()));
    }
}
