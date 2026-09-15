<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/helpers/menu_page.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$user_id = Session::getUserId();
$first_name = Session::get('first_name') ?? 'Usuário';
$menu_id = (int)($_GET['menu_id'] ?? 0);

if ($menu_id <= 0) {
    header('Location: ../index.php');
    exit;
}

$menu = menuPageFindMenu($conn, $menu_id);

if (!$menu) {
    header('Location: ../index.php');
    exit;
}

$custom_menus = menuPageSidebarItems($conn);
$submenus = menuPageSubmenus($conn, $menu_id);
$documents_by_submenu = [];
$default_documents = [];

if ($submenus) {
    $documents_by_submenu = menuPageDocumentsBySubmenu($conn, $menu_id, $submenus);
} else {
    $default_documents = menuPageDocuments($conn, $menu_id);
}

$page_title = $menu['title'];
$page_css = 'css/pages/menu_page.css';
$page_js = 'js/pages/menu_page.js';

require __DIR__ . '/../includes/layout_start.php';
require __DIR__ . '/../includes/views/menu-page-view.php';
require __DIR__ . '/../includes/layout_end.php';
