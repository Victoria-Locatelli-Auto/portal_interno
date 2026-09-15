<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';

Session::start();

header('Content-Type: application/json; charset=utf-8');

if (!Session::isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (Session::getAccessLevel() !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado'], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "
    SELECT
        s.id,
        s.sugestao,
        s.data_envio,
        u.first_name,
        u.last_name,
        u.username
    FROM sugestoes_portal s
    JOIN users u ON u.id = s.usuario_id
    ORDER BY s.data_envio DESC
";

$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao carregar sugestões.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$sugestoes = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($sugestoes, JSON_UNESCAPED_UNICODE);
