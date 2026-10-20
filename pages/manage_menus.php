<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/MenuHelper.php';
require_once __DIR__ . '/../includes/helpers/manage_menus.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$first_name = Session::get('first_name') ?? 'Usuário';
$user = manageMenusCurrentUser($conn, (int)Session::getUserId());

if (!$user || ($user['access_level'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Acesso negado. Apenas administradores podem acessar esta página.');
}

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = manageMenusHandleAction($conn, $_POST);
    $success_msg = $result['success'];
    $error_msg = $result['error'];
    unset($_SESSION['manage_menus_csrf']);
}

$custom_menus = manageMenusSidebarItems($conn);
$menus_list = manageMenusList($conn);
$menu_stats = manageMenusStats($menus_list);
$icons = getIconList();
$manage_menus_csrf = manageMenusCsrfToken();
$page_title = 'Gerenciar Menus';
$page_css = 'css/pages/manage_menus.css';
$page_js = 'js/pages/manage_menus.js';

require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/views/manage-menus-view.php';
require_once __DIR__ . '/../includes/layout_end.php';
