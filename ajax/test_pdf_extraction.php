<?php
/**
 * API para testar extração de PDF
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/PDFParser.php';

Session::start();

if (!Session::isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$filepath = isset($_POST['filepath']) ? $_POST['filepath'] : '';

if (!$id || !$filepath) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

// Validar permissão do usuário
$user_id = Session::getUserId();
$stmt = $conn->prepare("SELECT id, user_id FROM documents WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$document = $result->fetch_assoc();
$stmt->close();

if (!$document || $document['user_id'] != $user_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

try {
    // Testar extração
    $text = SimplePDFParser::extractText($filepath);
    
    if (empty($text)) {
        echo json_encode([
            'success' => false,
            'message' => 'Nenhum texto foi extraído do PDF'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'text' => $text,
            'length' => strlen($text),
            'words' => count(explode(' ', $text))
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar: ' . $e->getMessage()
    ]);
}
