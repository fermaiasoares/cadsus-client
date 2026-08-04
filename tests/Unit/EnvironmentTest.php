<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SolucaoInternet\Cadsus\Environment\CadsusEnvironment;

final class EnvironmentTest extends TestCase
{
    public function testOfficialEndpointsAreConfigured(): void
    {
        self::assertSame('https://ehr-auth-hmg.saude.gov.br/api/osb/token', CadsusEnvironment::HOMOLOGATION->authenticationEndpoint());

        self::assertSame('https://servicoshm.saude.gov.br/cadsus/v2/PDQSupplierJWT', CadsusEnvironment::HOMOLOGATION->pdqEndpoint());

        self::assertSame('https://ehr-auth.saude.gov.br/api/osb/token', CadsusEnvironment::PRODUCTION->authenticationEndpoint());

        self::assertSame('https://servicos.saude.gov.br/cadsus/v2/PDQSupplierJWT', CadsusEnvironment::PRODUCTION->pdqEndpoint());
    }
}
