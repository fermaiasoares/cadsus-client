<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SolucaoInternet\Cadsus\DTO\CadsusSearchRequest;
use SolucaoInternet\Cadsus\HL7\Oid;
use SolucaoInternet\Cadsus\HL7\PdqRequestBuilder;

final class PdqRequestBuilderTest extends TestCase
{
    public function testItBuildsAnXsdValidCpfRequest(): void
    {
        $xml = (new PdqRequestBuilder())
            ->build(CadsusSearchRequest::byCpf('000.000.000-00'), 'correlation');

        self::assertStringContainsString('root="' . Oid::CPF . '"', $xml);
        self::assertStringContainsString('extension="00000000000"', $xml);
    }

    public function testItEscapesXmlInput(): void
    {
        $xml = (new PdqRequestBuilder())
            ->build(CadsusSearchRequest::byDemographics(name: 'A & B', motherName: 'C < D'), 'correlation');

        self::assertStringContainsString('A &amp; B', $xml);
        self::assertStringNotContainsString('< D', $xml);
    }
}
