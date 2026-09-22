# Documento de Escopo - Portal MCM

## 1. Identificacao do projeto

**Projeto:** Portal MCM  
**Area responsavel:** PPGC/TI  
**Publico-alvo:** Colaboradores, gestores, administradores, editores e equipe de TI da MCM  
**Repositorio:** https://github.com/Victoria-Locatelli-Auto/portal_interno.git

## 2. Objetivo

O Portal MCM tem como objetivo centralizar, em um unico ambiente interno, o acesso a informacoes, solicitacoes, documentos, avisos, contatos, treinamentos e ferramentas utilizadas pelos colaboradores no dia a dia de trabalho.

O projeto tambem busca padronizar a experiencia visual do portal, melhorar a organizacao do codigo e facilitar a manutencao futura por meio da separacao entre controllers, helpers, views, estilos e scripts.

## 3. Contexto

Antes da refatoracao, diversas telas possuiam estrutura, menu lateral, cabecalho, rodape, CSS e JavaScript replicados diretamente em cada arquivo. Isso dificultava manutencao, aumentava repeticao de codigo e tornava alteracoes visuais mais trabalhosas.

A evolucao atual do projeto reorganiza o portal em componentes reutilizaveis, com layout compartilhado e padroes de desenvolvimento mais claros.

## 4. Entregas do projeto

As entregas contempladas nesta etapa incluem:

- Refatoracao da tela de login.
- Refatoracao da tela inicial do portal.
- Padronizacao visual da home e login.
- Criacao e reaproveitamento de componentes comuns.
- Separacao de controllers, helpers, views, CSS e JavaScript por tela.
- Implementacao de rodape compartilhado.
- Organizacao das paginas internas do portal.
- Criacao da integracao com treinamentos por token JWT.
- Criacao de design system inicial.
- Organizacao do projeto em repositorio Git.
- Criacao de README, documento de escopo e documento de requisitos.

## 5. Modulos contemplados

### 5.1 Autenticacao

- Login de usuarios.
- Primeiro acesso.
- Recuperacao de senha.
- Controle de sessao.
- Redirecionamento conforme autenticacao.

### 5.2 Home do portal

- Saudacao personalizada ao colaborador.
- Campo de busca.
- Cards de acessos frequentes.
- Avisos do portal.
- Links para sistemas internos e externos.

### 5.3 Componentes compartilhados

- Cabecalho.
- Topbar.
- Sidebar.
- Rodape.
- Layout base.

### 5.4 Documentos

- Listagem de documentos.
- Importacao de documentos.
- Adicao manual de documentos.
- Aprovacao de documentos.
- Visualizacao de documentos.
- Meus documentos.

### 5.5 Administracao

- Gerenciamento de usuarios.
- Gerenciamento de menus.
- Gerenciamento de avisos.
- Sugestoes do portal.
- Monitoramento do sistema.

### 5.6 Contatos e paginas informativas

- Contatos.
- Dados TI.
- Institucional.
- Paginas dinamicas de menu.

### 5.7 Treinamentos

- Botao de acesso aos treinamentos.
- Geracao de token JWT com dados do colaborador.
- Redirecionamento para o ambiente de treinamentos.

## 6. Fora do escopo desta etapa

Nao fazem parte do escopo atual:

- Recriacao completa do banco de dados.
- Desenvolvimento do site externo de treinamentos.
- Implementacao de painel BI ou dashboard analitico avancado.
- Migracao para framework PHP, como Laravel ou Symfony.
- Criacao de aplicativo mobile.
- Redesenho completo de todos os fluxos de negocio.
- Publicacao automatizada em ambiente de producao.
- Configuracao definitiva de infraestrutura do servidor.

## 7. Premissas

- O projeto continuara em PHP com MySQL/MariaDB.
- Os dados principais serao obtidos das tabelas existentes do portal.
- O controle de acesso sera baseado na sessao e nos dados do usuario autenticado.
- Arquivos sensiveis de configuracao nao devem ser versionados.
- Uploads, dependencias e credenciais devem permanecer fora do Git.
- O ambiente de producao tera suas proprias configuracoes locais.

## 8. Restricoes

- O portal depende de conexao com banco de dados configurado corretamente.
- O envio de e-mail depende de configuracao SMTP valida.
- A integracao de treinamentos depende de chave JWT compartilhada com o ambiente externo.
- Algumas funcionalidades dependem da estrutura atual das tabelas do banco.
- O projeto precisa manter compatibilidade com o servidor utilizado pela MCM.

## 9. Padrao tecnico adotado

As telas devem seguir o modelo:

```text
pages/nome_da_pagina.php          Controller, autenticacao, consultas e preparacao
includes/helpers/nome.php         Funcoes auxiliares e formatacao
includes/views/nome-view.php      HTML da tela
css/pages/nome.css                Estilos especificos
js/pages/nome.js                  Interacoes especificas
```

Componentes reutilizaveis devem ficar em:

```text
includes/layout_start.php
includes/layout_end.php
includes/header.php
includes/topbar.php
includes/sidebar.php
includes/footer.php
```

## 10. Criterios de aceite

O escopo sera considerado atendido quando:

- O portal estiver versionado no GitHub.
- O README e documentos de apoio estiverem disponiveis no repositorio.
- As telas refatoradas estiverem separadas por responsabilidade.
- Componentes compartilhados estiverem sendo reutilizados.
- Configuracoes sensiveis nao estiverem versionadas.
- O login, home, recuperacao de senha e paginas principais estiverem acessiveis localmente.
- A integracao de treinamentos estiver preparada para uso com JWT.

## 11. Riscos

- Credenciais reais serem adicionadas por engano ao versionamento.
- Divergencia entre banco local e banco de producao.
- Dependencias de servidor nao documentadas.
- Alteracoes visuais afetarem telas ainda nao revisadas.
- Falhas de permissao em diretorios de sessao ou upload no ambiente final.

## 12. Proximos passos sugeridos

- Validar o fluxo completo em ambiente de homologacao.
- Revisar permissao de usuarios e botoes restritos.
- Documentar modelo de banco de dados.
- Criar checklist de publicacao em producao.
- Criar rotina de backup antes de cada deploy.
- Evoluir o design system conforme novas telas forem padronizadas.
