# Design System - Portal MCM

Este documento resume o padrao visual definido durante a refatoracao do Portal MCM. Use como referencia para novas telas e ajustes nas paginas existentes.

## Identidade Visual

### Cores

| Uso | Cor | Observacao |
| --- | --- | --- |
| Azul principal | `#03285F` | Titulos, botoes de acesso e destaques |
| Azul hover | `#0a397c` | Hover de botoes/cards escuros |
| Azul escuro | `#041e42` | Login, botoes principais e navegacao |
| Fundo claro | `#f4f7fb` | Fundo da home/dashboard |
| Texto principal | `#061733` | Textos fortes |
| Texto secundario | `#526582` | Subtitulos e descricoes |
| Borda clara | `#dde5f0` | Inputs, cards e paineis |
| Campo input | `#eaf3ff` | Fundo de campos do login |
| Sucesso | `#168567` | Alertas positivos |
| Erro | `#cf4343` | Alertas de erro |

### Tipografia

Fonte base:

```css
font: 14px/1.5 Inter, Segoe UI, Arial, sans-serif;
```

Fonte dos titulos da home:

```css
--title-font: "Trebuchet MS", "Segoe UI", Arial, sans-serif;
```

Regras:

- Titulos principais usam `#03285F`.
- Textos de apoio usam `#526582`.
- Evitar fontes externas por CDN para nao depender de internet no servidor.
- Nome do colaborador na home deve usar iniciais maiusculas via `indexFormatCollaboratorName()`.

## Estrutura de Arquivos

Padrao usado na refatoracao:

| Camada | Local |
| --- | --- |
| Controller | `*.php` ou `pages/*.php` |
| Helpers/regras | `includes/helpers/*.php` |
| HTML da tela | `includes/views/*-view.php` |
| CSS da tela | `css/pages/*.css` ou `css/*.css` para telas raiz |
| JS da tela | `js/pages/*.js` |
| Componentes globais | `includes/topbar.php`, `includes/sidebar.php`, `includes/footer.php`, `includes/layout_start.php`, `includes/layout_end.php` |

## Componentes Globais

### Footer

Arquivo:

```text
includes/footer.php
```

Conteudo padrao:

```php
<footer class="footer">
    © <?php echo date('Y'); ?> Desenvolvido por PPGC/TI
</footer>
```

Regras:

- Nao duplicar rodape diretamente nas telas.
- Sempre chamar o componente.
- Em telas especiais, como login e recuperar senha, estilizar pelo contexto, exemplo: `.login-box > .footer`.

### Layout Interno

Telas autenticadas devem usar:

```php
require __DIR__ . '/../includes/layout_start.php';
require __DIR__ . '/../includes/views/nome-da-view.php';
require __DIR__ . '/../includes/layout_end.php';
```

O layout inclui sidebar, topbar e footer.

## Login

Arquivos:

- `login.php`
- `includes/helpers/login.php`
- `includes/views/login-view.php`
- `css/login.css`
- `js/pages/login.js`

Padrao visual:

- Tela dividida em duas colunas.
- Imagem fixa dos caminhoes a esquerda: `index_files/login-trucks.jpg`.
- Formulario a direita.
- Logo em bloco azul.
- Botao principal azul escuro.
- Abas `Login` e `Primeiro Acesso`.
- Cadastro com efeito sanfona vertical.
- Apenas a coluna do formulario faz scroll.

Texto principal:

```text
Bem-vindo
Um espaço para colaboradores acessarem, de forma rápida e prática, informações, solicitações, documentos e ferramentas que facilitam o dia a dia de trabalho.
```

Regras de cadastro:

- Todos os campos obrigatorios, exceto `WhatsApp Corporativo`.
- E-mail precisa ser `@mcmtocantins.com`.
- Senha minima de 6 caracteres.

## Recuperar Senha

Arquivos:

