<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Soap;

final class ContractPaths
{
    public static function root(): string
    {
        return dirname(__DIR__, 2) . '/resources/contracts';
    }

    public static function wsdl(): string
    {
        return self::root() . '/wsdl/PDQSupplier.wsdl';
    }

    public static function requestSchema(): string
    {
        return self::root() . '/schema/HL7V3/NE2008/multicacheschemas/PRPA_IN201305UV02.xsd';
    }

    private function __construct() {}
}
