<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Environment;

enum CadsusEnvironment: string
{
    case HOMOLOGATION = 'homologation';
    case PRODUCTION = 'production';

    public function authenticationEndpoint(): string
    {
        return match ($this) {
            self::HOMOLOGATION => 'https://ehr-auth-hmg.saude.gov.br/api/osb/token',
            self::PRODUCTION => 'https://ehr-auth.saude.gov.br/api/osb/token',
        };
    }

    public function pdqEndpoint(): string
    {
        return match ($this) {
            self::HOMOLOGATION => 'https://servicoshm.saude.gov.br/cadsus/v2/PDQSupplierJWT',
            self::PRODUCTION => 'https://servicos.saude.gov.br/cadsus/v2/PDQSupplierJWT',
        };
    }
}
