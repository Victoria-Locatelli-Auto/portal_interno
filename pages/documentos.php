<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/helpers/documentos.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$first_name = Session::get('first_name') ?? 'Usuário';
$page_title = 'Documentos';
$page_css = 'css/pages/documentos.css';
$page_js = 'js/pages/documentos.js';

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

$documents_data = documentosPageData($conn, $_GET);

$menu_id = $documents_data['menu_id'];
$submenu_id = $documents_data['submenu_id'];
$search = $documents_data['search'];
$current_page = $documents_data['current_page'];
$items_per_page = $documents_data['items_per_page'];
$total_documents = $documents_data['total_documents'];
$total_pages = $documents_data['total_pages'];
$documents = $documents_data['documents'];
$menus = $documents_data['menus'];
$submenus = $documents_data['submenus'];
$selected_menu_name = $documents_data['selected_menu_name'];
$can_import_documents = documentosCanImport(Session::getAccessLevel());

require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/views/documentos-view.php';
require_once __DIR__ . '/../includes/layout_end.php';
