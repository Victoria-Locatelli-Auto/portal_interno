<?php

function approveDocumentsUserLevel(mysqli $conn, int $user_id): string
{
    $stmt = $conn->prepare('SELECT access_level FROM users WHERE id = ? LIMIT 1');
    if (!$stmt) return '';
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (string)($user['access_level'] ?? '');
}

function approveDocumentsCanAccess(string $user_level): bool
{
    return in_array($user_level, ['admin', 'editor'], true);
}

function approveDocumentsCanManage(string $user_level): bool
{
    return $user_level === 'admin';
}

function approveDocumentsCsrfToken(): string
{
    if (empty($_SESSION['approve_documents_csrf'])) {
        $_SESSION['approve_documents_csrf'] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION['approve_documents_csrf'];
}

function approveDocumentsValidCsrf(array $post): bool
{
    $submitted = (string)($post['csrf_token'] ?? '');
    $stored = (string)($_SESSION['approve_documents_csrf'] ?? '');
    return $stored !== '' && hash_equals($stored, $submitted);
}

function approveDocumentsHandleAction(Document $document_model, array $post, string $user_level): array
{
    if (!approveDocumentsValidCsrf($post)) {
        return approveDocumentsResult('A sessão do formulário expirou. Atualize a página e tente novamente.', 'error');
    }
    if (!approveDocumentsCanManage($user_level)) {
        return approveDocumentsResult('Você não tem permissão para alterar documentos.', 'error');
    }

    $action = (string)($post['action'] ?? '');
    $document_id = (int)($post['document_id'] ?? 0);
    if ($document_id <= 0 || !in_array($action, ['approve', 'reject'], true)) {
        return approveDocumentsResult('Ação ou documento inválido.', 'error');
    }

    $document = $document_model->getById($document_id);
    if (!$document || ($document['status'] ?? '') !== 'pendente') {
        return approveDocumentsResult('O documento não está mais pendente de aprovação.', 'error');
    }

    $success = $action === 'approve'
        ? $document_model->approve($document_id)
        : $document_model->reject($document_id);

    if (!$success) {
        return approveDocumentsResult('Não foi possível atualizar o documento.', 'error');
    }

    return $action === 'approve'
        ? approveDocumentsResult('Documento aprovado com sucesso.', 'success')
        : approveDocumentsResult('Documento rejeitado com sucesso.', 'success');
}

function approveDocumentsStats(array $documents): array
{
    $stats = ['total' => count($documents), 'pdf' => 0, 'text' => 0];
    foreach ($documents as $document) {
        $stats[approveDocumentsIsPdf($document) ? 'pdf' : 'text']++;
    }
    return $stats;
}

function approveDocumentsFormatTitle(string $filename): string
{
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $name = preg_replace('/\s+/', ' ', str_replace(['_', '-', '.'], ' ', trim($name)));
    $name = mb_convert_case(mb_strtolower((string)$name, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    foreach (['De', 'Da', 'Do', 'Das', 'Dos', 'E', 'Em', 'Para', 'Por', 'Com'] as $word) {
        $name = preg_replace('/\b' . $word . '\b/u', mb_strtolower($word, 'UTF-8'), $name);
    }
    return preg_replace('/\bMcm\b/u', 'MCM', (string)$name);
}

function approveDocumentsIsPdf(array $document): bool
{
    return strtolower(pathinfo((string)($document['original_name'] ?? ''), PATHINFO_EXTENSION)) === 'pdf';
}

function approveDocumentsFormatDate(?string $date): string
{
    if (empty($date)) return '-';
    $timestamp = strtotime($date);
    return $timestamp ? date('d/m/Y H:i', $timestamp) : $date;
}

function approveDocumentsResult(string $message, string $type): array
{
    return ['message' => $message, 'type' => $type];
}
