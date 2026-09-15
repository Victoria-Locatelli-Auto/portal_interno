<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/Document.php';
require_once __DIR__ . '/../includes/SearchEngine.php';
require_once __DIR__ . '/../includes/helpers/meus_documentos.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$user_id = (int)Session::getUserId();
$first_name = Session::get('first_name') ?? 'Usuário';
$search_query = trim((string)($_POST['search_query'] ?? ''));
$results = meusDocumentosResults($conn, $user_id, $search_query);
$stats = meusDocumentosStats($conn, $user_id);
$custom_menus = meusDocumentosSidebarItems($conn);
$page_title = 'Meus Documentos';
$page_css = 'css/pages/meus_documentos.css';

require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/views/meus-documentos-view.php';
require_once __DIR__ . '/../includes/layout_end.php';
