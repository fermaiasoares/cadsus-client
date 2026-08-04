<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SolucaoInternet\Cadsus\Authentication\AuthenticationClient;
use SolucaoInternet\Cadsus\DTO\CadsusCredentials;

final class AuthenticationClientTest extends TestCase
{
    public function testItAuthenticatesWithMtlsOptionsAndParsesTtl(): void
    {
        $before = time();
        $seen = [];
        $mock = new MockHandler([function ($request, $options) use (&$seen) {
            $seen = $options;
            return new Response(200, [], json_encode(['access_token' => 'secret', 'token_type' => 'jwt', 'expires_in' => 1800000]));
        }]);

        $token = (new AuthenticationClient(new Client(['handler' => HandlerStack::create($mock)])))
            ->authenticate(new CadsusCredentials('tenant-1', "-----BEGIN CERTIFICATE-----\ntest\n-----END CERTIFICATE-----"));

        self::assertSame('jwt secret', $token->authorizationHeader());

        self::assertTrue($seen['verify']);

        self::assertFileDoesNotExist($seen['cert'][0]);

        self::assertGreaterThanOrEqual($before + 1799, $token->expiresAt->getTimestamp());

        self::assertLessThanOrEqual($before + 1801, $token->expiresAt->getTimestamp());
    }
}
