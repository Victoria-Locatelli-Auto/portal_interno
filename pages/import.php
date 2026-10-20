<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/Document.php';
require_once __DIR__ . '/../includes/helpers/import.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$user_id = (int) Session::getUserId();
$user = importCurrentUser($conn, $user_id);

if (!$user) {
    header('Location: ../login.php');
    exit;
}

if (empty($user['email_verified'])) {
    header('Location: ../verify-email.php');
    exit;
}

if (!importCanAccess((string)$user['access_level'])) {
    header('Location: ../index.php');
    exit;
}

$first_name = Session::get('first_name') ?? 'Usuário';
$page_title = 'Importar Documentos';
$page_css = 'css/pages/import.css';
$page_js = 'js/pages/import.js';
$error = '';
$success = '';
$import_max_file_size = importMaxFileSize();
$import_max_file_size_mb = max(1, (int)floor($import_max_file_size / 1024 / 1024));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = importHandleRequest($conn, $user_id, $_POST, $_FILES);
    unset($_SESSION['import_csrf']);

    if (!empty($result['redirect'])) {
        header('Location: ' . $result['redirect']);
        exit;
    }

    $error = $result['error'];
    $success = $result['success'];
}

$menus = importMenus($conn);
$custom_menus = importSidebarItems($conn);
$import_csrf = importCsrfToken();

require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/views/import-view.php';
require_once __DIR__ . '/../includes/layout_end.php';
