# Documento de Requisitos - Portal MCM

## 1. Identificacao

**Projeto:** Portal MCM  
**Documento:** Requisitos funcionais e nao funcionais  
**Area responsavel:** PPGC/TI  
**Repositorio:** https://github.com/Victoria-Locatelli-Auto/portal_interno.git

## 2. Objetivo do documento

Este documento descreve os principais requisitos do Portal MCM, contemplando funcionalidades, regras de negocio, seguranca, integracoes, requisitos tecnicos e criterios de qualidade esperados para o projeto.

## 3. Perfis de usuario

### 3.1 Colaborador

Usuario comum do portal. Pode acessar a home, documentos permitidos, avisos, contatos, treinamentos e demais links disponiveis.

### 3.2 Gestor

Usuario com permissoes ampliadas conforme regras internas. Pode acessar recursos especificos definidos pelo portal.

### 3.3 Editor

Usuario autorizado a cadastrar, importar, editar ou acompanhar conteudos conforme permissao da aplicacao.

### 3.4 Administrador

Usuario com acesso as telas administrativas, incluindo usuarios, menus, avisos, sugestoes e monitoramento.

### 3.5 Equipe de TI

Grupo responsavel por manutencao, configuracao, suporte tecnico, monitoramento e evolucao do portal.

## 4. Requisitos funcionais

### RF001 - Login de usuario

O sistema deve permitir que usuarios autenticados acessem o portal usando usuario ou e-mail e senha.

### RF002 - Primeiro acesso

O sistema deve permitir que novos usuarios realizem o primeiro cadastro informando dados obrigatorios, exceto WhatsApp Corporativo, que deve permanecer opcional.

### RF003 - Recuperacao de senha

O sistema deve permitir que o usuario solicite recuperacao de senha por e-mail corporativo.

### RF004 - Controle de sessao

O sistema deve iniciar, validar e encerrar sessoes de usuarios autenticados.

### RF005 - Redirecionamento de usuarios nao autenticados

O sistema deve redirecionar usuarios nao autenticados para a tela de login ao tentar acessar areas protegidas.

### RF006 - Home personalizada

O sistema deve exibir saudacao personalizada com o nome do colaborador autenticado.

### RF007 - Busca na home

O sistema deve disponibilizar campo de busca para localizar sistemas, documentos ou informacoes do portal.

### RF008 - Acessos frequentes

O sistema deve exibir cards ou botoes de acesso rapido para os principais recursos do portal.

### RF009 - Avisos

O sistema deve exibir avisos publicados para os colaboradores.

### RF010 - Menu lateral

O sistema deve exibir menu lateral reutilizavel nas telas internas do portal.

### RF011 - Cabecalho e topbar

O sistema deve utilizar componentes compartilhados de cabecalho e topbar nas telas internas.

### RF012 - Rodape compartilhado

O sistema deve exibir rodape padronizado com a informacao: © 2026 Desenvolvido por PPGC/TI.

### RF013 - Gerenciamento de documentos

O sistema deve permitir listar, visualizar, importar e gerenciar documentos conforme permissao do usuario.

### RF014 - Adicao manual de documento

O sistema deve permitir cadastrar documento manualmente a partir de texto inserido pelo usuario autorizado.

### RF015 - Aprovacao de documentos

O sistema deve permitir que usuarios autorizados aprovem documentos pendentes.

### RF016 - Meus documentos

O sistema deve permitir que o usuario visualize documentos enviados por ele.

### RF017 - Contatos

O sistema deve listar contatos ativos com filtros por nome, filial e funcao.

### RF018 - Gerenciamento de menus

O sistema deve permitir a administradores gerenciar menus e submenus customizados.

### RF019 - Gerenciamento de avisos

O sistema deve permitir a administradores, editores ou perfis autorizados gerenciar avisos do portal.

### RF020 - Sugestoes

O sistema deve permitir consultar sugestoes enviadas pelos usuarios em tela administrativa.

### RF021 - Monitoramento do sistema

O sistema deve disponibilizar tela de monitoramento para usuarios administradores.

### RF022 - Integracao com treinamentos

O sistema deve gerar token JWT com dados do colaborador e redirecionar para o ambiente de treinamentos configurado.

### RF023 - Dados no token de treinamento

O token JWT deve conter, no minimo, ID do funcionario, nome, cargo e filial.

### RF024 - Restricao de recursos por perfil

O sistema deve permitir que botoes, telas ou recursos sejam exibidos apenas para usuarios ou grupos especificos, conforme regra definida no banco ou na sessao.

