<?php
/**
 * API para testar SearchEngine
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/SearchEngine.php';

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

$query = isset($_POST['query']) ? trim($_POST['query']) : '';

if (empty($query)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Query vazia']);
    exit;
}

try {
    // Executar busca
    $user_id = Session::getUserId();
    $search_engine = new SearchEngine($conn, $user_id);
    $results = $search_engine->search($query);
    
    // Formatar resultados
    $formatted_results = [];
    foreach ($results as $result) {
        $formatted_results[] = [
            'id' => $result['document']['id'],
            'original_name' => $result['document']['original_name'],
            'score' => $result['score'],
            'preview' => $result['preview'],
            'status' => $result['document']['status']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'query' => $query,
        'results' => $formatted_results,
        'total' => count($formatted_results)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro na busca: ' . $e->getMessage()
    ]);
}
