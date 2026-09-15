<?php
/** Exclui exatamente um documento concluído. Acesso exclusivo a administrador ativo. */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';

Session::start();
header('Content-Type: application/json; charset=utf-8');

function deleteDocumentResponse(int $status, string $message, bool $success = false): void
{
    http_response_code($status);
    echo json_encode(['success' => $success, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!Session::isAuthenticated()) {
    deleteDocumentResponse(401, 'Não autorizado.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    deleteDocumentResponse(405, 'Método não permitido.');
}

// Rejeita arrays, listas, zero e qualquer formato que não represente um único ID positivo.
$documentIdInput = $_POST['document_id'] ?? null;
if (!is_string($documentIdInput) || !preg_match('/^[1-9][0-9]*$/', $documentIdInput)) {
    deleteDocumentResponse(422, 'Informe exatamente um ID de documento válido.');
}
$documentId = (int) $documentIdInput;

$csrfToken = $_POST['csrf_token'] ?? null;
$sessionToken = $_SESSION['delete_document_csrf'] ?? null;
if (!is_string($csrfToken) || !is_string($sessionToken) || !hash_equals($sessionToken, $csrfToken)) {
    deleteDocumentResponse(403, 'Confirmação de segurança inválida. Atualize a página e tente novamente.');
}

$userId = (int) Session::getUserId();
$userStmt = $conn->prepare('SELECT username, access_level FROM users WHERE id = ? LIMIT 1');
$userStmt->bind_param('i', $userId);
$userStmt->execute();
$currentUser = $userStmt->get_result()->fetch_assoc();
if (!$currentUser || $currentUser['access_level'] !== 'admin') {
    deleteDocumentResponse(403, 'Somente administradores podem excluir documentos.');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$stagedPath = null;
$originalPath = null;

try {
    $conn->begin_transaction();

    // Confere o status no momento da exclusão e bloqueia somente o registro selecionado.
    $documentStmt = $conn->prepare(
        "SELECT id, filename, filepath, original_name FROM documents
         WHERE id = ? AND status = 'concluido' LIMIT 1 FOR UPDATE"
    );
    $documentStmt->bind_param('i', $documentId);
    $documentStmt->execute();
    $document = $documentStmt->get_result()->fetch_assoc();
    if (!$document) {
        $conn->rollback();
        deleteDocumentResponse(409, 'Documento não encontrado ou não está com status concluído.');
    }

    // Renomear primeiro torna o arquivo recuperável se a transação falhar.
    $originalPath = appResolveDocumentPath($document['filepath'] ?? '', $document['filename'] ?? '');
    if ($originalPath !== null) {
        $stagedPath = $originalPath . '.deleting-' . bin2hex(random_bytes(8));
        if (!rename($originalPath, $stagedPath)) {
            throw new RuntimeException('Não foi possível preparar o arquivo físico para exclusão.');
        }
    }

    $menuStmt = $conn->prepare('DELETE FROM document_menus WHERE document_id = ?');
    $menuStmt->bind_param('i', $documentId);
    $menuStmt->execute();

    $deleteStmt = $conn->prepare("DELETE FROM documents WHERE id = ? AND status = 'concluido' LIMIT 1");
    $deleteStmt->bind_param('i', $documentId);
    $deleteStmt->execute();
    if ($deleteStmt->affected_rows !== 1) {
        throw new RuntimeException('A exclusão individual do registro não foi confirmada.');
    }

    $conn->commit();

    if ($stagedPath !== null && is_file($stagedPath) && !unlink($stagedPath)) {
        error_log(sprintf(
            '[AUDITORIA_DOCUMENTO] admin_id=%d usuario=%s documento_id=%d resultado=banco_excluido_arquivo_pendente caminho=%s',
            $userId, $currentUser['username'], $documentId, $stagedPath
        ));
        deleteDocumentResponse(500, 'O registro foi excluído, mas o arquivo físico precisa de limpeza administrativa.');
    }

    unset($_SESSION['delete_document_csrf']);
    error_log(sprintf(
        '[AUDITORIA_DOCUMENTO] admin_id=%d usuario=%s documento_id=%d nome=%s resultado=excluido',
        $userId,
        $currentUser['username'],
        $documentId,
        str_replace(["\r", "\n"], ' ', (string) $document['original_name'])
    ));
    deleteDocumentResponse(200, 'Documento ID ' . $documentId . ' excluído com sucesso.', true);
} catch (Throwable $error) {
    try {
        $conn->rollback();
    } catch (Throwable $ignored) {
    }

    if ($stagedPath !== null && $originalPath !== null && is_file($stagedPath) && !is_file($originalPath)) {
        rename($stagedPath, $originalPath);
    }

    error_log(sprintf(
        '[AUDITORIA_DOCUMENTO] admin_id=%d usuario=%s documento_id=%d resultado=erro detalhe=%s',
        $userId,
        $currentUser['username'],
        $documentId,
        str_replace(["\r", "\n"], ' ', $error->getMessage())
    ));
    deleteDocumentResponse(500, 'Erro ao excluir o documento. Nenhuma exclusão foi confirmada.');
}
