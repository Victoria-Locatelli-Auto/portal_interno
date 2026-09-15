<?php

function dadosTiMenu(mysqli $conn): ?array
{
    $stmt = $conn->prepare("
        SELECT id, title, icon
        FROM custom_menus
        WHERE (url = ? OR title = ?)
          AND is_active = TRUE
          AND menu_type = 'page'
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    $url = '/pages/dadosti';
    $title = 'Dados TI';
    $stmt->bind_param('ss', $url, $title);
    $stmt->execute();
    $menu = $stmt->get_result()->fetch_assoc();

    return $menu ?: null;
}

function dadosTiSubmenus(mysqli $conn, int $menu_id): array
{
    $stmt = $conn->prepare("
        SELECT
            s.id,
            s.title,
            COUNT(DISTINCT dm.document_id) AS doc_count
        FROM custom_submenus s
        LEFT JOIN document_menus dm
            ON s.id = dm.submenu_id
            AND dm.menu_id = ?
        WHERE s.menu_id = ?
          AND s.is_active = TRUE
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

function dadosTiDocuments(mysqli $conn, int $menu_id, ?int $submenu_id = null): array
{
    if ($submenu_id !== null) {
        $stmt = $conn->prepare("
            SELECT d.id, d.original_name, d.uploaded_at, d.status
            FROM documents d
            INNER JOIN document_menus dm ON d.id = dm.document_id
            WHERE dm.menu_id = ?
              AND dm.submenu_id = ?
              AND d.status IN ('approved', 'concluido')
            ORDER BY d.uploaded_at DESC
        ");

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('ii', $menu_id, $submenu_id);
    } else {
        $stmt = $conn->prepare("
            SELECT d.id, d.original_name, d.uploaded_at, d.status
            FROM documents d
            INNER JOIN document_menus dm ON d.id = dm.document_id
            WHERE dm.menu_id = ?
              AND d.status IN ('approved', 'concluido')
            ORDER BY d.uploaded_at DESC
        ");

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $menu_id);
    }

    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function dadosTiDateLabel(?string $date): string
{
    if (empty($date)) {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('d/m/Y', $timestamp) : $date;
}

function dadosTiDocumentUrl(string $base_url, int $document_id): string
{
    return $base_url . '/pages/view_document.php?id=' . $document_id;
}
