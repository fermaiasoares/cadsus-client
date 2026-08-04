# Testes

```bash
composer test
composer test:contract
```

A suíte padrão não acessa DATASUS nem usa credenciais reais. Testes futuros de homologação serão opt-in e separados.

Cobertura prevista:

- unidade: requests, value objects, TTL, builder, parser e domínios;
- contrato: WSDL, XSD, operações, tipos e hashes;
- integração local: mTLS simulado, cache, lock, faults e timeouts;
- segurança: logs, temporários, TLS e XML injection;
- concorrência: renovação única do token.
