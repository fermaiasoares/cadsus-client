# Arquitetura

## Fluxo aprovado

1. A aplicação fornece `CadsusCredentials` para o tenant selecionado.
2. O cliente HTTP autentica por GET/mTLS no EHR Auth.
3. O JWT é armazenado por credencial, ambiente e endpoint.
4. O builder produz `PRPA_IN201305UV02` e valida o XML contra o XSD.
5. `SoapClient` envia a operação PDQ em SOAP 1.2 com `Authorization: jwt ...`.
6. O parser converte `PRPA_IN201306UV02` em DTOs próprios.

O WSDL é local, mas `location` é sempre sobrescrito pelo endpoint atual do ambiente.

## Limites

O pacote encapsula autenticação, token, SOAP, HL7, domínios e respostas técnicas. Autorização do usuário, seleção do tenant, auditoria de negócio, persistência e LGPD permanecem na aplicação.

## Decisões

| Decisão | Escolha | Motivo |
|---|---|---|
| Núcleo | PHP 8.2 independente | Compatível com o consumidor atual e reutilizável |
| Autenticação | Cliente HTTP mTLS | O endpoint oficial não é SOAP |
| Consulta | `SoapClient` | Requisito arquitetural aprovado |
| Payload | DOM + XSD, entregue ao SOAP | Controle de namespaces, escaping e contrato |
| WSDL | Local com `location` sobrescrito | Contrato reproduzível e endpoint atual |
| Cache | Contrato sem Redis obrigatório | Portabilidade e multi-tenancy |
