# SupportLife — Sistema de Estoque

Sistema web de gerenciamento de estoque desenvolvido em PHP e MySQL. O projeto reúne autenticação, cadastro e edição de itens, movimentações, manutenção, agrupamento de itens em uso e exportação de dados.

## Tecnologias

- PHP
- MySQL / MariaDB
- HTML5 / CSS3 / JavaScript
- Bootstrap
- AdminLTE
- XlsxWriter (implementação simples incluída no projeto)

## Segurança

As credenciais reais do ambiente de produção não fazem parte deste repositório. Configure as variáveis de ambiente a partir de `.env.example`.

Variáveis necessárias:

- `DB_HOST`
- `DB_USER`
- `DB_PASSWORD`
- `DB_NAME`
- `PHARMACY_ACCESS_PASSWORD`
- `MOVEMENTS_ACCESS_PASSWORD`

> Não publique senhas, tokens ou dados reais de usuários no repositório.

## Execução

1. Configure um servidor PHP com MySQL/MariaDB.
2. Crie as variáveis de ambiente indicadas em `.env.example`.
3. Configure o banco de dados utilizado pela aplicação.
4. Aponte o servidor web para a raiz do projeto.
5. Acesse `login.php`.

## Portfólio

Este repositório foi preparado para apresentar a arquitetura e as funcionalidades do projeto sem expor credenciais do ambiente original.
