<?php
/**
 * Processador de Documentos - Chamado via AJAX ou Form
 * Não mostra página HTML, apenas processa e retorna JSON ou redireciona
 */

require_once __DIR__ . '/includes/Session.php';

Session::start();

// Verifica se está autenticado e é admin
if (!Session::isAuthenticated()) {
    http_response_code(401);
    die('Não autorizado');
}

require_once __DIR__ . '/config/database.php';

$user_id = Session::getUserId();
$result = $conn->query("SELECT access_level FROM users WHERE id = $user_id");
$user = $result->fetch_assoc();

if (!$user || $user['access_level'] !== 'admin') {
    http_response_code(403);
    die('Acesso negado');
}

// Inicia processamento
ob_start(); // Captura output

require_once __DIR__ . '/process_documents.php';

$output = ob_get_clean();

// Se é requisição AJAX, retorna JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // Extrai estatísticas do log
    $log_file = appUploadAbsolutePath('process.log');
    $log_content = @file_get_contents($log_file);
    
    preg_match('/Sucesso: (\d+) \| Erros: (\d+)/', $log_content, $matches);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Processamento concluído',
        'success_count' => (int)($matches[1] ?? 0),
        'error_count' => (int)($matches[2] ?? 0)
    ]);
} else {
    // Se é form normal, redireciona com mensagem
    $_SESSION['process_message'] = '✓ Processamento concluído! Atualize a página para ver os resultados.';
    header('Location: pages/approve_documents.php');
}
exit;
?>
