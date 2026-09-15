<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/Document.php';
require_once __DIR__ . '/../includes/helpers/view_document.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$first_name = Session::get('first_name') ?? 'Usuário';
$doc_id = (int)($_GET['id'] ?? 0);

if ($doc_id <= 0) {
    header('Location: documentos.php');
    exit;
}

$document = viewDocumentFind($conn, $doc_id);

if (!$document) {
    header('Location: documentos.php');
    exit;
}

$current_user = viewDocumentCurrentUser($conn, (int)Session::getUserId());
$is_admin = viewDocumentIsAdmin($current_user);
$delete_document_csrf = viewDocumentDeleteCsrf($is_admin);
$custom_menus = viewDocumentSidebarItems($conn);
$document_state = viewDocumentState($document);
$status_text = viewDocumentStatusLabel($document['status'] ?? '');
$formatted_title = viewDocumentFormatTitle($document['original_name'] ?? '');
$page_title = $formatted_title;
$page_css = 'css/pages/view_document.css';
$page_js = 'js/pages/view_document.js';

require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/views/view-document-view.php';
require_once __DIR__ . '/../includes/layout_end.php';
