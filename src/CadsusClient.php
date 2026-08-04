<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use SolucaoInternet\Cadsus\Authentication\AuthenticationClient;
use SolucaoInternet\Cadsus\Authentication\TokenProvider;
use SolucaoInternet\Cadsus\Cache\InMemoryTokenStore;
use SolucaoInternet\Cadsus\Cache\LocalLock;
use SolucaoInternet\Cadsus\Contracts\CadsusClientInterface;
use SolucaoInternet\Cadsus\Contracts\CredentialedCadsusClientInterface;
use SolucaoInternet\Cadsus\Contracts\LockInterface;
use SolucaoInternet\Cadsus\Contracts\TokenStoreInterface;
use SolucaoInternet\Cadsus\DTO\CadsusCredentials;
use SolucaoInternet\Cadsus\DTO\CadsusSearchRequest;
use SolucaoInternet\Cadsus\DTO\CadsusSearchResult;
use SolucaoInternet\Cadsus\Exceptions\CadsusException;
use SolucaoInternet\Cadsus\HL7\PdqRequestBuilder;
use SolucaoInternet\Cadsus\HL7\PdqResponseParser;
use SolucaoInternet\Cadsus\Soap\PdqSoapTransport;
use SolucaoInternet\Cadsus\Soap\SoapClientFactory;

final class CadsusClient implements CadsusClientInterface, CredentialedCadsusClientInterface
{
    public function __construct(private readonly TokenProvider $tokens, private readonly PdqRequestBuilder $builder, private readonly PdqSoapTransport $transport, private readonly PdqResponseParser $parser, private readonly ?CadsusCredentials $credentials = null) {}

    public static function create(?ClientInterface $http = null, ?TokenStoreInterface $store = null, ?LockInterface $lock = null): self
    {
        $tokens = new TokenProvider(new AuthenticationClient($http ?? new Client()), $store ?? new InMemoryTokenStore(), $lock ?? new LocalLock());
        return new self($tokens, new PdqRequestBuilder(), new PdqSoapTransport(new SoapClientFactory()), new PdqResponseParser());
    }

    public function forCredentials(CadsusCredentials $credentials): CredentialedCadsusClientInterface
    {
        return new self($this->tokens, $this->builder, $this->transport, $this->parser, $credentials);
    }

    public function search(CadsusSearchRequest $request): CadsusSearchResult
    {
        if ($this->credentials === null) throw new CadsusException('As credenciais CADSUS não foram informadas.', 'credentials');
        $id = bin2hex(random_bytes(16));
        $payload = $this->builder->build($request, $id);
        try {
            return $this->parser->parse($this->transport->query($payload, $this->credentials->environment, $this->tokens->token($this->credentials)), $id);
        } catch (CadsusException $e) {
            if ($e->category !== 'token_rejected') throw $e;
            $this->tokens->invalidate($this->credentials);
            return $this->parser->parse($this->transport->query($payload, $this->credentials->environment, $this->tokens->token($this->credentials, true)), $id);
        }
    }
}
