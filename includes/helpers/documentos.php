<?php

function documentosFilters(array $query): array
{
    return [
        'menu_id' => max(0, (int)($query['menu_id'] ?? 0)),
        'submenu_id' => max(0, (int)($query['submenu_id'] ?? 0)),
        'search' => trim((string)($query['search'] ?? '')),
        'page' => max(1, (int)($query['page'] ?? 1)),
    ];
}

function documentosCanImport(string $access_level): bool
{
    return in_array($access_level, ['admin', 'editor'], true);
}

function documentosWhere(array $filters): array
{
    $where = ["d.status IN ('approved', 'concluido')"];
    $params = [];
    $types = '';
    $join_menu = false;

    if ($filters['search'] !== '') {
        $where[] = 'd.original_name LIKE ?';
        $params[] = '%' . $filters['search'] . '%';
        $types .= 's';
    }

    if ($filters['menu_id'] > 0) {
        $join_menu = true;
        $where[] = 'dm.menu_id = ?';
        $params[] = $filters['menu_id'];
        $types .= 'i';
    }

    if ($filters['submenu_id'] > 0) {
        $join_menu = true;
        $where[] = 'dm.submenu_id = ?';
        $params[] = $filters['submenu_id'];
        $types .= 'i';
    }

    return [
        'sql' => implode(' AND ', $where),
        'params' => $params,
        'types' => $types,
        'join_menu' => $join_menu,
    ];
}

function documentosFromSql(bool $join_menu): string
{
    if ($join_menu) {
        return 'documents d INNER JOIN document_menus dm ON d.id = dm.document_id';
    }

    return 'documents d';
}

function documentosBindParams(mysqli_stmt $stmt, string $types, array $params): void
{
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
}

function documentosCount(mysqli $conn, array $where): int
{
    $from = documentosFromSql((bool)$where['join_menu']);
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT d.id) AS total
        FROM {$from}
        WHERE {$where['sql']}
    ");

    if (!$stmt) {
        return 0;
    }

    documentosBindParams($stmt, $where['types'], $where['params']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return (int)($result['total'] ?? 0);
}

function documentosList(mysqli $conn, array $where, int $limit, int $offset): array
{
    $from = documentosFromSql((bool)$where['join_menu']);
    $stmt = $conn->prepare("
        SELECT DISTINCT
            d.id,
            d.user_id,
            d.original_name,
            d.uploaded_at,
            d.status,
            d.extracted_text,
            d.filepath
        FROM {$from}
        WHERE {$where['sql']}
        ORDER BY d.uploaded_at DESC
        LIMIT ? OFFSET ?
    ");

    if (!$stmt) {
        return [];
    }

    $params = $where['params'];
    $types = $where['types'] . 'ii';
    $params[] = $limit;
    $params[] = $offset;

    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function documentosMenus(mysqli $conn): array
{
    $result = $conn->query("
        SELECT id, title
        FROM custom_menus
        WHERE is_active = TRUE
          AND menu_type = 'page'
        ORDER BY order_position ASC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function documentosSubmenus(mysqli $conn, int $menu_id): array
{
    if ($menu_id <= 0) {
        return [];
    }

    $stmt = $conn->prepare("
        SELECT id, title
        FROM custom_submenus
        WHERE menu_id = ?
          AND is_active = TRUE
        ORDER BY order_position ASC
    ");

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('i', $menu_id);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function documentosSelectedMenuName(array $menus, int $menu_id): string
{
    foreach ($menus as $menu) {
        if ((int)$menu['id'] === $menu_id) {
            return (string)$menu['title'];
        }
    }

    return '';
}

function documentosPageData(mysqli $conn, array $query, int $items_per_page = 10): array
{
    $filters = documentosFilters($query);
    $where = documentosWhere($filters);
    $total_documents = documentosCount($conn, $where);
    $total_pages = max(1, (int)ceil($total_documents / $items_per_page));
    $current_page = min($filters['page'], $total_pages);
    $offset = ($current_page - 1) * $items_per_page;
    $menus = documentosMenus($conn);

    return [
        'menu_id' => $filters['menu_id'],
        'submenu_id' => $filters['submenu_id'],
        'search' => $filters['search'],
        'current_page' => $current_page,
        'items_per_page' => $items_per_page,
        'total_documents' => $total_documents,
        'total_pages' => $total_pages,
        'documents' => documentosList($conn, $where, $items_per_page, $offset),
        'menus' => $menus,
        'submenus' => documentosSubmenus($conn, $filters['menu_id']),
        'selected_menu_name' => documentosSelectedMenuName($menus, $filters['menu_id']),
    ];
}

function documentosFormatTitle(string $filename): string
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

function documentosDateLabel(?string $date): string
{
    if (empty($date)) {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('d/m/Y H:i', $timestamp) : $date;
}

function documentosStatusLabel(string $status): string
{
    return ucfirst($status);
}

function documentosFilterLink(array $params = []): string
{
    $query = [];

    foreach ($params as $key => $value) {
        if ($value !== '' && $value !== 0 && $value !== null) {
            $query[$key] = $value;
        }
    }

    $query_string = http_build_query($query);

    return 'documentos.php' . ($query_string !== '' ? '?' . $query_string : '');
}

function documentosPageLink(int $page, int $menu_id, int $submenu_id, string $search): string
{
    return documentosFilterLink([
        'page' => $page,
        'menu_id' => $menu_id,
        'submenu_id' => $submenu_id,
        'search' => $search,
    ]);
}
