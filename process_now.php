<?php
require_once __DIR__ . '/includes/Session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Document.php';
require_once __DIR__ . '/includes/helpers/approve_documents.php';

Session::start();
if (!Session::isAuthenticated()) {
    http_response_code(401);
    exit('Não autorizado.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método não permitido.');
}

$user_level = approveDocumentsUserLevel($conn, (int)Session::getUserId());
if (!approveDocumentsCanManage($user_level)) {
    http_response_code(403);
    exit('Acesso negado.');
}
if (!approveDocumentsValidCsrf($_POST)) {
    Session::set('approve_documents_feedback', approveDocumentsResult('A sessão do formulário expirou. Atualize a página e tente novamente.', 'error'));
    header('Location: pages/approve_documents.php');
    exit;
}

unset($_SESSION['approve_documents_csrf']);
ob_start();
require __DIR__ . '/process_documents.php';
ob_end_clean();

$is_ajax = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
if ($is_ajax) {
    $log_content = @file_get_contents(appUploadAbsolutePath('process.log')) ?: '';
    preg_match('/Sucesso: (\d+) \| Erros: (\d+)/', $log_content, $matches);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'message' => 'Processamento concluído.',
        'success_count' => (int)($matches[1] ?? 0),
        'error_count' => (int)($matches[2] ?? 0),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

Session::set('approve_documents_feedback', approveDocumentsResult('Processamento concluído. A lista de documentos foi atualizada.', 'success'));
header('Location: pages/approve_documents.php');
exit;
