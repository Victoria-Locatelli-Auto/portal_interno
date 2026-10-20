<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/helpers/systems.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$first_name = Session::get('first_name') ?? 'Usuário';
$custom_menus = systemsSidebarItems($conn);
$systems = systemsList($conn);
$page_title = 'Sistemas da Empresa';
$page_css = 'css/pages/sistemas.css';
$page_js = 'js/pages/sistemas.js';

require __DIR__ . '/../includes/layout_start.php';
require __DIR__ . '/../includes/views/systems-view.php';
require __DIR__ . '/../includes/layout_end.php';