- `forgot_password.php`
- `css/forgot_password.css`
- `includes/footer.php`

Padrao visual:

- Mesmo layout da tela de login.
- Imagem fixa a esquerda.
- Formulario a direita.
- Footer chamado pelo componente global.

Texto preservado:

```text
Solicite a recuperação de senha de forma rápida e segura. Informe seu e-mail corporativo para receber as instruções de acesso.
```

Regra importante:

- Ajustes visuais nao devem alterar o envio de e-mail.
- O envio continua usando `EmailManager()->sendPasswordResetEmail(...)`.

## Home

Arquivos:

- `index.php`
- `includes/helpers/index.php`
- `includes/views/index-view.php`
- `css/pages/index.css`
- `js/pages/index.js`

### Cabecalho

Titulo:

```text
Bem-vindo {Nome do colaborador} ao Portal MCM
```

Subtitulo:

```text
Informações, solicitações, documentos e ferramentas do dia a dia, em um só lugar.
```

Texto acima da busca:

```text
O que você precisa hoje?
```

Removido:

- Data.
- Dia da semana.
- Hora.
- Card de horario.

### Busca

Padrao:

- Campo largo.
- Fundo branco.
- Borda clara.
- Icone de busca.
- Atalho visual `Ctrl K`.
- JS foca a busca com `Ctrl + K`.

Placeholder:

```text
Busque por sistemas, documentos ou pessoas...
```

### Cards de Acesso Frequente

Fonte dos dados:

- Menus dinamicos vindos de `custom_menus`.
- Cards fixos adicionais:
  - Institucional
  - Contatos
  - Treinamentos

Visual atual:

```css
background-image:
    linear-gradient(rgba(3, 40, 95, 0.90), rgba(3, 40, 95, 0.90)),
    url("../../index_files/login-trucks.jpg");
```

Regras:

- Manter a imagem dos caminhoes no fundo.
- Aplicar overlay azul `#03285F` com opacidade alta.
- Texto branco.
- Hover clareia levemente com `brightness`.
- Nao alterar os links dos cards ao mexer no visual.

## Botoes

### Primario

Uso:

- Login.
- Envio de formulario.
- Acoes principais.

Padrao:

```css
background: #041e42;
color: #fff;
border-radius: 8px;
font-weight: 700;
```

Hover:

```css
background: #0a315f;
```

### Cards/Botoes de Acesso

Uso:

- Home / acessos frequentes.

Padrao:

- Altura compacta.
- Fundo com imagem e overlay azul.
- Icone branco.
- Texto branco em negrito.
- Cantos arredondados pequenos.

## Formularios

Padrao:

```css
background: #eaf3ff;
border: 1px solid #cfd7e2;
border-radius: 7px;
min-height: 49px;
```

Focus:

```css
border-color: #041e42;
box-shadow: 0 0 0 3px rgba(4, 30, 66, 0.14);
```

## Alertas

Erro:

```css
background: #fdecec;
color: #cf4343;
border: 1px solid #f3c7c7;
```

Sucesso:

```css
background: #e7f7f2;
color: #168567;
border: 1px solid #bfe8dc;
```

## Assets

Imagem principal:

```text
index_files/login-trucks.jpg
```

Logo:

```text
index_files/logo_mcm.webp
```

Regra:

- Ao subir para o servidor, enviar tambem os assets usados pelo CSS.
- Evitar caminhos absolutos para CSS/JS/assets em telas raiz; preferir caminhos relativos quando possivel.

## Checklist Para Novas Telas

- Controller fino, sem HTML extenso.
- Regras em `includes/helpers`.
- HTML em `includes/views`.
- CSS separado.
- JS separado.
- Footer, sidebar e topbar chamados pelos componentes globais.
- Nao duplicar estrutura de layout.
- Validar com `php -l`.
- Testar se CSS/JS/assets respondem `200`.
- Confirmar responsividade em desktop e mobile.
