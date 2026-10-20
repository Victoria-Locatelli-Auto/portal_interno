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
$notice_form = [
    'title' => '',
    'description' => '',
    'content' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = manageNoticesHandleAction($conn, $_POST);
    $success_msg = $result['success'];
    $error_msg = $result['error'];
    unset($_SESSION['manage_notices_csrf']);

    if ($error_msg !== '' && ($_POST['action'] ?? '') === 'add_notice') {
        $notice_form = [
            'title' => trim((string)($_POST['title'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'content' => trim((string)($_POST['content'] ?? '')),
        ];
    }
}

$custom_menus = manageNoticesSidebarItems($conn);
$notices_list = manageNoticesList($conn);
$notice_stats = manageNoticesStats($notices_list);
$manage_notices_csrf = manageNoticesCsrfToken();
$page_title = 'Gerenciar Avisos';
$page_css = 'css/pages/manage_notices.css';
$page_js = 'js/pages/manage_notices.js';

require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/views/manage-notices-view.php';
require_once __DIR__ . '/../includes/layout_end.php';
