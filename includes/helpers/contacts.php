<?php

function formatPersonName(string $name): string
{
    $name = trim($name);
    $name = mb_strtolower($name, 'UTF-8');
    $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');

    $lower_words = ['Da', 'De', 'Do', 'Das', 'Dos', 'E'];
    foreach ($lower_words as $word) {
        $name = preg_replace('/\b' . $word . '\b/u', mb_strtolower($word, 'UTF-8'), $name);
    }

    $upper_words = ['Rh', 'Ti', 'Mcm'];
    foreach ($upper_words as $word) {
        $name = preg_replace('/\b' . $word . '\b/u', strtoupper($word), $name);
    }

    return $name;
}

function fullName(array $row): string
{
    $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
    return formatPersonName($name !== '' ? $name : 'Sem nome');
}

function buildPageLink(int $page): string
{
    $params = $_GET;
    $params['page'] = $page;

    return '?' . http_build_query($params);
}

function contactsFilters(array $query): array
{
    return [
        'search' => trim((string)($query['search'] ?? '')),
        'branch' => trim((string)($query['branch'] ?? '')),
        'job_title' => trim((string)($query['job_title'] ?? '')),
        'page' => max(1, (int)($query['page'] ?? 1)),
    ];
}

function contactsBaseSelect(): string
{
    return "
        SELECT
            u.id,
            u.first_name,
            u.last_name,
            u.email,
            u.access_level,
            u.is_active,
            u.wpp_corporativo,
            NULL AS branch,
            u.id_funcao,
            u.id_filial,
            f.descricao AS funcao,
            fi.nome_filial AS filiais
        FROM users u
        LEFT JOIN funcao f ON f.id = u.id_funcao
        LEFT JOIN filiais fi ON fi.id = u.id_filial
    ";
}

function contactsWhereClause(array $filters): array
{
    $where = ['is_active = TRUE'];
    $params = [];
    $types = '';

    if ($filters['search'] !== '') {
        $where[] = "(first_name LIKE ? OR last_name LIKE ? OR CONCAT(first_name, ' ', last_name) LIKE ?)";
        $search_like = '%' . $filters['search'] . '%';
        $params[] = $search_like;
        $params[] = $search_like;
        $params[] = $search_like;
        $types .= 'sss';
    }

    if ($filters['branch'] !== '') {
        $where[] = "COALESCE(filiais, '') = ?";
        $params[] = $filters['branch'];
        $types .= 's';
    }

    if ($filters['job_title'] !== '') {
        $where[] = "COALESCE(funcao, '') = ?";
        $params[] = $filters['job_title'];
        $types .= 's';
    }

    return [
        'sql' => implode(' AND ', $where),
        'params' => $params,
        'types' => $types,
    ];
}

function contactsCount(mysqli $conn, string $base_select, array $where): int
{
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM ({$base_select}) u WHERE {$where['sql']}");

    if (!$stmt) {
        return 0;
    }

    if (!empty($where['params'])) {
        $stmt->bind_param($where['types'], ...$where['params']);
    }

    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return (int)($result['total'] ?? 0);
}

function contactsList(mysqli $conn, string $base_select, array $where, int $limit, int $offset): array
{
    $stmt = $conn->prepare("
        SELECT *
        FROM ({$base_select}) u
        WHERE {$where['sql']}
        ORDER BY first_name ASC, last_name ASC
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

function contactsBranches(mysqli $conn, string $base_select): array
{
    $result = $conn->query("
        SELECT DISTINCT filiais AS branch
        FROM ({$base_select}) u
        WHERE COALESCE(filiais, '') <> ''
        ORDER BY branch ASC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function contactsJobs(mysqli $conn, string $base_select): array
{
    $result = $conn->query("
        SELECT DISTINCT funcao AS job_title
        FROM ({$base_select}) u
        WHERE COALESCE(funcao, '') <> ''
        ORDER BY job_title ASC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function contactsPageData(mysqli $conn, array $query, int $items_per_page = 12): array
{
    $filters = contactsFilters($query);
    $base_select = contactsBaseSelect();
    $where = contactsWhereClause($filters);
    $total_items = contactsCount($conn, $base_select, $where);
    $total_pages = max(1, (int)ceil($total_items / $items_per_page));
    $current_page = min($filters['page'], $total_pages);
    $offset = ($current_page - 1) * $items_per_page;

    return [
        'search' => $filters['search'],
        'branch_filter' => $filters['branch'],
        'job_filter' => $filters['job_title'],
        'current_page' => $current_page,
        'items_per_page' => $items_per_page,
        'total_items' => $total_items,
        'total_pages' => $total_pages,
        'contacts' => contactsList($conn, $base_select, $where, $items_per_page, $offset),
        'branches' => contactsBranches($conn, $base_select),
        'jobs' => contactsJobs($conn, $base_select),
    ];
}
