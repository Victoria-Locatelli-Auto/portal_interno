<?php
/**
 * Funções auxiliares para gerenciamento de menus
 */

/**
 * Converte uma string em um slug válido para URL
 * @param string $text
 * @return string
 */
function slugify($text) {
    // Converte para minúsculas
    $text = strtolower($text);
    
    // Remove acentos
    $text = preg_replace('/[áàâãä]/u', 'a', $text);
    $text = preg_replace('/[éèêë]/u', 'e', $text);
    $text = preg_replace('/[íìîï]/u', 'i', $text);
    $text = preg_replace('/[óòôõö]/u', 'o', $text);
    $text = preg_replace('/[úùûü]/u', 'u', $text);
    $text = preg_replace('/[ç]/u', 'c', $text);
    
    // Substitui espaços e caracteres especiais por underscore
    $text = preg_replace('/[^a-z0-9_-]/', '_', $text);
    
    // Remove underscores múltiplos
    $text = preg_replace('/_+/', '_', $text);
    
    // Remove underscores do início e fim
    $text = trim($text, '_');
    
    return $text;
}

/**
 * Gera uma URL válida a partir do título
 * @param string $title
 * @param string $pagesDir
 * @return string
 */
function generatePageUrl($title, $pagesDir = '/pages/') {
    $slug = slugify($title);
    $baseUrl = $pagesDir . $slug . '.php';
    
    return $baseUrl;
}

/**
 * Verifica se um arquivo de página já existe e gera um nome alternativo se necessário
 * @param string $baseUrl
 * @param string $baseDir
 * @return string
 */
function getUniquePageUrl($baseUrl, $baseDir = '../pages/') {
    // Remove /pages/ e .php para processar
    $filename = str_replace('/pages/', '', $baseUrl);
    $filename = str_replace('.php', '', $filename);
    
    $filepath = $baseDir . $filename . '.php';
    
    // Se o arquivo não existe, retorna a URL original
    if (!file_exists($filepath)) {
        return $baseUrl;
    }
    
    // Se existe, adiciona sufixo numérico
    $counter = 1;
    while (file_exists($baseDir . $filename . '_' . $counter . '.php')) {
        $counter++;
    }
    
    return '/pages/' . $filename . '_' . $counter . '.php';
}

/**
 * Cria um arquivo PHP padrão para uma página de menu
 * @param string $filepath
 * @param string $title
 * @return bool
 */
function createDefaultMenuPage($filepath, $title) {
    // Template HTML padrão para página de menu
    $template = <<<'EOT'
<?php
/**
 * Página dinâmica para menu
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

// Redirecionar para a página de menu genérica
$menu_id = (int)($_GET['menu_id'] ?? 0);
if ($menu_id > 0) {
    header('Location: menu_page.php?menu_id=' . $menu_id);
    exit;
}

header('Location: ../index.php');
exit;
?>
EOT;
    
    // Tenta criar o arquivo
    if (@file_put_contents($filepath, $template)) {
        return true;
    }
    
    return false;
}

/**
 * Lista de ícones FontAwesome recomendados
 */
function getIconList() {
    return [
        'fas fa-file-pdf' => '📄 PDF',
        'fas fa-folder' => '📁 Pasta',
        'fas fa-chart-bar' => '📊 Gráfico',
        'fas fa-briefcase' => '💼 Trabalho',
        'fas fa-users' => '👥 Usuários',
        'fas fa-cog' => '⚙️ Configurações',
        'fas fa-heart' => '❤️ Favoritos',
        'fas fa-star' => '⭐ Destaque',
        'fas fa-bell' => '🔔 Avisos',
        'fas fa-envelope' => '✉️ Mensagens',
        'fas fa-calendar' => '📅 Calendário',
        'fas fa-clock' => '⏱️ Tempo',
        'fas fa-lock' => '🔒 Segurança',
        'fas fa-download' => '⬇️ Download',
        'fas fa-upload' => '⬆️ Upload',
    ];
}
?>
