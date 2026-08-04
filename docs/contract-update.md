# Atualização do contrato

1. Obter WSDL, XSDs, manual, collection e domínios de fonte oficial.
2. Registrar origem, versão e data de obtenção.
3. Comparar semanticamente operações, actions, tipos, critérios e endpoints.
4. Preservar `wsdl/../schema/HL7V3/NE2008/...`.
5. Não incluir `.DS_Store`, `__MACOSX`, Java de exemplo ou credenciais.
6. Executar `composer contract:manifest`.
7. Revisar o diff do manifesto e dos artefatos.
8. Executar `composer test:contract` e a suíte completa.
9. Atualizar `docs/contract-report.md` e o changelog.

O manifesto de hashes detecta alterações, mas não substitui a revisão semântica.
