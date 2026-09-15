<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/Document.php';
require_once __DIR__ . '/../includes/SearchEngine.php';
require_once __DIR__ . '/../includes/AIEngine.php';
require_once __DIR__ . '/../includes/helpers/search.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$user_id = (int)Session::getUserId();
$first_name = Session::get('first_name') ?? 'Usuário';
$search_query = trim((string)($_POST['search_query'] ?? ''));
$ai_answer = [];
$results = [];

if ($search_query !== '') {
    $search_data = searchPageAnswer($conn, $user_id, $search_query);
    $ai_answer = $search_data['ai_answer'];
    $results = $search_data['results'];
}

$custom_menus = searchPageSidebarItems($conn);
$page_title = 'Busca Inteligente';
$page_css = 'css/pages/search.css';
$page_js = 'js/pages/search.js';

require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/views/search-view.php';
require_once __DIR__ . '/../includes/layout_end.php';
