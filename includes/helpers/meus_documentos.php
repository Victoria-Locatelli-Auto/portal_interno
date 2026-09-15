<?php

function meusDocumentosResults(mysqli $conn, int $user_id, string $search_query): array
{
    if ($search_query !== '') {
        $search_engine = new SearchEngine($conn, $user_id, false);
        $search_results = $search_engine->search($search_query);
        $documents = [];

        foreach ($search_results as $result) {
            if (isset($result['document']) && is_array($result['document'])) {
                $documents[] = $result['document'];
            }
        }

        return $documents;
    }

    $stmt = $conn->prepare("
        SELECT id, filename, original_name, filepath, status, uploaded_at, extracted_text
        FROM documents
        WHERE user_id = ? AND status IN ('approved', 'concluido')
        ORDER BY uploaded_at DESC
    ");

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function meusDocumentosStats(mysqli $conn, int $user_id): array
{
    return [
        'total' => meusDocumentosCount($conn, $user_id),
        'approved' => meusDocumentosCount($conn, $user_id, ['approved', 'concluido']),
        'rejected' => meusDocumentosCount($conn, $user_id, ['rejeitado', 'rejected']),
    ];
}

function meusDocumentosCount(mysqli $conn, int $user_id, array $statuses = []): int
{
    if (!$statuses) {
        $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM documents WHERE user_id = ?');

        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param('i', $user_id);
        $stmt->execute();

        return (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    }

    $placeholders = implode(',', array_fill(0, count($statuses), '?'));
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM documents WHERE user_id = ? AND status IN ({$placeholders})");

    if (!$stmt) {
        return 0;
    }

    $types = 'i' . str_repeat('s', count($statuses));
    $params = array_merge([$user_id], $statuses);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    return (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
}

function meusDocumentosSidebarItems(mysqli $conn): array
{
    $result = $conn->query("
        SELECT id, title, url, icon, menu_type
        FROM custom_menus
        WHERE is_active = TRUE
        ORDER BY order_position ASC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function meusDocumentosFileIcon(string $filename): string
{
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if ($extension === 'pdf') {
        return 'fas fa-file-pdf file-icon-pdf';
    }

    if (in_array($extension, ['doc', 'docx'], true)) {
        return 'fas fa-file-word file-icon-word';
    }

    if (in_array($extension, ['xls', 'xlsx'], true)) {
        return 'fas fa-file-excel file-icon-excel';
    }

    return 'fas fa-file file-icon-default';
}

function meusDocumentosTitle(string $title, int $limit = 50): string
{
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($title, 'UTF-8') > $limit ? mb_substr($title, 0, $limit, 'UTF-8') . '...' : $title;
    }

    return strlen($title) > $limit ? substr($title, 0, $limit) . '...' : $title;
}

function meusDocumentosStatusLabel(string $status): string
{
    $labels = [
        'approved' => 'Aprovado',
        'concluido' => 'Concluído',
        'pending' => 'Pendente',
        'rejected' => 'Rejeitado',
        'rejeitado' => 'Rejeitado',
    ];

    return $labels[$status] ?? ucfirst($status);
}

function meusDocumentosDate(?string $date): string
{
    if (!$date) {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('d/m/Y H:i', $timestamp) : '-';
}
