<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/helpers/manage_banners.php';

Session::start();
if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$user_id = (int)Session::getUserId();
$first_name = Session::get('first_name') ?? 'Usuário';
$current_user = manageBannersCurrentUser($conn, $user_id);
if (!$current_user || ($current_user['access_level'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Acesso negado. Apenas administradores podem gerenciar banners.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback = manageBannersHandleAction($conn, $_POST, $_FILES, $user_id, dirname(__DIR__));
    Session::set('manage_banners_feedback', $feedback);
    unset($_SESSION['manage_banners_csrf']);
    header('Location: manage_banners.php');
    exit;
}

$feedback = Session::get('manage_banners_feedback', ['type' => '', 'message' => '']);
Session::remove('manage_banners_feedback');
$manage_banners_csrf = manageBannersCsrfToken();
$custom_menus = manageBannersSidebarItems($conn);
$banners = manageBannersList($conn);
$banner_stats = manageBannersStats($banners);
$page_title = 'Gerenciar Banners';
$page_css = 'css/pages/manage_banners.css';
$page_js = 'js/pages/manage_banners.js';

require __DIR__ . '/../includes/layout_start.php';
require __DIR__ . '/../includes/views/manage-banners-view.php';
require __DIR__ . '/../includes/layout_end.php';
