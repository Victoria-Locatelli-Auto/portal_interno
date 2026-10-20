<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/helpers/manage_systems.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$user_id = (int)Session::getUserId();
$first_name = Session::get('first_name') ?? 'Usuário';
$current_user = manageSystemsCurrentUser($conn, $user_id);

if (!$current_user || ($current_user['access_level'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Acesso negado. Apenas administradores podem gerenciar sistemas.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback = manageSystemsHandleAction($conn, $_POST, $_FILES, $user_id, dirname(__DIR__));
    Session::set('manage_systems_feedback', $feedback);
    unset($_SESSION['manage_systems_csrf']);
    header('Location: manage_systems.php');
    exit;
}

$feedback = Session::get('manage_systems_feedback', ['type' => '', 'message' => '']);
Session::remove('manage_systems_feedback');
$manage_systems_csrf = manageSystemsCsrfToken();
$custom_menus = manageSystemsSidebarItems($conn);
$systems = manageSystemsList($conn);
$system_stats = manageSystemsStats($systems);
$page_title = 'Gerenciar Sistemas';
$page_css = 'css/pages/manage_systems.css';
$page_js = 'js/pages/manage_systems.js';

require __DIR__ . '/../includes/layout_start.php';
require __DIR__ . '/../includes/views/manage-systems-view.php';
require __DIR__ . '/../includes/layout_end.php';
