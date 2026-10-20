<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/helpers/institucional.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$first_name = Session::get('first_name') ?? 'Usuário';
$page_title = 'Institucional';
$page_css = 'css/pages/institucional.css';

$custom_menus = institutionalSidebarItems($conn);
$institutional_cards = institutionalCards();
$institutional_history = institutionalHistory();

require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/views/institucional-view.php';
require_once __DIR__ . '/../includes/layout_end.php';
