# Segurança

- TLS e hostname devem permanecer validados.
- Não haverá opção genérica para desabilitar TLS em produção.
- `SoapClient::trace` permanece desabilitado por padrão.
- Certificados temporários deverão usar nome imprevisível, permissão `0600` e remoção em `finally`.
- Token, certificado, senha, headers de autorização e payload SOAP não podem aparecer em logs ou exceções públicas.
- CPF/CNS somente poderão ser correlacionados por HMAC com segredo fornecido pela aplicação.
- Chaves de cache usam identificador não sensível, ambiente e endpoint.
- O comportamento inseguro do exemplo Java — trust-all, hostname verifier permissivo e credenciais fixas — é expressamente rejeitado.
