<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/Document.php';
require_once __DIR__ . '/../includes/helpers/approve_documents.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$first_name = Session::get('first_name') ?? 'Usuário';
$user_id = (int) Session::getUserId();
$user_level = approveDocumentsUserLevel($conn, $user_id);

if (!approveDocumentsCanAccess($user_level)) {
    header('Location: ../index.php');
    exit;
}

$page_title = 'Aprovar Documentos';
$page_css = 'css/pages/approve_documents.css';
$page_js = 'js/pages/approve_documents.js';

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

$message = Session::get('process_message', '');
$message_type = $message !== '' ? 'success' : '';

if ($message !== '') {
    Session::remove('process_message');
}

$document_model = new Document($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_result = approveDocumentsHandleAction($document_model, $_POST, $user_level);

    if ($action_result['message'] !== '') {
        $message = $action_result['message'];
        $message_type = $action_result['type'];
    }
}

$pending_documents = $document_model->listPending();

require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/views/approve-documents-view.php';
require_once __DIR__ . '/../includes/layout_end.php';
