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

$feedback = Session::get('approve_documents_feedback', ['message' => '', 'type' => '']);
Session::remove('approve_documents_feedback');
$message = (string)($feedback['message'] ?? '');
$message_type = (string)($feedback['type'] ?? '');

$document_model = new Document($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_result = approveDocumentsHandleAction($document_model, $_POST, $user_level);
    Session::set('approve_documents_feedback', $action_result);
    unset($_SESSION['approve_documents_csrf']);
    header('Location: approve_documents.php');
    exit;
}

$pending_documents = $document_model->listPending();
$document_stats = approveDocumentsStats($pending_documents);
$approve_documents_csrf = approveDocumentsCsrfToken();

require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/views/approve-documents-view.php';
require_once __DIR__ . '/../includes/layout_end.php';
