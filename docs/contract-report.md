# Relatório do contrato CADSUS/CNS-PDQ

## Fontes

- Manual de Integração PDQ CNS v2.1, 11/03/2024.
- Manual de Configuração de Ambientes Postman v1.0.
- Collection oficial CNS-PDQ.
- Projeto Java e árvore HL7 v3 fornecidos pelo DATASUS.
- Planilha oficial de domínios.

## Operações

- `PDQSupplier_PRPA_IN201305UV02`: consulta, request `PRPA_IN201305UV02`, response `PRPA_IN201306UV02`.
- `PDQSupplier_QUQI_IN000003UV01_Continue`: continuação.
- `PDQSupplier_QUQI_IN000003UV01_Cancel`: cancelamento.

O `SoapClient` reconhece 3.140 definições de tipos no contrato atual.

## Identificadores

- CNS: `2.16.840.1.113883.13.236`.
- CPF: `2.16.840.1.113883.13.237`.

O Manual de Configuração contém uma divergência textual ao mostrar `.236` para CPF. Collection, Java e planilha confirmam `.237`, que foi adotado.

## Divergências relevantes

- O WSDL aponta para `/cadsus/PDQSupplier`; manual e Postman atuais usam `/cadsus/v2/PDQSupplierJWT`. O pacote preserva o WSDL e sobrescreve `location`.
- A action de cancelamento no WSDL contém um espaço antes do nome da mensagem. O artefato original foi preservado.
- O Java usa UsernameToken legado e desabilita TLS/hostname; nenhum desses comportamentos será copiado.
- O manual v2.1 inclui NIS, ausente na implementação atual. O OID/mapeamento será consolidado durante a implementação do builder.
