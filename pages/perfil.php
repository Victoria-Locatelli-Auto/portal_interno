<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/helpers/profile.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$base_url = appBaseUrl();
$user_id = Session::getUserId();
$first_name = Session::get('first_name') ?? 'Usuário';

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

$stmt = $conn->prepare("
    SELECT
        usr.id,
        usr.first_name,
        usr.last_name,
        usr.email,
        usr.access_level,
        usr.created_at,
        usr.is_active,
        usr.email_verified,
        usr.id_funcao,
        funcao.descricao,
        usr.wpp_corporativo,
        usr.id_filial,
        fi.nome_filial
        
    FROM users usr
    LEFT JOIN funcao ON funcao.id = usr.id_funcao
    LEFT JOIN filiais fi ON fi.id = usr.id_filial
    WHERE usr.id = ?
");



if (!$stmt) {
    die('Erro SQL: ' . $conn->error);
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die('Usuário não encontrado');
}

$full_name = formatProfileName(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
$funcao_descricao = formatProfileRole($user['descricao'] ?? '');

$page_title = 'Meu Perfil';
$page_css = 'css/pages/perfil.css';

require __DIR__ . '/../includes/layout_start.php';
require __DIR__ . '/../includes/views/perfil-view.php';
require __DIR__ . '/../includes/layout_end.php';
