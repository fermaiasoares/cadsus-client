<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\HL7;

use DateTimeImmutable;
use DOMDocument;
use DOMXPath;
use SolucaoInternet\Cadsus\DTO\CadsusCitizen;
use SolucaoInternet\Cadsus\DTO\CadsusAddress;
use SolucaoInternet\Cadsus\DTO\CadsusContact;
use SolucaoInternet\Cadsus\DTO\CadsusIdentifier;
use SolucaoInternet\Cadsus\DTO\CadsusSearchResult;
use SolucaoInternet\Cadsus\Exceptions\CadsusException;
use SolucaoInternet\Cadsus\ValueObjects\CadsusGender;

final class PdqResponseParser
{
    public function parse(
        string $xml,
        string $correlationId,
    ): CadsusSearchResult {
        $dom = new DOMDocument();

        if (!@$dom->loadXML($xml, LIBXML_NONET | LIBXML_NOBLANKS)) {
            throw new CadsusException(
                'Resposta XML inválida.',
                'unexpected_response',
            );
        }
        $xp = new DOMXPath($dom);
        $xp->registerNamespace('h', 'urn:hl7-org:v3');
        $xp->registerNamespace('s', 'http://www.w3.org/2003/05/soap-envelope');
        if ($xp->query('//s:Fault')->length) {
            throw new CadsusException(
                'O CADSUS retornou uma falha SOAP.',
                'soap_fault',
            );
        }
        $code = $xp->evaluate('string(//h:queryAck/h:queryResponseCode/@code)');
        if ($code === 'NF') {
            return new CadsusSearchResult([], 0, false, null, $correlationId);
        }
        if ($code !== '' && $code !== 'OK') {
            throw new CadsusException(
                'Código de resposta CADSUS incompatível.',
                'unexpected_response',
            );
        }
        $citizens = [];
        foreach (
            $xp->query(
                '//h:registrationEvent/h:subject1/h:patient/h:patientPerson',
            )
            as $person
        ) {
            $ids = [];
            foreach ($xp->query('.//h:asOtherIDs/h:id[1]', $person) as $id) {
                $oid = $id->getAttribute('root');
                $ids[] = new CadsusIdentifier(
                    match ($oid) {
                        Oid::CNS => 'cns',
                        Oid::CPF => 'cpf',
                        Oid::NIS => 'nis',
                        default => 'other',
                    },
                    $id->getAttribute('extension'),
                    $oid,
                );
            }
            $birth = $xp->evaluate('string(./h:birthTime/@value)', $person);
            $date = $birth
                ? (DateTimeImmutable::createFromFormat(
                    '!Ymd',
                    substr($birth, 0, 8),
                ) ?:
                    null)
                : null;

            $addresses = [];
            foreach ($xp->query('./h:addr', $person) as $address) {
                $city = $this->text($xp, './h:city', $address);
                $addresses[] = new CadsusAddress(
                    street: $this->text($xp, './h:streetName', $address)
                        ?? $this->text($xp, './h:streetAddressLine', $address),
                    number: $this->text($xp, './h:houseNumber', $address),
                    complement: $this->text($xp, './h:unitID', $address),
                    district: $this->text($xp, './h:additionalLocator', $address)
                        ?? $this->text($xp, './h:censusTract', $address),
                    city: $city !== null && !ctype_digit($city) ? $city : null,
                    cityCode: $this->text($xp, './h:city/@code', $address)
                        ?? ($city !== null && ctype_digit($city) ? $city : null),
                    state: $this->text($xp, './h:state', $address),
                    postalCode: $this->text($xp, './h:postalCode', $address),
                    countryCode: $this->text($xp, './h:country/@code', $address)
                        ?? $this->text($xp, './h:country', $address),
                    use: $address->getAttribute('use') ?: null,
                    streetTypeCode: $this->text($xp, './h:streetNameType', $address),
                );
            }

            $contacts = [];
            foreach ($xp->query('./h:telecom[@value]', $person) as $telecom) {
                $value = trim($telecom->getAttribute('value'));
                if ($value === '') {
                    continue;
                }

                $use = $telecom->getAttribute('use') ?: null;
                $contacts[] = new CadsusContact(
                    $this->contactType($value, $use),
                    preg_replace('/^(?:mailto:|tel:)/i', '', $value) ?? $value,
                    $use,
                );
            }

            $preferredLanguage = $this->text(
                $xp,
                './h:languageCommunication[1]/h:preferenceInd/@value',
                $person,
            );

            $sourceIdentifiers = [];
            foreach ($xp->query('../h:id[@extension]', $person) as $id) {
                $sourceIdentifiers[] = new CadsusIdentifier(
                    type: match ($id->getAttribute('assigningAuthorityName')) {
                        'CADSUS-ESUSAB' => 'cadsus_esusab',
                        'RES-BRASIL' => 'res_brasil',
                        default => 'source',
                    },
                    value: $id->getAttribute('extension'),
                    oid: $id->getAttribute('root'),
                    assigningAuthorityName: $id->getAttribute('assigningAuthorityName') ?: null,
                );
            }
            $citizens[] = new CadsusCitizen(
                $ids,
                $this->name($xp, './h:name[@use="L"][1]', $person),
                $this->name($xp, './h:name[@use="ASGN"][1]', $person),
                $this->name(
                    $xp,
                    './/h:personalRelationship[h:code/@code="PRN"]//h:name[1]',
                    $person,
                ),
                $date,
                CadsusGender::tryFrom(
                    $xp->evaluate(
                        'string(./h:administrativeGenderCode/@code)',
                        $person,
                    ),
                ),
                $addresses,
                $contacts,
                $this->name(
                    $xp,
                    './/h:personalRelationship[h:code/@code="NPRN"]//h:name[1]',
                    $person,
                ),
                $this->text($xp, './h:raceCode/@code', $person),
                $this->text($xp, './h:birthPlace/h:addr/h:city', $person),
                $this->text($xp, './h:birthPlace/h:addr/h:country', $person),
                $this->text($xp, './h:languageCommunication[1]/h:languageCode/@code', $person),
                $preferredLanguage === null ? null : filter_var($preferredLanguage, FILTER_VALIDATE_BOOLEAN),
                $this->text($xp, '../h:statusCode/@code', $person),
                $this->text($xp, '../h:confidentialityCode/@code', $person),
                $this->integer($xp, '../h:subjectOf1/h:queryMatchObservation/h:value/@value', $person),
                $sourceIdentifiers,
            );
        }
        $total = (int) $xp->evaluate(
            'string(//h:queryAck/h:resultTotalQuantity/@value)',
        );
        if (!$total) {
            $total = count($citizens);
        }
        $remaining = (int) $xp->evaluate(
            'string(//h:queryAck/h:resultRemainingQuantity/@value)',
        );
        $pointer = $this->text($xp, '//h:queryAck/h:queryId/@extension');

        return new CadsusSearchResult(
            $citizens,
            $total,
            $remaining > 0,
            $remaining > 0 ? $pointer : null,
            $correlationId,
        );
    }

    private function name(DOMXPath $xp, string $query, ?\DOMNode $context = null): ?string
    {
        $parts = [];
        foreach ($xp->query($query . '/*[self::h:prefix or self::h:given or self::h:family or self::h:suffix]', $context) as $part) {
            $value = trim($part->textContent);
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        return $parts === [] ? null : implode(' ', $parts);
    }

    private function integer(DOMXPath $xp, string $query, ?\DOMNode $context = null): ?int
    {
        $value = $this->text($xp, $query, $context);
        return $value !== null && is_numeric($value) ? (int) $value : null;
    }

    private function contactType(string $value, ?string $use): string
    {
        if ($use === 'NET' || str_contains($value, '@') || str_starts_with(strtolower($value), 'mailto:')) {
            return 'email';
        }

        return match ($use) {
            'MC', 'ASN' => 'mobile',
            'WP' => 'work_phone',
            default => 'phone',
        };
    }

    private function text(
        DOMXPath $xp,
        string $query,
        ?\DOMNode $context = null,
    ): ?string {
        $value = trim($xp->evaluate('string(' . $query . ')', $context));
        return $value === '' ? null : $value;
    }
}
