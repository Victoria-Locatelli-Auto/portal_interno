<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/helpers/dadosti.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$first_name = Session::get('first_name') ?? 'Usuário';

$menu = dadosTiMenu($conn);

if (!$menu) {
    header('Location: ../index.php');
    exit;
}

$menu_id = (int)$menu['id'];
$submenus = dadosTiSubmenus($conn, $menu_id);
$default_documents = empty($submenus) ? dadosTiDocuments($conn, $menu_id) : [];
$documents_by_submenu = [];

foreach ($submenus as $submenu) {
    $documents_by_submenu[(int)$submenu['id']] = dadosTiDocuments($conn, $menu_id, (int)$submenu['id']);
}

$custom_menus = [];
$menus_result = $conn->query("
    SELECT id, title, url, icon, menu_type
    FROM custom_menus
    WHERE is_active = TRUE
    ORDER BY order_position ASC
");

if ($menus_result) {
    $custom_menus = $menus_result->fetch_all(MYSQLI_ASSOC);
}

$page_title = $menu['title'] ?? 'Dados TI';
$page_css = 'css/pages/dadosti.css';
$page_js = 'js/pages/dadosti.js';

require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/views/dadosti-view.php';
require_once __DIR__ . '/../includes/layout_end.php';
