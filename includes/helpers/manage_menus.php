<?php

function manageMenusCurrentUser(mysqli $conn, int $user_id): ?array
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

function manageMenusSidebarItems(mysqli $conn): array
{
    $result = $conn->query("
        SELECT id, title, url, icon, menu_type
        FROM custom_menus
        WHERE is_active = TRUE
        ORDER BY order_position ASC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function manageMenusHandleAction(mysqli $conn, array $post): array
{
    $action = (string)($post['action'] ?? '');

    if ($action === 'add_menu') {
        return manageMenusAdd($conn, $post);
    }

    if ($action === 'edit_menu') {
        return manageMenusEdit($conn, $post);
    }

    if ($action === 'delete_menu') {
        return manageMenusDelete($conn, (int)($post['menu_id'] ?? 0));
    }

    if ($action === 'toggle_menu') {
        return manageMenusToggle($conn, (int)($post['menu_id'] ?? 0));
    }

    if ($action === 'add_submenu') {
        return manageMenusAddSubmenu($conn, $post);
    }

    if ($action === 'delete_submenu') {
        return manageMenusDeleteSubmenu($conn, (int)($post['submenu_id'] ?? 0));
    }

    return manageMenusResult('', '');
}

function manageMenusAdd(mysqli $conn, array $post): array
{
    $title = trim((string)($post['title'] ?? ''));
    $icon = trim((string)($post['icon'] ?? ''));
    $menu_type = trim((string)($post['menu_type'] ?? 'link'));
    $url = '';

    if ($title === '' || $icon === '') {
        return manageMenusResult('', 'Título e ícone são obrigatórios.');
    }

    if (manageMenusTitleExists($conn, $title)) {
        return manageMenusResult('', 'Categoria com esse nome já existe. Use outro nome.');
    }

    if ($menu_type === 'page') {
        $url = getUniquePageUrl(generatePageUrl($title));
    } else {
        $url = trim((string)($post['url'] ?? ''));

        if ($url === '') {
            return manageMenusResult('', 'URL é obrigatória para links externos.');
        }
    }

    $next_position = manageMenusNextPosition($conn, 'custom_menus');
    $stmt = $conn->prepare("
        INSERT INTO custom_menus (title, url, icon, menu_type, order_position, is_active)
        VALUES (?, ?, ?, ?, ?, TRUE)
    ");

    if (!$stmt) {
        return manageMenusResult('', 'Erro ao preparar categoria: ' . $conn->error);
    }

    $stmt->bind_param('ssssi', $title, $url, $icon, $menu_type, $next_position);

    if (!$stmt->execute()) {
        return manageMenusResult('', 'Erro ao criar categoria: ' . $stmt->error);
    }

    return manageMenusResult('Categoria adicionada com sucesso.', '');
}

function manageMenusEdit(mysqli $conn, array $post): array
{
    $menu_id = (int)($post['menu_id'] ?? 0);
    $title = trim((string)($post['title'] ?? ''));
    $url = trim((string)($post['url'] ?? ''));
    $icon = trim((string)($post['icon'] ?? ''));
    $menu_type = trim((string)($post['menu_type'] ?? 'link'));

    if ($menu_id <= 0 || $title === '' || $icon === '') {
        return manageMenusResult('', 'Dados inválidos.');
    }

    if ($menu_type === 'page' && $url === '') {
        $url = getUniquePageUrl(generatePageUrl($title));
    }

    if ($menu_type !== 'page' && $url === '') {
        return manageMenusResult('', 'URL é obrigatória para links externos.');
    }

    $stmt = $conn->prepare("
        UPDATE custom_menus
        SET title = ?, url = ?, icon = ?, menu_type = ?
        WHERE id = ?
    ");

    if (!$stmt) {
        return manageMenusResult('', 'Erro ao preparar atualização: ' . $conn->error);
    }

    $stmt->bind_param('ssssi', $title, $url, $icon, $menu_type, $menu_id);

    if (!$stmt->execute()) {
        return manageMenusResult('', 'Erro ao atualizar categoria: ' . $stmt->error);
    }

    return manageMenusResult('Categoria atualizada com sucesso.', '');
}

function manageMenusDelete(mysqli $conn, int $menu_id): array
{
    if ($menu_id <= 0) {
        return manageMenusResult('', 'Categoria inválida.');
    }

    $stmt = $conn->prepare('DELETE FROM custom_menus WHERE id = ?');

    if (!$stmt) {
        return manageMenusResult('', 'Erro ao preparar exclusão: ' . $conn->error);
    }

    $stmt->bind_param('i', $menu_id);
    $stmt->execute();

    return manageMenusResult('Categoria removida com sucesso.', '');
}

function manageMenusToggle(mysqli $conn, int $menu_id): array
{
    if ($menu_id <= 0) {
        return manageMenusResult('', 'Categoria inválida.');
    }

    $stmt = $conn->prepare('UPDATE custom_menus SET is_active = NOT is_active WHERE id = ?');

    if (!$stmt) {
        return manageMenusResult('', 'Erro ao preparar alteração: ' . $conn->error);
    }

    $stmt->bind_param('i', $menu_id);
    $stmt->execute();

    return manageMenusResult('Status alterado.', '');
}

function manageMenusAddSubmenu(mysqli $conn, array $post): array
{
    $menu_id = (int)($post['menu_id'] ?? 0);
    $title = trim((string)($post['submenu_title'] ?? ''));

    if ($menu_id <= 0 || $title === '') {
        return manageMenusResult('', 'Menu e título são obrigatórios.');
    }

    if (manageMenusSubmenuExists($conn, $menu_id, $title)) {
        return manageMenusResult('', 'Subcategoria com esse nome já existe neste menu.');
    }

    $next_position = manageMenusNextPosition($conn, 'custom_submenus', $menu_id);
    $stmt = $conn->prepare("
        INSERT INTO custom_submenus (menu_id, title, order_position, is_active)
        VALUES (?, ?, ?, TRUE)
    ");

    if (!$stmt) {
        return manageMenusResult('', 'Erro ao preparar subcategoria: ' . $conn->error);
    }

    $stmt->bind_param('isi', $menu_id, $title, $next_position);

    if (!$stmt->execute()) {
        return manageMenusResult('', 'Erro ao criar subcategoria: ' . $stmt->error);
    }

    return manageMenusResult('Subcategoria adicionada com sucesso.', '');
}

function manageMenusDeleteSubmenu(mysqli $conn, int $submenu_id): array
{
    if ($submenu_id <= 0) {
        return manageMenusResult('', 'Subcategoria inválida.');
    }

    $stmt = $conn->prepare('DELETE FROM custom_submenus WHERE id = ?');

    if (!$stmt) {
        return manageMenusResult('', 'Erro ao preparar exclusão: ' . $conn->error);
    }

    $stmt->bind_param('i', $submenu_id);
    $stmt->execute();

    return manageMenusResult('Subcategoria removida com sucesso.', '');
}

function manageMenusTitleExists(mysqli $conn, string $title): bool
{
    $stmt = $conn->prepare('SELECT id FROM custom_menus WHERE LOWER(title) = LOWER(?) LIMIT 1');

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('s', $title);
    $stmt->execute();

    return (bool)$stmt->get_result()->fetch_assoc();
}

function manageMenusSubmenuExists(mysqli $conn, int $menu_id, string $title): bool
{
    $stmt = $conn->prepare('SELECT id FROM custom_submenus WHERE menu_id = ? AND LOWER(title) = LOWER(?) LIMIT 1');

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('is', $menu_id, $title);
    $stmt->execute();

    return (bool)$stmt->get_result()->fetch_assoc();
}

function manageMenusNextPosition(mysqli $conn, string $table, ?int $menu_id = null): int
{
    if ($table === 'custom_submenus' && $menu_id !== null) {
        $stmt = $conn->prepare('SELECT MAX(order_position) AS max_pos FROM custom_submenus WHERE menu_id = ?');

        if (!$stmt) {
            return 1;
        }

        $stmt->bind_param('i', $menu_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return ((int)($row['max_pos'] ?? 0)) + 1;
    }

    $result = $conn->query('SELECT MAX(order_position) AS max_pos FROM custom_menus');
    $row = $result ? $result->fetch_assoc() : [];

    return ((int)($row['max_pos'] ?? 0)) + 1;
}

function manageMenusList(mysqli $conn): array
{
    $result = $conn->query("
        SELECT id, title, url, icon, menu_type, order_position, is_active
        FROM custom_menus
        ORDER BY order_position ASC
    ");

    $menus = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    foreach ($menus as &$menu) {
        $menu['submenus'] = manageMenusSubmenus($conn, (int)$menu['id']);
    }
    unset($menu);

    return $menus;
}

function manageMenusSubmenus(mysqli $conn, int $menu_id): array
{
    $stmt = $conn->prepare("
        SELECT id, title, order_position, is_active
        FROM custom_submenus
        WHERE menu_id = ?
        ORDER BY order_position ASC
    ");

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('i', $menu_id);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function manageMenusShortUrl(string $url): string
{
    return mb_strlen($url, 'UTF-8') > 25 ? mb_substr($url, 0, 25, 'UTF-8') . '...' : $url;
}

function manageMenusResult(string $success, string $error): array
{
    return [
        'success' => $success,
        'error' => $error,
    ];
}
