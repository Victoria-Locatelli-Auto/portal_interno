<?php
/**
 * Pagina inicial - Portal MCM.
 */

require_once __DIR__ . '/includes/Session.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Document.php';
require_once __DIR__ . '/includes/PDFParser.php';
require_once __DIR__ . '/includes/SearchEngine.php';
require_once __DIR__ . '/includes/helpers/index.php';

$base_url = appBaseUrl();
$user_id = (int) Session::getUserId();
$first_name = Session::get('first_name') ?? 'Usuario';
$page_title = 'Pagina Inicial';
$page_css = 'css/pages/index.css';
$page_js = 'js/pages/index.js';

$user = [];
$user_level = 'viewer';
$user_stmt = $conn->prepare('SELECT access_level FROM users WHERE id = ? LIMIT 1');

if ($user_stmt) {
    $user_stmt->bind_param('i', $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result ? ($user_result->fetch_assoc() ?: []) : [];
    $user_level = $user['access_level'] ?? 'viewer';
}

$search_query = '';
$search_results = [];
$total_results = 0;
$page = 1;
$items_per_page = 5;
$total_pages = 0;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['q'])) {
    $search_query = trim($_GET['q']);
    $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

    if ($search_query !== '') {
        $search_engine = new SearchEngine($conn, $user_id, true);
        $all_results = $search_engine->search($search_query);

        $total_results = count($all_results);
        $total_pages = (int) ceil($total_results / $items_per_page);

        if ($page > $total_pages && $total_pages > 0) {
            $page = $total_pages;
        }

        $offset = ($page - 1) * $items_per_page;
        $search_results = array_slice($all_results, $offset, $items_per_page);
    }
}

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

$notices = [];
$notices_result = $conn->query("
    SELECT id, title, description, created_at
    FROM notices
    WHERE is_active = TRUE
    ORDER BY created_at DESC
    LIMIT 5
");

if ($notices_result) {
    $notices = $notices_result->fetch_all(MYSQLI_ASSOC);
}

$banners = indexActiveBanners($conn);

require __DIR__ . '/includes/layout_start.php';
require __DIR__ . '/includes/views/index-view.php';
require __DIR__ . '/includes/layout_end.php';
