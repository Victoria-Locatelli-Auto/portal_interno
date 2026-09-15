<?php

require_once __DIR__ . '/../includes/Session.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/training.php';
require_once __DIR__ . '/../includes/helpers/jwt.php';

$user_id = (int) Session::getUserId();

$stmt = $conn->prepare("
    SELECT
        u.id,
        u.first_name,
        u.last_name,
        f.descricao AS cargo,
        fi.nome_filial AS filial
    FROM users u
    LEFT JOIN funcao f
        ON f.id = u.id_funcao
    LEFT JOIN filiais fi
        ON fi.id = u.id_filial
    WHERE u.id = ?
    LIMIT 1
");

if (!$stmt) {
    http_response_code(500);
    exit('Erro ao preparar acesso aos treinamentos.');
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    http_response_code(404);
    exit('Usuario nao encontrado.');
}

if (TRAINING_JWT_SECRET === 'troque-esta-chave-secreta') {
    http_response_code(500);
    exit('Configure TRAINING_JWT_SECRET em config/training.php antes de usar os treinamentos.');
}

$now = time();
$full_name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

$payload = [
    'iss' => defined('APP_URL') ? APP_URL : 'Portal MCM',
    'aud' => TRAINING_JWT_AUDIENCE,
    'iat' => $now,
    'exp' => $now + TRAINING_JWT_TTL,
    'sub' => (string) $user['id'],
    'id_funcionario' => (int) $user['id'],
    'nome' => $full_name,
    'cargo' => $user['cargo'] ?? '',
    'filial' => $user['filial'] ?? '',
];

$token = jwtCreateHs256($payload, TRAINING_JWT_SECRET);
$separator = strpos(TRAINING_URL, '?') === false ? '?' : '&';

header('Location: ' . TRAINING_URL . $separator . http_build_query(['token' => $token]));
exit;
