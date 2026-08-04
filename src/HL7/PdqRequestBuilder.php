<?php

declare(strict_types=1);

namespace SolucaoInternet\Cadsus\HL7;

use DateTimeImmutable;
use DOMDocument;
use DOMElement;
use SolucaoInternet\Cadsus\DTO\CadsusSearchRequest;
use SolucaoInternet\Cadsus\Exceptions\CadsusException;
use SolucaoInternet\Cadsus\Soap\ContractPaths;

final class PdqRequestBuilder
{
    public function build(
        CadsusSearchRequest $request,
        string $correlationId,
    ): string {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElementNS('urn:hl7-org:v3', 'PRPA_IN201305UV02');
        $root->setAttribute('ITSVersion', 'XML_1.0');
        $dom->appendChild($root);
        $this->node($dom, $root, 'id', [
            'root' => '2.16.840.1.113883.4.714',
            'extension' => $correlationId,
        ]);
        $this->node($dom, $root, 'creationTime', [
            'value' => (new DateTimeImmutable())->format('YmdHis'),
        ]);
        $this->node($dom, $root, 'interactionId', [
            'root' => '2.16.840.1.113883.1.6',
            'extension' => 'PRPA_IN201305UV02',
        ]);
        $this->node($dom, $root, 'processingCode', ['code' => 'T']);
        $this->node($dom, $root, 'processingModeCode', ['code' => 'T']);
        $this->node($dom, $root, 'acceptAckCode', ['code' => 'AL']);
        $this->party(
            $dom,
            $root,
            'receiver',
            'RCV',
            '2.16.840.1.113883.3.72.6.5.100.85',
        );
        $this->party(
            $dom,
            $root,
            'sender',
            'SND',
            '2.16.840.1.113883.3.72.6.2',
        );
        $control = $this->node($dom, $root, 'controlActProcess', [
            'classCode' => 'CACT',
            'moodCode' => 'EVN',
        ]);
        $this->node($dom, $control, 'code', [
            'code' => 'PRPA_TE201305UV02',
            'codeSystem' => '2.16.840.1.113883.1.6',
        ]);
        $query = $this->node($dom, $control, 'queryByParameter');
        $this->node($dom, $query, 'queryId', [
            'root' => '1.2.840.114350.1.13.28.1.18.5.999',
            'extension' => $correlationId,
        ]);
        $this->node($dom, $query, 'statusCode', ['code' => 'new']);
        $this->node($dom, $query, 'responseModalityCode', ['code' => 'R']);
        $this->node($dom, $query, 'responsePriorityCode', ['code' => 'I']);
        $list = $this->node($dom, $query, 'parameterList');
        foreach (
            [
                [$request->cns, Oid::CNS],
                [$request->cpf, Oid::CPF],
                [$request->nis, Oid::NIS],
            ]
            as [$value, $oid]
        ) {
            if ($value !== null) {
                $c = $this->node($dom, $list, 'livingSubjectId');
                $this->node($dom, $c, 'value', [
                    'root' => $oid,
                    'extension' => $value,
                ]);
                $this->node($dom, $c, 'semanticsText', [], 'LivingSubject.id');
            }
        }
        if ($request->gender) {
            $c = $this->node($dom, $list, 'livingSubjectAdministrativeGender');
            $this->node($dom, $c, 'value', [
                'code' => $request->gender->value,
                'codeSystem' => '2.16.840.1.113883.5.1',
            ]);
            $this->node(
                $dom,
                $c,
                'semanticsText',
                [],
                'LivingSubject.administrativeGender',
            );
        }
        if ($request->birthDate) {
            $c = $this->node($dom, $list, 'livingSubjectBirthTime');
            $this->node($dom, $c, 'value', [
                'value' => $request->birthDate->format('Ymd') . '000000',
            ]);
            $this->node(
                $dom,
                $c,
                'semanticsText',
                [],
                'LivingSubject.birthTime',
            );
        }
        if ($request->name) {
            $c = $this->node($dom, $list, 'livingSubjectName');
            $v = $this->node($dom, $c, 'value', ['use' => 'L']);
            $this->node($dom, $v, 'given', [], $request->name);
            $this->node($dom, $c, 'semanticsText', [], 'LivingSubject.Given');
        }
        if ($request->motherName) {
            $c = $this->node($dom, $list, 'mothersMaidenName');
            $v = $this->node($dom, $c, 'value', ['use' => 'L']);
            $this->node($dom, $v, 'family', [], $request->motherName);
            $this->node($dom, $c, 'semanticsText', [], 'Person.MothersName');
        }
        if ($request->birthCityCode) {
            $c = $this->node($dom, $list, 'patientAddress');
            $v = $this->node($dom, $c, 'value', ['use' => 'BIRTHPL']);
            $this->node($dom, $v, 'city', [], $request->birthCityCode);
            $this->node(
                $dom,
                $c,
                'semanticsText',
                [],
                'LivingSubject.birthPlaceAddress',
            );
        }
        if (!$dom->schemaValidate(ContractPaths::requestSchema())) {
            throw new CadsusException(
                'A mensagem HL7 não atende ao contrato XSD.',
                'contract_validation',
            );
        }
        $xml = $dom->saveXML($root);
        if ($xml === false) {
            throw new CadsusException(
                'Não foi possível gerar a mensagem HL7.',
                'contract_validation',
            );
        }
        return $xml;
    }

    private function party(
        DOMDocument $d,
        DOMElement $root,
        string $tag,
        string $type,
        string $oid,
    ): void {
        $p = $this->node($d, $root, $tag, ['typeCode' => $type]);
        $device = $this->node($d, $p, 'device', [
            'classCode' => 'DEV',
            'determinerCode' => 'INSTANCE',
        ]);
        $this->node($d, $device, 'id', ['root' => $oid]);
        if ($tag === 'sender') {
            $this->node($d, $device, 'name', [], 'CADSUS');
        }
    }

    private function node(
        DOMDocument $d,
        DOMElement $parent,
        string $name,
        array $attributes = [],
        ?string $text = null,
    ): DOMElement {
        $n = $d->createElementNS('urn:hl7-org:v3', $name);
        foreach ($attributes as $k => $v) {
            $n->setAttribute($k, (string) $v);
        }
        if ($text !== null) {
            $n->appendChild($d->createTextNode($text));
        }
        $parent->appendChild($n);
        return $n;
    }
}
