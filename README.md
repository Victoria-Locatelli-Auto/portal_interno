# Portal MCM

Portal interno da MCM para centralizar acessos, documentos, avisos, contatos, treinamentos e ferramentas usadas no dia a dia dos colaboradores.

## Sobre o projeto

O Portal MCM foi organizado em uma estrutura modular para separar responsabilidades entre controladores PHP, helpers, views, estilos e scripts JavaScript. A ideia é facilitar manutenção, reaproveitar componentes comuns e manter cada tela mais limpa.

Principais recursos:

- Login, primeiro acesso e recuperação de senha.
- Home com acessos rápidos, busca e avisos.
- Componentes reutilizáveis de layout, como cabeçalho, topbar, sidebar e rodapé.
- Gestão de documentos, importação, aprovação e visualização.
- Páginas administrativas para menus, avisos, sugestões e usuários.
- Integração com site de treinamentos por token JWT.
- Busca inteligente com apoio dos módulos de IA do portal.

## Estrutura de pastas

```text
api/                     Endpoints usados pelo frontend
ajax/                    Rotas auxiliares e testes internos
config/                  Configurações locais do ambiente
css/                     Estilos globais e estilos por página
docs/                    Documentação do projeto
includes/                Classes, helpers, layout e views reutilizáveis
includes/helpers/        Regras auxiliares e preparação de dados por tela
includes/views/          HTML das telas e parciais
index_files/             Imagens e arquivos estáticos principais
js/                      Scripts globais e scripts por página
pages/                   Controladores das páginas internas
portalmcm/               Arquivos auxiliares de ambiente
uploads/                 Arquivos enviados pelos usuários, não versionado
vendor/                  Dependências do Composer, não versionado
```

## Padrão de organização das telas

As telas seguem este padrão:

```text
pages/nome_da_pagina.php          Controller, autenticação, consultas e preparação da tela
includes/helpers/nome.php         Funções auxiliares e formatação de dados
includes/views/nome-view.php      HTML da tela
css/pages/nome.css                Estilos específicos da tela
js/pages/nome.js                  Interações específicas da tela
```

Componentes compartilhados ficam em:

```text
includes/layout_start.php
includes/layout_end.php
includes/header.php
includes/topbar.php
includes/sidebar.php
includes/footer.php
```

## Requisitos

- PHP 8.2 ou superior.
- MySQL ou MariaDB.
- Composer.
- Servidor local como XAMPP, Apache ou PHP built-in server.

Dependências PHP do projeto:

```bash
composer install
```

## Configuração

Os arquivos reais de configuração não são versionados por segurança. Para configurar um novo ambiente, copie os exemplos abaixo e ajuste com os dados reais:

```bash
copy config\database.example.php config\database.php
copy config\email.example.php config\email.php
copy config\training.example.php config\training.php
copy config\ai.example.php config\ai.php
```

No Linux ou servidor:

```bash
cp config/database.example.php config/database.php
cp config/email.example.php config/email.php
cp config/training.example.php config/training.php
cp config/ai.example.php config/ai.php
```

Configure principalmente:

- Banco de dados em `config/database.php`.
- SMTP em `config/email.php`.
- URL e chave JWT dos treinamentos em `config/training.php`.
- Configurações de IA em `config/ai.php`, se aplicável.

Nunca envie credenciais reais para o Git.

## Como executar localmente

Exemplo usando PHP built-in server:

```bash
php -S 127.0.0.1:8088 -t .
```

Com XAMPP no Windows, também pode ser usado:

```powershell
C:\xampp\php\php.exe -S 127.0.0.1:8088 -t .
```

Acesse:

```text
http://127.0.0.1:8088/login.php
```

Se houver problema com sessões no Windows, configure ou crie uma pasta de sessão válida no ambiente local.

## Banco de dados

O projeto depende das tabelas usadas pelo Portal MCM, incluindo usuários, funções, filiais, menus, documentos, avisos e demais módulos internos.

Tabelas utilizadas em diferentes telas incluem, entre outras:

- `users`
- `funcao`
- `filiais`
- `custom_menus`
- `custom_submenus`
- `documents`
- `document_menus`
- `notices`
- `searches`
- `system_config`

## Treinamentos com JWT

A tela de treinamentos gera um token JWT com informações do colaborador autenticado e redireciona para o site configurado em `TRAINING_URL`.

Dados enviados no token:

- ID do funcionário.
- Nome.
- Cargo.
- Filial.

A chave `TRAINING_JWT_SECRET` deve ser configurada apenas no ambiente real.

## Arquivos não versionados

Por segurança e para evitar arquivos pesados, os seguintes itens ficam fora do Git:

- `config/*.php`, exceto `config/*.example.php`.
- `uploads/`.
- `vendor/`.
- `composer.phar`.
- `.env` e variações.
- Arquivos temporários e logs.

## Fluxo recomendado de desenvolvimento

1. Atualize a branch principal.
2. Faça as alterações seguindo o padrão de controller, helper, view, CSS e JS.
3. Teste localmente no navegador.
4. Verifique se nenhum arquivo sensível entrou no Git.
5. Faça commit com uma mensagem clara.
6. Envie para o repositório remoto.

## Design system

O design system inicial do portal está em:

```text
docs/design-system.md
docs/design-system.pdf
```

Ele documenta cores, tipografia, botões, cards, layout e padrões visuais definidos durante a refatoração.

## Repositório

```text
https://github.com/Victoria-Locatelli-Auto/portal_interno.git
```

## Créditos

© 2026 Desenvolvido por PPGC/TI
