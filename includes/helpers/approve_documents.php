<?php

function approveDocumentsUserLevel(mysqli $conn, int $user_id): string
{
    $stmt = $conn->prepare('SELECT access_level FROM users WHERE id = ? LIMIT 1');

    if (!$stmt) {
        return '';
    }

    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

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

function approveDocumentsHandleAction(Document $document_model, array $post, string $user_level): array
{
    if (!approveDocumentsCanManage($user_level)) {
        return [
            'message' => 'Você não tem permissão para alterar documentos.',
            'type' => 'error',
        ];
    }

    $action = (string)($post['action'] ?? '');
    $document_id = (int)($post['document_id'] ?? 0);

    if ($document_id <= 0) {
        return ['message' => '', 'type' => ''];
    }

    if ($action === 'approve') {
        $success = $document_model->approve($document_id);

        return [
            'message' => $success ? 'Documento aprovado para processamento.' : 'Não foi possível aprovar o documento.',
            'type' => $success ? 'success' : 'error',
        ];
    }

    if ($action === 'reject') {
        $success = $document_model->reject($document_id);

        return [
            'message' => $success ? 'Documento rejeitado.' : 'Não foi possível rejeitar o documento.',
            'type' => 'error',
        ];
    }

    return ['message' => '', 'type' => ''];
}

function approveDocumentsFormatTitle(string $filename): string
{
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $name = str_replace(['_', '-', '.'], ' ', $name);
    $name = preg_replace('/\s+/', ' ', trim($name));
    $name = mb_strtolower((string)$name, 'UTF-8');
    $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');

    $lower_words = ['De', 'Da', 'Do', 'Das', 'Dos', 'E', 'Em', 'Para', 'Por', 'Com'];

    foreach ($lower_words as $word) {
        $name = preg_replace('/\b' . $word . '\b/u', mb_strtolower($word, 'UTF-8'), $name);
    }

    return preg_replace('/\bMcm\b/u', 'MCM', (string)$name);
}

function approveDocumentsIsPdf(array $document): bool
{
    return stripos((string)($document['original_name'] ?? ''), '.pdf') !== false;
}

function approveDocumentsFormatDate(?string $date): string
{
    if (empty($date)) {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('d/m/Y H:i', $timestamp) : $date;
}
