<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/helpers/contacts.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$first_name = Session::get('first_name') ?? 'Usuário';
$page_title = 'Contatos';
$page_css = 'css/pages/contatos.css';

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

$contacts_data = contactsPageData($conn, $_GET);

$search = $contacts_data['search'];
$branch_filter = $contacts_data['branch_filter'];
$job_filter = $contacts_data['job_filter'];
$current_page = $contacts_data['current_page'];
$items_per_page = $contacts_data['items_per_page'];
$total_items = $contacts_data['total_items'];
$total_pages = $contacts_data['total_pages'];
$contacts = $contacts_data['contacts'];
$branches = $contacts_data['branches'];
$jobs = $contacts_data['jobs'];

require __DIR__ . '/../includes/layout_start.php';
require __DIR__ . '/../includes/views/contatos-view.php';
require __DIR__ . '/../includes/layout_end.php';
