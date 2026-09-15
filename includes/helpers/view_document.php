<?php

function viewDocumentFind(mysqli $conn, int $doc_id): ?array
{
    $document = new Document($conn);
    $data = $document->getById($doc_id);

    return $data ?: null;
}

function viewDocumentFormatTitle(string $filename): string
{
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $name = str_replace(['_', '-', '.'], ' ', $name);
    $name = preg_replace('/\s+/', ' ', trim($name));

    if (function_exists('mb_strtolower') && function_exists('mb_convert_case')) {
        $name = mb_strtolower($name, 'UTF-8');
        $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
    } else {
        $name = ucwords(strtolower($name));
    }

    $name = preg_replace('/\bMcm\b/u', 'MCM', $name);
    $lower_words = ['De', 'Da', 'Do', 'Das', 'Dos', 'E', 'Em', 'Para', 'Por', 'Com', 'Ao', 'A'];

    foreach ($lower_words as $word) {
        $name = preg_replace('/\b' . $word . '\b/u', strtolower($word), $name);
    }

    return preg_replace('/\bMcm\b/u', 'MCM', $name);
}

function viewDocumentCurrentUser(mysqli $conn, int $user_id): ?array
{
    $stmt = $conn->prepare('SELECT access_level FROM users WHERE id = ? LIMIT 1');

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    return $user ?: null;
}

function viewDocumentIsAdmin(?array $user): bool
{
    return ($user['access_level'] ?? '') === 'admin';
}

function viewDocumentDeleteCsrf(bool $is_admin): string
{
    if (!$is_admin) {
        return '';
    }

    if (empty($_SESSION['delete_document_csrf'])) {
        $_SESSION['delete_document_csrf'] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION['delete_document_csrf'];
}

function viewDocumentSidebarItems(mysqli $conn): array
{
    $result = $conn->query("
        SELECT id, title, url, icon, menu_type
        FROM custom_menus
        WHERE is_active = TRUE
        ORDER BY order_position ASC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function viewDocumentState(array $document): array
{
    $status = (string)($document['status'] ?? '');
    $is_pdf = strpos((string)($document['filename'] ?? ''), '.pdf') !== false;
    $has_content = !empty($document['extracted_text']);
    $resolved_path = appResolveDocumentPath($document['filepath'] ?? '', $document['filename'] ?? '');
    $file_available = $resolved_path !== null;
    $is_processing = in_array($status, ['processando', 'processing'], true);
    $is_error = in_array($status, ['erro', 'error'], true);

    return [
        'is_pdf' => $is_pdf,
        'has_content' => $has_content,
        'file_available' => $file_available,
        'should_show_pdf' => $is_pdf && $file_available,
        'should_show_content' => $has_content && (!$is_pdf || !$file_available) && !$is_processing && !$is_error,
        'is_processing' => $is_processing,
        'is_error' => $is_error,
    ];
}

function viewDocumentStatusLabel(string $status): string
{
    $labels = [
        'processed' => 'Processado',
        'approved' => 'Aprovado',
        'concluido' => 'Concluído',
        'processing' => 'Processando',
        'processando' => 'Processando',
        'error' => 'Erro',
        'erro' => 'Erro',
        'pendente' => 'Pendente',
    ];

    return $labels[$status] ?? ucfirst($status);
}

function viewDocumentDate(?string $date): string
{
    if (!$date) {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('d/m/Y H:i', $timestamp) : '-';
}
