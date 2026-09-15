<?php

function indexMenuHref(array $menu, string $base_url): string
{
    if (($menu['menu_type'] ?? '') === 'page') {
        return $base_url . '/pages/menu_page.php?menu_id=' . (int) $menu['id'];
    }

    return $menu['url'] ?? '#';
}

function indexMenuTarget(array $menu): string
{
    return ($menu['menu_type'] ?? '') === 'page'
        ? ''
        : 'target="_blank" rel="noopener noreferrer"';
}

function indexMenuIcon(array $menu): string
{
    return !empty($menu['icon']) ? $menu['icon'] : 'fas fa-folder';
}

function indexQuickAccessSubtitle(string $title): string
{
    $normalized = mb_strtolower(trim($title), 'UTF-8');

    $subtitles = [
        'chamado ti' => 'Solicite suporte e acompanhe atendimentos',
        'sistema de chamados' => 'Solicite suporte e acompanhe atendimentos',
        'processos' => 'Consulte fluxos e procedimentos internos',
        'base mcm' => 'Encontre documentos e informações',
        'politicas' => 'Normas, políticas e diretrizes',
        'políticas' => 'Normas, políticas e diretrizes',
        'vagas abertas' => 'Oportunidades para crescer com a MCM',
        'institucional' => 'Conheça informações da empresa',
        'contatos' => 'Encontre pessoas e ramais',
        'treinamentos' => 'Acesse conteúdos de capacitação',
        'mcm noticias' => 'Fique por dentro das novidades',
        'mcm notícias' => 'Fique por dentro das novidades',
        'compliance' => 'Canais e orientações de conduta',
        'denuncias' => 'Acesse canais de denúncia',
        'denúncias' => 'Acesse canais de denúncia',
    ];

    return $subtitles[$normalized] ?? 'Acesse este recurso do Portal MCM';
}

function indexQuickAccessTone(int $position): string
{
    $tones = ['blue', 'purple', 'cyan', 'amber', 'rose', 'green'];

    return $tones[$position % count($tones)];
}

function indexFormatCollaboratorName(?string $name): string
{
    $name = trim((string) $name);

    if ($name === '') {
        return 'Colaborador';
    }

    return mb_convert_case(mb_strtolower($name, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
}

function indexIsPdfDocument(array $document): bool
{
    return stripos($document['filename'] ?? '', '.pdf') !== false;
}

function indexPageLink(string $search_query, int $page): string
{
    return '?q=' . urlencode($search_query) . '&page=' . $page;
}

function indexFormatNoticeDescription(?string $description): string
{
    $description = str_replace(["\r\n", "\r", "\n"], ' ', (string) $description);
    $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');

    return preg_replace_callback(
        '/(https?:\/\/[^\s<]+)/i',
        function (array $matches): string {
            $url = rtrim($matches[1], '.,;:!?)');
            $href = htmlspecialchars(html_entity_decode($url, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');

            if (strpos($href, 'view_document.php') !== false) {
                return '<a href="' . $href . '" target="_blank" rel="noopener noreferrer" class="notice-link"><i class="fas fa-file-pdf"></i> Ver documento</a>';
            }

            return '<a href="' . $href . '" target="_blank" rel="noopener noreferrer" class="notice-link">' . $url . '</a>';
        },
        $description
    );
}
