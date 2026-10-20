<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/Document.php';
require_once __DIR__ . '/../includes/helpers/approve_documents.php';

Session::start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

function previewDocumentResponse(bool $success, string $message, int $status = 200, array $extra = []): void
{
    http_response_code($status);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if (!Session::isAuthenticated()) {
    previewDocumentResponse(false, 'Não autenticado.', 401);
}

$user_level = approveDocumentsUserLevel($conn, (int)Session::getUserId());
if (!approveDocumentsCanAccess($user_level)) {
    previewDocumentResponse(false, 'Sem permissão para visualizar este documento.', 403);
}

$document_id = (int)($_GET['id'] ?? 0);
if ($document_id <= 0) {
    previewDocumentResponse(false, 'Documento inválido.', 400);
}

$stmt = $conn->prepare('SELECT id, original_name, filename, filepath, extracted_text FROM documents WHERE id = ? LIMIT 1');
if (!$stmt) {
    previewDocumentResponse(false, 'Não foi possível preparar a visualização.', 500);
}
$stmt->bind_param('i', $document_id);
$stmt->execute();
$document = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$document) {
    previewDocumentResponse(false, 'Documento não encontrado.', 404);
}

$content = (string)($document['extracted_text'] ?? '');
$is_pdf = strtolower(pathinfo((string)$document['original_name'], PATHINFO_EXTENSION)) === 'pdf';
if ($content === '') {
    $filepath = appResolveDocumentPath((string)($document['filepath'] ?? ''), (string)($document['filename'] ?? ''));
    if ($is_pdf) {
        $content = $filepath !== null
            ? '[PDF ainda não processado. Use a ação "Processar PDFs agora" para extrair o texto.]'
            : '[Arquivo PDF não encontrado no servidor.]';
    } elseif ($filepath !== null && is_readable($filepath)) {
        $content = (string)file_get_contents($filepath);
    } else {
        $content = '[Arquivo sem conteúdo disponível para visualização.]';
    }
}

if (strlen($content) > 51200) {
    $total_kb = round(strlen($content) / 1024, 2);
    $content = mb_strcut($content, 0, 51200, 'UTF-8') . "\n\n[Visualização limitada. Tamanho total: {$total_kb} KB]";
}

previewDocumentResponse(true, '', 200, [
    'content' => $content !== '' ? $content : '[Arquivo sem conteúdo.]',
    'filename' => (string)$document['original_name'],
]);
