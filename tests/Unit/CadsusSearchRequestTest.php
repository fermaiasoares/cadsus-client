<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Tests\Unit;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SolucaoInternet\Cadsus\DTO\CadsusSearchRequest;

final class CadsusSearchRequestTest extends TestCase
{
    public function testItNormalizesCpf(): void
    {
        $request = CadsusSearchRequest::byCpf('000.000.000-00');

        self::assertSame('00000000000', $request->cpf);
        self::assertSame('cpf', $request->type());
    }

    public function testItNormalizesCns(): void
    {
        $request = CadsusSearchRequest::byCns('000 0000 0000 0000');

        self::assertSame('000000000000000', $request->cns);
    }

    public function testItRejectsInsufficientDemographicCriteria(): void
    {
        $this->expectException(InvalidArgumentException::class);

        CadsusSearchRequest::byDemographics(name: 'JOÃO DA SILVA');
    }

    public function testItAcceptsDemographicCriteria(): void
    {
        $request = CadsusSearchRequest::byDemographics(
            name: 'JOÃO DA SILVA',
            birthDate: new DateTimeImmutable('1950-01-10'),
        );

        self::assertSame('demographics', $request->type());
    }
}
