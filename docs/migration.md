# Migração

1. Criar fixtures sanitizadas da integração atual.
2. Validar o pacote em testes locais e homologação.
3. Adicionar um adapter na aplicação sem remover o serviço atual.
4. Comparar respostas normalizadas, sem duplicar consultas em produção.
5. Habilitar por feature flag e tenant.
6. Observar erros, latência, cache e quantidade de resultados.
7. Expandir gradualmente.
8. Remover o legado somente depois de estabilidade comprovada.
