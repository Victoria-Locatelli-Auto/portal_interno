from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import cm
from reportlab.platypus import (
    BaseDocTemplate,
    Frame,
    PageBreak,
    PageTemplate,
    Paragraph,
    Spacer,
    Table,
    TableStyle,
)


OUTPUT = "docs/design-system.pdf"

BLUE = colors.HexColor("#03285F")
DARK_BLUE = colors.HexColor("#041e42")
HOVER_BLUE = colors.HexColor("#0a397c")
BG = colors.HexColor("#f4f7fb")
TEXT = colors.HexColor("#061733")
MUTED = colors.HexColor("#526582")
LINE = colors.HexColor("#dde5f0")
SUCCESS = colors.HexColor("#168567")
ERROR = colors.HexColor("#cf4343")


def on_page(canvas, doc):
    canvas.saveState()
    width, height = A4
    canvas.setFillColor(BLUE)
    canvas.rect(0, height - 1.15 * cm, width, 1.15 * cm, fill=1, stroke=0)
    canvas.setFillColor(colors.white)
    canvas.setFont("Helvetica-Bold", 9)
    canvas.drawString(1.5 * cm, height - 0.72 * cm, "Portal MCM - Design System")
    canvas.setFont("Helvetica", 8)
    canvas.drawRightString(width - 1.5 * cm, 0.8 * cm, f"Pagina {doc.page}")
    canvas.setStrokeColor(LINE)
    canvas.line(1.5 * cm, 1.15 * cm, width - 1.5 * cm, 1.15 * cm)
    canvas.restoreState()


def style_sheet():
    styles = getSampleStyleSheet()
    styles.add(
        ParagraphStyle(
            "CoverTitle",
            parent=styles["Title"],
            fontName="Helvetica-Bold",
            fontSize=31,
            leading=36,
            textColor=BLUE,
            alignment=TA_CENTER,
            spaceAfter=16,
        )
    )
    styles.add(
        ParagraphStyle(
            "CoverSub",
            parent=styles["Normal"],
            fontName="Helvetica",
            fontSize=13,
            leading=19,
            textColor=MUTED,
            alignment=TA_CENTER,
            spaceAfter=22,
        )
    )
    styles.add(
        ParagraphStyle(
            "H1Custom",
            parent=styles["Heading1"],
            fontName="Helvetica-Bold",
            fontSize=19,
            leading=24,
            textColor=BLUE,
            spaceBefore=10,
            spaceAfter=10,
        )
    )
    styles.add(
        ParagraphStyle(
            "H2Custom",
            parent=styles["Heading2"],
            fontName="Helvetica-Bold",
            fontSize=13,
            leading=17,
            textColor=DARK_BLUE,
            spaceBefore=8,
            spaceAfter=6,
        )
    )
    styles.add(
        ParagraphStyle(
            "BodyCustom",
            parent=styles["BodyText"],
            fontName="Helvetica",
            fontSize=9.6,
            leading=14,
            textColor=TEXT,
            spaceAfter=6,
        )
    )
    styles.add(
        ParagraphStyle(
            "SmallMuted",
            parent=styles["BodyText"],
            fontName="Helvetica",
            fontSize=8.6,
            leading=12,
            textColor=MUTED,
            spaceAfter=4,
        )
    )
    styles.add(
        ParagraphStyle(
            "CodeBlock",
            parent=styles["BodyText"],
            fontName="Courier",
            fontSize=8.2,
            leading=11,
            textColor=colors.HexColor("#23324a"),
            backColor=colors.HexColor("#eef3fb"),
            borderColor=LINE,
            borderWidth=0.5,
            borderPadding=6,
            spaceBefore=4,
            spaceAfter=8,
        )
    )
    return styles


def p(text, style):
    return Paragraph(text, style)


def bullet(text, styles):
    return Paragraph(f"- {text}", styles["BodyCustom"])


def table(data, widths, header=True):
    t = Table(data, colWidths=widths, hAlign="LEFT")
    style = [
        ("GRID", (0, 0), (-1, -1), 0.35, LINE),
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("LEFTPADDING", (0, 0), (-1, -1), 7),
        ("RIGHTPADDING", (0, 0), (-1, -1), 7),
        ("TOPPADDING", (0, 0), (-1, -1), 6),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 6),
        ("FONTNAME", (0, 0), (-1, -1), "Helvetica"),
        ("FONTSIZE", (0, 0), (-1, -1), 8.3),
    ]
    if header:
        style.extend(
            [
                ("BACKGROUND", (0, 0), (-1, 0), BLUE),
                ("TEXTCOLOR", (0, 0), (-1, 0), colors.white),
                ("FONTNAME", (0, 0), (-1, 0), "Helvetica-Bold"),
            ]
        )
    t.setStyle(TableStyle(style))
    return t