## 5. Requisitos nao funcionais

### RNF001 - Organizacao do codigo

O codigo deve seguir separacao entre controller, helper, view, CSS e JavaScript sempre que aplicavel.

### RNF002 - Reutilizacao de componentes

Elementos comuns, como sidebar, topbar, cabecalho e rodape, devem ser reutilizados em vez de duplicados por tela.

### RNF003 - Manutenibilidade

O projeto deve manter estrutura clara de pastas para facilitar evolucao e manutencao pela equipe de TI.

### RNF004 - Compatibilidade

O sistema deve funcionar em ambiente PHP 8.2 ou superior, com MySQL ou MariaDB.

### RNF005 - Responsividade

As telas devem se adaptar a diferentes resolucoes de tela sempre que aplicavel.

### RNF006 - Performance

Consultas devem ser objetivas e evitar carregamento desnecessario de dados.

### RNF007 - Seguranca de credenciais

Credenciais reais, chaves JWT, dados SMTP e senhas de banco nao devem ser versionadas.

### RNF008 - Versionamento

O projeto deve ser mantido em repositorio Git, com commits claros e historico rastreavel.

### RNF009 - Padrao visual

As telas devem seguir o design system definido para cores, fontes, botoes, cards e espacamentos.

### RNF010 - Acessibilidade basica

Campos, botoes e links devem manter textos claros, contraste adequado e estrutura navegavel.

## 6. Regras de negocio

### RN001 - Usuario autenticado

Somente usuarios autenticados podem acessar paginas internas do portal.

### RN002 - Primeiro acesso

Todos os campos do primeiro acesso devem ser obrigatorios, exceto WhatsApp Corporativo.

### RN003 - Recuperacao de senha

A recuperacao de senha deve enviar instrucoes para o e-mail corporativo informado, sem alterar a regra de envio existente.

### RN004 - Administracao

Telas administrativas devem validar permissao antes de exibir ou executar acoes sensiveis.

### RN005 - Treinamentos

O acesso ao ambiente de treinamentos deve ocorrer por redirecionamento com token JWT valido.

### RN006 - Expiracao do token

O token JWT de treinamentos deve ter tempo de expiracao configuravel.

### RN007 - Configuracoes locais

Cada ambiente deve possuir suas proprias configuracoes em arquivos locais nao versionados.

## 7. Requisitos de seguranca

- Utilizar `password_hash` e `password_verify` para senhas.
- Nao versionar arquivos reais de configuracao.
- Usar prepared statements nas consultas SQL com parametros externos.
- Validar sessao antes de acessar paginas protegidas.
- Restringir telas administrativas por perfil.
- Proteger chave JWT dos treinamentos.
- Evitar expor mensagens tecnicas sensiveis ao usuario final em producao.

## 8. Integracoes

### 8.1 Banco de dados

O portal deve se conectar ao banco configurado em `config/database.php`.

### 8.2 E-mail

O envio de e-mails deve usar configuracao definida em `config/email.php`.

### 8.3 Treinamentos

O portal deve redirecionar para a URL configurada em `config/training.php`, enviando o token JWT por parametro.

### 8.4 IA e busca inteligente

Quando aplicavel, o portal deve usar configuracoes de IA definidas em `config/ai.php`.

## 9. Requisitos de dados

O portal depende de tabelas como:

- `users`
- `funcao`
- `filiais`
- `custom_menus`
- `custom_submenus`
- `documents`
- `document_menus`
- `notices`
- `password_reset_tokens`
- `searches`
- `system_config`

## 10. Criterios de aceite

- O usuario consegue acessar a tela de login.
- O usuario consegue realizar login com credenciais validas.
- O primeiro acesso valida campos obrigatorios corretamente.
- A recuperacao de senha mantém o fluxo de envio de e-mail.
- A home exibe saudacao personalizada e acessos frequentes.
- Componentes comuns sao reutilizados nas telas refatoradas.
- Arquivos sensiveis permanecem fora do Git.
- O projeto pode ser executado localmente com PHP e configuracoes adequadas.
- O repositorio contem README, escopo, requisitos e design system.

## 11. Pendencias e evolucoes futuras

- Documentar o modelo fisico do banco de dados.
- Criar checklist de deploy para producao.
- Definir matriz oficial de permissoes por perfil.
- Validar todos os fluxos em ambiente de homologacao.
- Criar testes automatizados para fluxos criticos.
- Padronizar telas restantes conforme o design system.
