<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Tests\Unit;

use DateTimeImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;
use SolucaoInternet\Cadsus\Authentication\AuthenticationClient;
use SolucaoInternet\Cadsus\Authentication\TokenProvider;
use SolucaoInternet\Cadsus\Cache\InMemoryTokenStore;
use SolucaoInternet\Cadsus\Cache\LocalLock;
use SolucaoInternet\Cadsus\DTO\AccessToken;
use SolucaoInternet\Cadsus\DTO\CadsusCredentials;

final class TokenProviderTest extends TestCase
{
    public function testItReusesAValidCachedToken(): void
    {
        $credentials = new CadsusCredentials('tenant', 'pem');

        $store = new InMemoryTokenStore();

        $expected = new AccessToken('cached', new DateTimeImmutable('+30 minutes'));

        $store->put($credentials->cacheScope(), $expected);

        $auth = new AuthenticationClient(new Client(['handler' => HandlerStack::create(new MockHandler([]))]));

        self::assertSame($expected, (new TokenProvider($auth, $store, new LocalLock()))->token($credentials));
    }
}