def color_swatch(hex_color):
    return Paragraph(
        f'<font color="{hex_color}">■</font> {hex_color}',
        ParagraphStyle("Swatch", fontName="Helvetica-Bold", fontSize=9, leading=12),
    )


def build():
    styles = style_sheet()
    doc = BaseDocTemplate(
        OUTPUT,
        pagesize=A4,
        rightMargin=1.5 * cm,
        leftMargin=1.5 * cm,
        topMargin=1.7 * cm,
        bottomMargin=1.5 * cm,
        title="Design System - Portal MCM",
        author="PPGC/TI",
    )
    frame = Frame(doc.leftMargin, doc.bottomMargin, doc.width, doc.height, id="normal")
    doc.addPageTemplates([PageTemplate(id="main", frames=[frame], onPage=on_page)])

    story = []
    story.append(Spacer(1, 4.5 * cm))
    story.append(p("Design System", styles["CoverTitle"]))
    story.append(p("Portal MCM", styles["CoverTitle"]))
    story.append(
        p(
            "Guia visual e estrutural das telas refatoradas: login, recuperacao de senha, home, componentes compartilhados e padroes para novas paginas.",
            styles["CoverSub"],
        )
    )
    story.append(Spacer(1, 0.5 * cm))
    story.append(
        table(
            [
                ["Versao", "Projeto", "Area responsavel"],
                ["1.0", "Portal MCM", "PPGC/TI"],
            ],
            [3.4 * cm, 6.2 * cm, 5.4 * cm],
        )
    )
    story.append(PageBreak())

    story.append(p("1. Identidade visual", styles["H1Custom"]))
    story.append(
        p(
            "A identidade visual do Portal MCM usa azul institucional, fundos claros, cards limpos e componentes reutilizaveis. O objetivo e manter as telas profissionais, legiveis e consistentes.",
            styles["BodyCustom"],
        )
    )
    story.append(p("Paleta de cores", styles["H2Custom"]))
    story.append(
        table(
            [
                ["Uso", "Cor", "Aplicacao"],
                ["Azul principal", color_swatch("#03285F"), "Titulos, botoes de acesso e destaques"],
                ["Azul hover", color_swatch("#0a397c"), "Hover de botoes e cards escuros"],
                ["Azul escuro", color_swatch("#041e42"), "Login, botoes principais e navegacao"],
                ["Fundo claro", color_swatch("#f4f7fb"), "Home e dashboards"],
                ["Texto secundario", color_swatch("#526582"), "Subtitulos e descricoes"],
                ["Borda clara", color_swatch("#dde5f0"), "Inputs, cards e paineis"],
                ["Sucesso", color_swatch("#168567"), "Alertas positivos"],
                ["Erro", color_swatch("#cf4343"), "Alertas de erro"],
            ],
            [4.2 * cm, 4.4 * cm, 7.4 * cm],
        )
    )
    story.append(p("Tipografia", styles["H2Custom"]))
    story.append(p("Fonte base: Inter, Segoe UI, Arial, sans-serif.", styles["BodyCustom"]))
    story.append(p('Titulos da home: "Trebuchet MS", "Segoe UI", Arial, sans-serif.', styles["BodyCustom"]))
    story.append(bullet("Titulos principais usam #03285F.", styles))
    story.append(bullet("Textos de apoio usam #526582.", styles))
    story.append(bullet("Evitar fontes externas por CDN para reduzir dependencia do servidor.", styles))

    story.append(p("2. Arquitetura de telas", styles["H1Custom"]))
    story.append(
        table(
            [
                ["Camada", "Local", "Responsabilidade"],
                ["Controller", "*.php ou pages/*.php", "Sessao, autenticacao, consultas e variaveis da tela"],
                ["Helpers", "includes/helpers/*.php", "Regras, formatacoes, validacoes e consultas auxiliares"],
                ["Views", "includes/views/*-view.php", "HTML da tela"],
                ["CSS", "css/pages/*.css ou css/*.css", "Estilos separados por tela"],
                ["JS", "js/pages/*.js", "Interacoes especificas da tela"],
                ["Componentes", "includes/*.php", "Sidebar, topbar, footer e layout compartilhado"],
            ],
            [3.0 * cm, 5.1 * cm, 7.9 * cm],
        )
    )

    story.append(p("3. Componentes globais", styles["H1Custom"]))
    story.append(p("Footer", styles["H2Custom"]))
    story.append(
        p(
            "O rodape deve sempre ser chamado por includes/footer.php. Nao duplicar HTML de rodape dentro das telas.",
            styles["BodyCustom"],
        )
    )
    story.append(p("&copy; &lt;?php echo date('Y'); ?&gt; Desenvolvido por PPGC/TI", styles["CodeBlock"]))
    story.append(p("Layout interno", styles["H2Custom"]))
    story.append(
        p(
            "Telas autenticadas usam layout_start.php e layout_end.php para herdar sidebar, topbar e footer.",
            styles["BodyCustom"],
        )
    )

    story.append(p("4. Login", styles["H1Custom"]))
    story.append(
        table(
            [
                ["Arquivo", "Funcao"],
                ["login.php", "Controller da tela"],
                ["includes/helpers/login.php", "Redirect, cache, login e cadastro"],
                ["includes/views/login-view.php", "HTML"],
                ["css/login.css", "Visual"],
                ["js/pages/login.js", "Abas e sanfona"],
            ],
            [6.2 * cm, 9.8 * cm],
        )
    )
    story.append(bullet("Layout em duas colunas: imagem fixa a esquerda e formulario a direita.", styles))
    story.append(bullet("Cadastro com efeito sanfona vertical.", styles))
    story.append(bullet("Todos os campos obrigatorios, exceto WhatsApp Corporativo.", styles))
    story.append(bullet("E-mail restrito a @mcmtocantins.com.", styles))

    story.append(p("5. Recuperar senha", styles["H1Custom"]))
    story.append(
        p(
            "A tela de recuperacao segue o mesmo padrao visual do login e chama o footer compartilhado. Ajustes visuais nao devem alterar a regra de envio de e-mail.",
            styles["BodyCustom"],
        )
    )
    story.append(
        p(
            "Texto principal: Solicite a recuperacao de senha de forma rapida e segura. Informe seu e-mail corporativo para receber as instrucoes de acesso.",
            styles["CodeBlock"],
        )
    )

    story.append(p("6. Home", styles["H1Custom"]))
    story.append(p("Cabecalho", styles["H2Custom"]))
    story.append(p("Bem-vindo {Nome do colaborador} ao Portal MCM", styles["CodeBlock"]))
    story.append(p("Informacoes, solicitacoes, documentos e ferramentas do dia a dia, em um so lugar.", styles["CodeBlock"]))
    story.append(p("O que voce precisa hoje?", styles["CodeBlock"]))
    story.append(bullet("Nome do colaborador formatado com iniciais maiusculas.", styles))
    story.append(bullet("Data, dia da semana, hora e card de horario foram removidos.", styles))

    story.append(p("Busca", styles["H2Custom"]))
    story.append(bullet("Campo largo, branco, com borda clara e icone de busca.", styles))
    story.append(bullet("Atalho Ctrl + K foca o campo de busca.", styles))
    story.append(p("Placeholder: Busque por sistemas, documentos ou pessoas...", styles["CodeBlock"]))

    story.append(p("Cards de acesso frequente", styles["H2Custom"]))
    story.append(
        p(
            "Os cards usam os menus dinamicos de custom_menus e mantem cards fixos para Institucional, Contatos e Treinamentos.",
            styles["BodyCustom"],
        )
    )
    story.append(
        p(
            'Overlay atual: linear-gradient(rgba(3, 40, 95, 0.90), rgba(3, 40, 95, 0.90)), url("../../index_files/login-trucks.jpg")',
            styles["CodeBlock"],
        )
    )
    story.append(bullet("Nao alterar os links ao mexer no visual dos cards.", styles))
    story.append(bullet("Texto e icones brancos, com hover clareando levemente.", styles))

    story.append(PageBreak())
    story.append(p("7. Botoes, formularios e alertas", styles["H1Custom"]))
    story.append(
        table(
            [
                ["Elemento", "Padrao"],
                ["Botao primario", "Fundo #041e42, texto branco, raio 8px, peso 700"],
                ["Hover primario", "Fundo #0a315f"],
                ["Campo", "Fundo #eaf3ff, borda #cfd7e2, raio 7px, altura minima 49px"],
                ["Focus", "Borda #041e42 e sombra rgba(4, 30, 66, 0.14)"],
                ["Alerta sucesso", "Fundo claro verde, texto #168567"],
                ["Alerta erro", "Fundo claro vermelho, texto #cf4343"],
            ],
            [4.2 * cm, 11.8 * cm],
        )
    )

    story.append(p("8. Assets", styles["H1Custom"]))
    story.append(bullet("Imagem principal: index_files/login-trucks.jpg", styles))
    story.append(bullet("Logo: index_files/logo_mcm.webp", styles))
    story.append(bullet("Ao subir para o servidor, enviar tambem CSS, JS e assets usados por caminhos relativos.", styles))

    story.append(p("9. Checklist para novas telas", styles["H1Custom"]))
    checklist = [
        "Controller fino, sem HTML extenso.",
        "Regras em includes/helpers.",
        "HTML em includes/views.",
        "CSS separado.",
        "JS separado.",
        "Footer, sidebar e topbar chamados pelos componentes globais.",
        "Nao duplicar estrutura de layout.",
        "Validar com php -l.",
        "Testar se CSS, JS e assets respondem 200.",
        "Confirmar responsividade em desktop e mobile.",
    ]
    for item in checklist:
        story.append(bullet(item, styles))

    doc.build(story)


if __name__ == "__main__":
    build()
