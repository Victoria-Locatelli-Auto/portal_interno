<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/helpers/manage_notices.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$first_name = Session::get('first_name') ?? 'Usuário';
$user = manageNoticesCurrentUser($conn, (int)Session::getUserId());

if (!manageNoticesCanAccess($user)) {
    http_response_code(403);
    exit('Acesso negado. Apenas administradores, editores e RH podem acessar esta página.');
}

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = manageNoticesHandleAction($conn, $_POST);
    $success_msg = $result['success'];
    $error_msg = $result['error'];
}

$custom_menus = manageNoticesSidebarItems($conn);
$notices_list = manageNoticesList($conn);
$page_title = 'Gerenciar Avisos';
$page_css = 'css/pages/manage_notices.css';
$page_js = 'js/pages/manage_notices.js';

require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/views/manage-notices-view.php';
require_once __DIR__ . '/../includes/layout_end.php';
