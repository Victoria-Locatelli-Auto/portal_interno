<?php
require_once __DIR__ . '/includes/Session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers/admin.php';

Session::start();
if (!Session::isAuthenticated()) {
    header('Location: login.php');
    exit;
}

$base_url = appBaseUrl();
$user_id = (int)Session::getUserId();
$current_user = adminCurrentUser($conn, $user_id);
if (!$current_user || ($current_user['access_level'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Acesso negado. Apenas administradores podem acessar esta página.');
}

$first_name = Session::get('first_name') ?? 'Usuário';
$logged_username = (string)($current_user['username'] ?? Session::getUsername());
$filters = adminFilters($_GET);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback = adminHandleAction($conn, $_POST, $user_id);
    Session::set('admin_feedback', $feedback);
    unset($_SESSION['admin_csrf']);
    $redirect = adminBuildQuery($filters);
    header('Location: admin.php' . ($redirect !== '' ? '?' . $redirect : ''));
    exit;
}

$feedback = Session::get('admin_feedback', ['success' => '', 'error' => '']);
Session::remove('admin_feedback');
$admin_csrf = adminCsrfToken();
$custom_menus = adminSidebarItems($conn);
$users_data = adminUsersData($conn, $filters);
$users = $users_data['users'];
$total_users = $users_data['total'];
$total_pages = $users_data['pages'];
$page = $users_data['page'];
$filters['page'] = $page;
$admin_stats = adminStats($conn);

$page_title = 'Gerenciar Usuários';
$page_css = 'css/pages/admin.css';
$page_js = 'js/pages/admin.js';

require __DIR__ . '/includes/layout_start.php';
require __DIR__ . '/includes/views/admin-view.php';
require __DIR__ . '/includes/layout_end.php';
