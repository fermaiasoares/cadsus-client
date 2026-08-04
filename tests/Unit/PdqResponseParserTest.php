<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SolucaoInternet\Cadsus\HL7\PdqResponseParser;

final class PdqResponseParserTest extends TestCase
{
    public function testMapsCitizenDataFromSuccessfulResponse(): void
    {
        $xml = <<<'XML'
<s:Envelope xmlns:s="http://www.w3.org/2003/05/soap-envelope"><s:Body>
<PRPA_IN201306UV02 xmlns="urn:hl7-org:v3"><controlActProcess>
<subject><registrationEvent><subject1><patient>
<id root="2.16.840.1.113883.3.4594.10" extension="identificador-interno" assigningAuthorityName="CADSUS-ESUSAB"/>
<id root="2.16.840.1.113883.3.4594" extension="registro-brasil" assigningAuthorityName="RES-BRASIL"/>
<statusCode code="active"/><confidentialityCode code="N"/>
<patientPerson>
<name use="L"><given>PESSOA</given><family>TESTE</family></name>
<name use="ASGN"><given>NOME SOCIAL</given></name>
<telecom value="mailto:pessoa@example.test" use="NET"/>
<telecom value="tel:+55-31-999999999" use="ASN"/>
<addr use="H"><city>310620</city><state>MG</state><postalCode>30000000</postalCode><country>010</country><houseNumber>10</houseNumber><streetName>RUA TESTE</streetName><streetNameType>081</streetNameType><additionalLocator>CENTRO</additionalLocator><unitID>APTO 1</unitID></addr>
<administrativeGenderCode code="M"/><birthTime value="19920227000000"/><raceCode code="03"/>
<asOtherIDs><id root="2.16.840.1.113883.13.236" extension="700000000000000"/></asOtherIDs>
<asOtherIDs><id root="2.16.840.1.113883.13.237" extension="00000000000"/></asOtherIDs>
<personalRelationship><code code="PRN"/><relationshipHolder1><name><given>MAE TESTE</given></name></relationshipHolder1></personalRelationship>
<personalRelationship><code code="NPRN"/><relationshipHolder1><name><given>PAI TESTE</given></name></relationshipHolder1></personalRelationship>
<birthPlace><addr><city>311340</city><country>010</country></addr></birthPlace>
<languageCommunication><languageCode code="pt"/><preferenceInd value="true"/></languageCommunication>
</patientPerson>
<subjectOf1><queryMatchObservation><value value="100"/></queryMatchObservation></subjectOf1>
</patient></subject1></registrationEvent></subject>
<queryAck><queryResponseCode code="OK"/><resultTotalQuantity value="1"/><resultRemainingQuantity value="0"/></queryAck>
</controlActProcess></PRPA_IN201306UV02></s:Body></s:Envelope>
XML;

        $result = (new PdqResponseParser())->parse($xml, 'correlation-id');
        $citizen = $result->citizens[0];

        self::assertSame(1, $result->total);
        self::assertSame('PESSOA TESTE', $citizen->name);
        self::assertSame('NOME SOCIAL', $citizen->socialName);
        self::assertSame('MAE TESTE', $citizen->motherName);
        self::assertSame('PAI TESTE', $citizen->fatherName);
        self::assertSame('1992-02-27', $citizen->birthDate?->format('Y-m-d'));
        self::assertSame('03', $citizen->raceCode);
        self::assertSame('311340', $citizen->birthCityCode);
        self::assertSame('010', $citizen->birthCountryCode);
        self::assertSame('pt', $citizen->language);
        self::assertTrue($citizen->preferredLanguage);
        self::assertSame('active', $citizen->status);
        self::assertSame('N', $citizen->confidentialityCode);
        self::assertSame(100, $citizen->matchScore);
        self::assertSame('cns', $citizen->identifiers[0]->type);
        self::assertSame('cpf', $citizen->identifiers[1]->type);
        self::assertSame('email', $citizen->contacts[0]->type);
        self::assertSame('pessoa@example.test', $citizen->contacts[0]->value);
        self::assertSame('mobile', $citizen->contacts[1]->type);
        self::assertSame('+55-31-999999999', $citizen->contacts[1]->value);
        self::assertSame('RUA TESTE', $citizen->addresses[0]->street);
        self::assertSame('10', $citizen->addresses[0]->number);
        self::assertSame('APTO 1', $citizen->addresses[0]->complement);
        self::assertSame('CENTRO', $citizen->addresses[0]->district);
        self::assertNull($citizen->addresses[0]->city);
        self::assertSame('310620', $citizen->addresses[0]->cityCode);
        self::assertSame('H', $citizen->addresses[0]->use);
        self::assertSame('081', $citizen->addresses[0]->streetTypeCode);
        self::assertSame('cadsus_esusab', $citizen->sourceIdentifiers[0]->type);
        self::assertSame('CADSUS-ESUSAB', $citizen->sourceIdentifiers[0]->assigningAuthorityName);
        self::assertSame('res_brasil', $citizen->sourceIdentifiers[1]->type);
    }

    public function testNotFoundIsAValidEmptyResult(): void
    {
        $xml = '<s:Envelope xmlns:s="http://www.w3.org/2003/05/soap-envelope"><s:Body><PRPA_IN201306UV02 xmlns="urn:hl7-org:v3"><controlActProcess><queryAck><queryResponseCode code="NF"/></queryAck></controlActProcess></PRPA_IN201306UV02></s:Body></s:Envelope>';

        $result = (new PdqResponseParser())->parse($xml, 'id');

        self::assertTrue($result->isEmpty());

        self::assertSame(0, $result->total);
    }
}
