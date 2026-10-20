<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/helpers/profile.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$user_id = (int)Session::getUserId();
$first_name = Session::get('first_name') ?? 'Usuário';
$custom_menus = profileSidebarItems($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['profile_photo_feedback'] = profileHandlePhotoRequest(
        $conn,
        $user_id,
        $_POST,
        $_FILES,
        dirname(__DIR__)
    );
    unset($_SESSION['profile_photo_csrf']);

    header('Location: ' . $base_url . '/pages/perfil.php');
    exit;
}

$user = profileFindUser($conn, $user_id);

if (!$user) {
    http_response_code(404);
    exit('Usuário não encontrado.');
}

$full_name = formatProfileName(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
$funcao_descricao = formatProfileText($user['descricao'] ?? null);
$filial_nome = formatProfileText($user['nome_filial'] ?? null);
$whatsapp = formatProfileWhatsApp($user['wpp_corporativo'] ?? null);
$registered_at = formatProfileDate($user['created_at'] ?? null);
$profile_photo_url = profilePhotoUrl($base_url, $user['profile_photo'] ?? null);
$profile_photo_csrf = profilePhotoCsrfToken();
$profile_photo_feedback = profilePhotoFeedback();

$page_title = 'Meu Perfil';
$page_css = 'css/pages/perfil.css';
$page_js = 'js/pages/perfil.js';

require __DIR__ . '/../includes/layout_start.php';
require __DIR__ . '/../includes/views/perfil-view.php';
require __DIR__ . '/../includes/layout_end.php';
