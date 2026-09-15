<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/helpers/admin_sugestoes.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$first_name = Session::get('first_name') ?? 'Usuário';
$page_title = 'Admin - Sugestões';
$page_css = 'css/pages/admin_sugestoes.css';
$page_js = 'js/pages/admin_sugestoes.js';
$can_view_suggestions = adminSuggestionsCanView();

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

require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/views/admin-sugestoes-view.php';
require_once __DIR__ . '/../includes/layout_end.php';
