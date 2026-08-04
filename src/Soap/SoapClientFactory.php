<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Soap;

use SoapClient;
use SolucaoInternet\Cadsus\DTO\AccessToken;
use SolucaoInternet\Cadsus\Environment\CadsusEnvironment;

final class SoapClientFactory
{
    public function __construct(private readonly ?string $wsdlPath = null) {}

    public function create(CadsusEnvironment $environment, AccessToken $token): SoapClient
    {
        $context = stream_context_create([
            'http' => [
                'header' => 'Authorization: ' . $token->authorizationHeader() . "\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
            ],
        ]);

        return new SoapClient($this->wsdlPath ?? ContractPaths::wsdl(), [
            'soap_version' => SOAP_1_2,
            'stream_context' => $context,
            'location' => $environment->pdqEndpoint(),
            'exceptions' => true,
            'trace' => false,
            'cache_wsdl' => WSDL_CACHE_MEMORY,
            'connection_timeout' => 10,
        ]);
    }
}
