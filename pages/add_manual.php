<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/helpers/add_manual.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$first_name = Session::get('first_name') ?? 'Usuário';
$page_title = 'Adicionar Documento Manual';
$page_css = 'css/pages/add_manual.css';
$page_js = 'js/pages/add_manual.js';

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

$form_data = addManualDefaultFormData();
$feedback = [
    'error' => '',
    'success' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = addManualHandleSubmit($conn, (int) Session::getUserId(), $_POST);
    $form_data = $result['form_data'];
    $feedback['error'] = $result['error'];
    $feedback['success'] = $result['success'];
}

require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/views/add-manual-view.php';
require_once __DIR__ . '/../includes/layout_end.php';
