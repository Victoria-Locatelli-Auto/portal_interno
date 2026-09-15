<?php

function menuPageFormatDocumentTitle(string $filename): string
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

function menuPageFileIcon(string $filename): string
{
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if ($extension === 'pdf') {
        return 'fas fa-file-pdf';
    }

    if (in_array($extension, ['txt', 'doc', 'docx'], true)) {
        return 'fas fa-file-word';
    }

    if (in_array($extension, ['xls', 'xlsx'], true)) {
        return 'fas fa-file-excel';
    }

    if (in_array($extension, ['ppt', 'pptx'], true)) {
        return 'fas fa-file-powerpoint';
    }

    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'], true)) {
        return 'fas fa-file-image';
    }

    if (in_array($extension, ['zip', 'rar', '7z'], true)) {
        return 'fas fa-file-archive';
    }

    return 'fas fa-file';
}

function menuPageStatusLabel(string $status): string
{
    $labels = [
        'processed' => 'Processado',
        'processing' => 'Processando',
        'concluido' => 'Concluído',
        'approved' => 'Aprovado',
        'error' => 'Erro',
    ];

    return $labels[$status] ?? ucfirst($status);
}

function menuPageDocumentDate(?string $date): string
{
    if (!$date) {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('d/m/Y H:i', $timestamp) : '-';
}

function menuPageIsTextDocument(array $document): bool
{
    return ($document['filepath'] ?? '') === '[texto direto]'
        || strpos((string)($document['filename'] ?? ''), 'text_') === 0;
}

function menuPageFindMenu(mysqli $conn, int $menu_id): ?array
{
    $stmt = $conn->prepare("
        SELECT id, title, icon
        FROM custom_menus
        WHERE id = ? AND is_active = TRUE AND menu_type = 'page'
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $menu_id);
    $stmt->execute();
    $menu = $stmt->get_result()->fetch_assoc();

    return $menu ?: null;
}

function menuPageSidebarItems(mysqli $conn): array
{
    $result = $conn->query("
        SELECT id, title, url, icon, menu_type
        FROM custom_menus
        WHERE is_active = TRUE
        ORDER BY order_position ASC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function menuPageSubmenus(mysqli $conn, int $menu_id): array
{
    $stmt = $conn->prepare("
        SELECT
            s.id,
            s.title,
            COUNT(DISTINCT dm.document_id) AS doc_count
        FROM custom_submenus s
        LEFT JOIN document_menus dm ON s.id = dm.submenu_id AND dm.menu_id = ?
        WHERE s.menu_id = ? AND s.is_active = TRUE
        GROUP BY s.id, s.title, s.order_position
        ORDER BY s.order_position ASC
    ");

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('ii', $menu_id, $menu_id);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function menuPageDocuments(mysqli $conn, int $menu_id, ?int $submenu_id = null): array
{
    if ($submenu_id !== null) {
        $stmt = $conn->prepare("
            SELECT d.id, d.original_name, d.uploaded_at, d.status, d.filename, d.filepath, d.extracted_text
            FROM documents d
            INNER JOIN document_menus dm ON d.id = dm.document_id
            WHERE dm.menu_id = ? AND dm.submenu_id = ? AND d.status IN ('approved', 'concluido')
            ORDER BY d.uploaded_at DESC
        ");

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('ii', $menu_id, $submenu_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    $stmt = $conn->prepare("
        SELECT d.id, d.original_name, d.uploaded_at, d.status, d.filename, d.filepath, d.extracted_text
        FROM documents d
        INNER JOIN document_menus dm ON d.id = dm.document_id
        WHERE dm.menu_id = ? AND d.status IN ('approved', 'concluido')
        ORDER BY d.uploaded_at DESC
    ");

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('i', $menu_id);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function menuPageDocumentsBySubmenu(mysqli $conn, int $menu_id, array $submenus): array
{
    $documents = [];

    foreach ($submenus as $submenu) {
        $submenu_id = (int)$submenu['id'];
        $documents[$submenu_id] = menuPageDocuments($conn, $menu_id, $submenu_id);
    }

    return $documents;
}
