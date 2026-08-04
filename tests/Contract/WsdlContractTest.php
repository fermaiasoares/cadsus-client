<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Tests\Contract;

use PHPUnit\Framework\TestCase;
use SoapClient;
use SolucaoInternet\Cadsus\Soap\ContractPaths;

final class WsdlContractTest extends TestCase
{
    public function testWsdlLoadsAndExposesTheExpectedOperations(): void
    {
        $client = new SoapClient(ContractPaths::wsdl(), [
            'cache_wsdl' => WSDL_CACHE_NONE,
            'exceptions' => true,
        ]);

        $functions = implode("\n", $client->__getFunctions());

        self::assertStringContainsString('PDQSupplier_PRPA_IN201305UV02', $functions);
        self::assertStringContainsString('PDQSupplier_QUQI_IN000003UV01_Continue', $functions);
        self::assertStringContainsString('PDQSupplier_QUQI_IN000003UV01_Cancel', $functions);
        self::assertGreaterThan(3000, count($client->__getTypes()));
    }

    public function testPrimaryTypesArePresent(): void
    {
        $client = new SoapClient(ContractPaths::wsdl(), ['cache_wsdl' => WSDL_CACHE_NONE]);
        $types = implode("\n", $client->__getTypes());

        self::assertStringContainsString('PRPA_IN201305UV02', $types);
        self::assertStringContainsString('PRPA_IN201306UV02', $types);
        self::assertStringContainsString('QUQI_IN000003UV01', $types);
    }
}
