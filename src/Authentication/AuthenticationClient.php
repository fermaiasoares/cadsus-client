<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Authentication;

use DateTimeImmutable;
use GuzzleHttp\ClientInterface;
use Throwable;
use SolucaoInternet\Cadsus\Certificate\TemporaryCertificate;
use SolucaoInternet\Cadsus\DTO\AccessToken;
use SolucaoInternet\Cadsus\DTO\CadsusCredentials;
use SolucaoInternet\Cadsus\Exceptions\CadsusException;

final readonly class AuthenticationClient
{
    public function __construct(private ClientInterface $http, private int $timeoutSeconds = 10) {}
    public function authenticate(CadsusCredentials $credentials): AccessToken
    {
        $certificate = TemporaryCertificate::create($credentials->certificateContents, $credentials->certificatePassword);
        try {
            $response = $this->http->request('GET', $credentials->environment->authenticationEndpoint(), [
                'headers' => ['Accept' => 'application/json'],
                'cert' => [$certificate->path, $credentials->certificatePassword],
                'connect_timeout' => $this->timeoutSeconds,
                'timeout' => $this->timeoutSeconds,
                'verify' => true,
            ]);
            $data = json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
            if (!is_string($data['access_token'] ?? null) || $data['access_token'] === '') {
                throw new CadsusException('Resposta de autenticação incompatível.', 'authentication');
            }
            $ttl = max(1, (int) ($data['expires_in'] ?? 1800));
            // O EHR Auth atual informa 1.800.000 milissegundos para 30 minutos.
            if ($ttl > 86400) {
                $ttl = max(1, intdiv($ttl, 1000));
            }
            return new AccessToken($data['access_token'], new DateTimeImmutable("+{$ttl} seconds"), (string) ($data['token_type'] ?? 'jwt'));
        } catch (CadsusException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new CadsusException('Não foi possível autenticar no CADSUS.', 'authentication', false, $exception);
        } finally {
            $certificate->remove();
        }
    }
}
