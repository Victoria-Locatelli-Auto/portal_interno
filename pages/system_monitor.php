<?php
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers/system_monitor.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$first_name = Session::get('first_name') ?? 'Usuário';
$current_user = systemMonitorCurrentUser($conn, (int)Session::getUserId());

if (!systemMonitorCanAccess($current_user)) {
    http_response_code(403);
    exit('Acesso negado. Somente administradores podem acessar este monitor.');
}

$custom_menus = systemMonitorSidebarItems($conn);
$default_status = systemMonitorDefaultDatabaseStatus();
$auth_status = systemMonitorAuthDatabaseStatus();
$portal_errors = systemMonitorFilteredLogLines('[PortalMCM][DB] Service unavailable:');
$base_errors = systemMonitorFilteredLogLines('[BaseMCM][AUTH] Service unavailable:');
$page_title = 'Monitor de Sistema';
$page_css = 'css/pages/system_monitor.css';

require_once __DIR__ . '/../includes/layout_start.php';
require_once __DIR__ . '/../includes/views/system-monitor-view.php';
require_once __DIR__ . '/../includes/layout_end.php';
