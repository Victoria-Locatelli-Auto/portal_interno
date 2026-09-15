<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/helpers/edit_funcionario.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$logged_user_id = (int) Session::getUserId();
$first_name = Session::get('first_name') ?? 'Usuário';
$logged_user = editFuncionarioLoggedUser($conn, $logged_user_id);

if (!$logged_user) {
    exit('Usuário logado não encontrado.');
}

$is_admin = editFuncionarioIsAdmin($logged_user);
$target_user_id = (int)($_GET['id'] ?? 0);

if ($target_user_id <= 0 || !editFuncionarioCanEdit($logged_user, $logged_user_id, $target_user_id)) {
    header('Location: ' . $base_url . '/pages/perfil.php');
    exit;
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

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submit_result = editFuncionarioHandleSubmit($conn, $_POST, $logged_user, $logged_user_id);
    $success_msg = $submit_result['success'];
    $error_msg = $submit_result['error'];
}

$funcoes = editFuncionarioFuncoes($conn);
$filiais = editFuncionarioFiliais($conn);
$user = editFuncionarioFindUser($conn, $target_user_id);

if (!$user) {
    header('Location: ' . $base_url . '/admin.php');
    exit;
}

$full_name = editFuncionarioFormatName(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
$cargo = editFuncionarioFormatCargo($user['job_title'] ?? '');
$filial_nome = editFuncionarioFormatFilial($user['nome_filial'] ?? '');
$niveis = editFuncionarioAccessLevels();
$page_title = 'Editar Funcionário';
$page_css = 'css/pages/editar_funcionario.css';

require __DIR__ . '/../includes/layout_start.php';
require __DIR__ . '/../includes/views/edit-func-view.php';
require __DIR__ . '/../includes/layout_end.php';
