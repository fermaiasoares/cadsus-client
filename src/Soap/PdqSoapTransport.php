<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Soap;

use SoapFault;
use SolucaoInternet\Cadsus\DTO\AccessToken;
use SolucaoInternet\Cadsus\Environment\CadsusEnvironment;
use SolucaoInternet\Cadsus\Exceptions\CadsusException;

final readonly class PdqSoapTransport
{
    public function __construct(private SoapClientFactory $factory) {}
    public function query(string $payload, CadsusEnvironment $environment, AccessToken $token): string
    {
        $envelope = '<?xml version="1.0" encoding="UTF-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope"><soap:Body>' . $payload . '</soap:Body></soap:Envelope>';
        try {
            $response = $this->factory->create($environment, $token)->__doRequest($envelope, $environment->pdqEndpoint(), 'urn:hl7-org:v3:PRPA_IN201305UV02', SOAP_1_2);
            if (!is_string($response) || $response === '') throw new CadsusException('O CADSUS retornou uma resposta vazia.', 'unexpected_response', true);
            return $response;
        } catch (SoapFault $e) {
            $message = strtolower($e->getMessage());
            $rejected = str_contains($message, '401') || str_contains($message, '403') || str_contains($message, 'unauthorized');
            throw new CadsusException('A consulta SOAP ao CADSUS falhou.', $rejected ? 'token_rejected' : 'soap_fault', !$rejected, $e);
        }
    }
}
