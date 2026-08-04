# Solucao Internet CADSUS Client

Pacote privado PHP 8.2+ para integração com CADSUS/CNS-PDQ. O núcleo não depende de Laravel, models ou banco de dados da aplicação consumidora.

## Estado atual

O pacote executa autenticação HTTP/mTLS, reutiliza o JWT em cache, constrói e valida mensagens HL7, consulta pelo `SoapClient` e converte respostas em DTOs. Aplicações distribuídas devem fornecer implementações compartilhadas de `TokenStoreInterface` e `LockInterface`.

O desenho aprovado usa:

- HTTP GET com mTLS para obter o JWT;
- `SoapClient` como transporte das consultas;
- SOAP 1.2 e WSDL local;
- `Authorization: jwt {access_token}` no header HTTP do SOAP;
- endpoint do WSDL sobrescrito conforme o ambiente;
- validação TLS e hostname sempre habilitada;
- `trace` desabilitado por padrão.

## Instalação para desenvolvimento

```bash
composer install
composer test
```

Durante o desenvolvimento dentro do VersaSaúde, também é possível executar a suíte com o PHPUnit do projeto pai:

```bash
../../vendor/bin/phpunit -c phpunit.xml.dist
```

## API pública

```php
use SolucaoInternet\Cadsus\CadsusClient;
use SolucaoInternet\Cadsus\DTO\CadsusCredentials;
use SolucaoInternet\Cadsus\DTO\CadsusSearchRequest;

$cadsus = CadsusClient::create();
$credentials = new CadsusCredentials(
  'tenant-nao-sensivel',
  $certificateContents,
  $certificatePassword,
);

$result = $cadsus
  ->forCredentials($credentials)
  ->search(CadsusSearchRequest::byCpf('00000000000'));
```

O pacote não recebe nem persiste models locais. A aplicação é responsável por selecionar o tenant, autorizar a consulta e mapear o resultado para suas entidades.

## Contrato oficial

O WSDL está em `resources/contracts/wsdl`; os caminhos relativos para `resources/contracts/schema` foram preservados. `resources/contracts/manifest.php` registra os hashes SHA-256 aprovados.

Para atualizar o manifesto após uma atualização intencional e revisada:

```bash
composer contract:manifest
composer test:contract
```

Consulte `docs/contract-update.md` antes de substituir qualquer artefato.

## Segurança

Nunca registre token, certificado, senha, CPF, CNS, nome, nascimento, endereço ou XML SOAP integral. Não habilite `trace` em produção. O certificado usado na autenticação deve ser o mesmo vinculado à credencial aprovada pelo DATASUS.

Mais detalhes em `docs/security.md`.
