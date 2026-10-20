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

$custom_menus = editFuncionarioSidebarItems($conn);

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submit_result = editFuncionarioHandleSubmit(
        $conn,
        $_POST,
        $logged_user,
        $logged_user_id,
        $target_user_id
    );
    $success_msg = $submit_result['success'];
    $error_msg = $submit_result['error'];
    unset($_SESSION['edit_funcionario_csrf']);

    if ($success_msg !== '' && $target_user_id === $logged_user_id) {
        Session::set('first_name', trim((string)($_POST['first_name'] ?? '')));
        Session::set('last_name', trim((string)($_POST['last_name'] ?? '')));
        Session::set('email', trim((string)($_POST['email'] ?? Session::getEmail())));
        Session::set('id_funcao', (int)($_POST['id_funcao'] ?? 0));
        Session::set('id_filial', (int)($_POST['id_filial'] ?? 0));
        $first_name = Session::get('first_name') ?? $first_name;

        if (Session::hasCompleteProfile() && Session::has('profile_completion_return_url')) {
            $completion_return_url = (string)Session::get(
                'profile_completion_return_url',
                $base_url . '/index.php'
            );
            Session::remove('profile_completion_return_url');

            header('Location: ' . $completion_return_url);
            exit;
        }
    }
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
$whatsapp = editFuncionarioFormatWhatsApp($user['wpp_corporativo'] ?? null);
$employee_photo_url = editFuncionarioPhotoUrl($base_url, $user['profile_photo'] ?? null);
$edit_funcionario_csrf = editFuncionarioCsrfToken();
$profile_completion_required = $target_user_id === $logged_user_id && !Session::hasCompleteProfile();
$return_url = ($target_user_id === $logged_user_id)
    ? $base_url . '/pages/perfil.php'
    : $base_url . '/admin.php';
$niveis = editFuncionarioAccessLevels();
$page_title = 'Editar Funcionário';
$page_css = 'css/pages/editar_funcionario.css';
$page_js = 'js/pages/editar_funcionario.js';

require __DIR__ . '/../includes/layout_start.php';
require __DIR__ . '/../includes/views/edit-func-view.php';
require __DIR__ . '/../includes/layout_end.php';
