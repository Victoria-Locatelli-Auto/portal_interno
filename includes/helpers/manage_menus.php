<?php

function manageMenusCsrfToken(): string
{
    if (empty($_SESSION['manage_menus_csrf'])) {
        $_SESSION['manage_menus_csrf'] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION['manage_menus_csrf'];
}

function manageMenusCurrentUser(mysqli $conn, int $user_id): ?array
{
    $stmt = $conn->prepare('SELECT access_level FROM users WHERE id = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $user ?: null;
}

function manageMenusSidebarItems(mysqli $conn): array
{
    $result = $conn->query("SELECT id, title, url, icon, menu_type FROM custom_menus WHERE is_active = TRUE ORDER BY order_position ASC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function manageMenusHandleAction(mysqli $conn, array $post): array
{
    $submitted_token = (string)($post['csrf_token'] ?? '');
    $session_token = (string)($_SESSION['manage_menus_csrf'] ?? '');
    if ($session_token === '' || !hash_equals($session_token, $submitted_token)) {
        return manageMenusResult('', 'A sessão do formulário expirou. Atualize a página e tente novamente.');
    }

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
    return manageMenusResult('', 'Ação inválida.');
}

function manageMenusAdd(mysqli $conn, array $post): array
{
    $data = manageMenusValidateMenu($conn, $post);
    if (isset($data['error'])) {
        return manageMenusResult('', $data['error']);
    }
    if (manageMenusTitleExists($conn, $data['title'])) {
        return manageMenusResult('', 'Já existe uma categoria com esse nome.');
    }

    $next_position = manageMenusNextPosition($conn, 'custom_menus');
    $stmt = $conn->prepare('INSERT INTO custom_menus (title, url, icon, menu_type, order_position, is_active) VALUES (?, ?, ?, ?, ?, TRUE)');
    if (!$stmt) {
        return manageMenusResult('', 'Não foi possível preparar a criação da categoria.');
    }
    $stmt->bind_param('ssssi', $data['title'], $data['url'], $data['icon'], $data['menu_type'], $next_position);
    $success = $stmt->execute();
    $stmt->close();
    return $success ? manageMenusResult('Categoria adicionada com sucesso.', '') : manageMenusResult('', 'Não foi possível criar a categoria.');
}

function manageMenusEdit(mysqli $conn, array $post): array
{
    $menu_id = (int)($post['menu_id'] ?? 0);
    if ($menu_id <= 0 || !manageMenusRecordExists($conn, 'custom_menus', $menu_id)) {
        return manageMenusResult('', 'Categoria não encontrada.');
    }

    $data = manageMenusValidateMenu($conn, $post, $menu_id);
    if (isset($data['error'])) {
        return manageMenusResult('', $data['error']);
    }
    if (manageMenusTitleExists($conn, $data['title'], $menu_id)) {
        return manageMenusResult('', 'Já existe outra categoria com esse nome.');
    }

    $stmt = $conn->prepare('UPDATE custom_menus SET title = ?, url = ?, icon = ?, menu_type = ? WHERE id = ?');
    if (!$stmt) {
        return manageMenusResult('', 'Não foi possível preparar a atualização.');
    }
    $stmt->bind_param('ssssi', $data['title'], $data['url'], $data['icon'], $data['menu_type'], $menu_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success ? manageMenusResult('Categoria atualizada com sucesso.', '') : manageMenusResult('', 'Não foi possível atualizar a categoria.');
}

function manageMenusValidateMenu(mysqli $conn, array $post, int $menu_id = 0): array
{
    $title = trim((string)($post['title'] ?? ''));
    $icon = trim((string)($post['icon'] ?? ''));
    $menu_type = trim((string)($post['menu_type'] ?? 'page'));

    if ($title === '' || mb_strlen($title, 'UTF-8') > 120) {
        return ['error' => 'Informe um nome com até 120 caracteres.'];
    }
    if (!in_array($menu_type, ['page', 'link'], true)) {
        return ['error' => 'Selecione um tipo de categoria válido.'];
    }
    if (!array_key_exists($icon, getIconList())) {
        return ['error' => 'Selecione um ícone válido.'];
    }

    if ($menu_type === 'page') {
        $current_url = $menu_id > 0 ? manageMenusCurrentPageUrl($conn, $menu_id) : '';
        $url = $current_url !== '' ? $current_url : getUniquePageUrl(generatePageUrl($title));
    } else {
        $url = trim((string)($post['url'] ?? ''));
        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
        if (!filter_var($url, FILTER_VALIDATE_URL) || !in_array($scheme, ['http', 'https'], true)) {
            return ['error' => 'Informe um link externo válido iniciado por http:// ou https://.'];
        }
    }
    return compact('title', 'icon', 'menu_type', 'url');
}

function manageMenusDelete(mysqli $conn, int $menu_id): array
{
    if ($menu_id <= 0 || !manageMenusRecordExists($conn, 'custom_menus', $menu_id)) {
        return manageMenusResult('', 'Categoria não encontrada.');
    }
    if (manageMenusCountByField($conn, 'document_menus', 'menu_id', $menu_id) > 0) {
        return manageMenusResult('', 'Essa categoria possui documentos vinculados e não pode ser excluída.');
    }
    if (manageMenusCountByField($conn, 'custom_submenus', 'menu_id', $menu_id) > 0) {
        return manageMenusResult('', 'Remova primeiro as subcategorias desta categoria.');
    }
    return manageMenusDeleteRecord($conn, 'custom_menus', $menu_id, 'Categoria removida com sucesso.', 'Não foi possível remover a categoria.');
}

function manageMenusToggle(mysqli $conn, int $menu_id): array
{
    if ($menu_id <= 0 || !manageMenusRecordExists($conn, 'custom_menus', $menu_id)) {
        return manageMenusResult('', 'Categoria não encontrada.');
    }
    $stmt = $conn->prepare('UPDATE custom_menus SET is_active = NOT is_active WHERE id = ?');
    if (!$stmt) {
        return manageMenusResult('', 'Não foi possível preparar a alteração de status.');
    }
    $stmt->bind_param('i', $menu_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success ? manageMenusResult('Status atualizado com sucesso.', '') : manageMenusResult('', 'Não foi possível alterar o status.');
}

function manageMenusAddSubmenu(mysqli $conn, array $post): array
{
    $menu_id = (int)($post['menu_id'] ?? 0);
    $title = trim((string)($post['submenu_title'] ?? ''));
    if ($menu_id <= 0 || !manageMenusRecordExists($conn, 'custom_menus', $menu_id)) {
        return manageMenusResult('', 'Categoria não encontrada.');
    }
    if ($title === '' || mb_strlen($title, 'UTF-8') > 120) {
        return manageMenusResult('', 'Informe um nome de subcategoria com até 120 caracteres.');
    }
    if (manageMenusSubmenuExists($conn, $menu_id, $title)) {
        return manageMenusResult('', 'Essa subcategoria já existe na categoria selecionada.');
    }

    $next_position = manageMenusNextPosition($conn, 'custom_submenus', $menu_id);
    $stmt = $conn->prepare('INSERT INTO custom_submenus (menu_id, title, order_position, is_active) VALUES (?, ?, ?, TRUE)');
    if (!$stmt) {
        return manageMenusResult('', 'Não foi possível preparar a criação da subcategoria.');
    }
    $stmt->bind_param('isi', $menu_id, $title, $next_position);
    $success = $stmt->execute();
    $stmt->close();
    return $success ? manageMenusResult('Subcategoria adicionada com sucesso.', '') : manageMenusResult('', 'Não foi possível criar a subcategoria.');
}

function manageMenusDeleteSubmenu(mysqli $conn, int $submenu_id): array
{
    if ($submenu_id <= 0 || !manageMenusRecordExists($conn, 'custom_submenus', $submenu_id)) {
        return manageMenusResult('', 'Subcategoria não encontrada.');
    }
    if (manageMenusCountByField($conn, 'document_menus', 'submenu_id', $submenu_id) > 0) {
        return manageMenusResult('', 'Essa subcategoria possui documentos vinculados e não pode ser excluída.');
    }
    return manageMenusDeleteRecord($conn, 'custom_submenus', $submenu_id, 'Subcategoria removida com sucesso.', 'Não foi possível remover a subcategoria.');
}

function manageMenusDeleteRecord(mysqli $conn, string $table, int $id, string $success_message, string $error_message): array
{
    if (!in_array($table, ['custom_menus', 'custom_submenus'], true)) {
        return manageMenusResult('', $error_message);
    }
    $stmt = $conn->prepare("DELETE FROM {$table} WHERE id = ?");
    if (!$stmt) {
        return manageMenusResult('', $error_message);
    }
    $stmt->bind_param('i', $id);
    $success = $stmt->execute();
    $stmt->close();
    return $success ? manageMenusResult($success_message, '') : manageMenusResult('', $error_message);
}

function manageMenusRecordExists(mysqli $conn, string $table, int $id): bool
{
    if (!in_array($table, ['custom_menus', 'custom_submenus'], true)) {
        return false;
    }
    $stmt = $conn->prepare("SELECT id FROM {$table} WHERE id = ? LIMIT 1");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $exists = (bool)$stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $exists;
}

function manageMenusCountByField(mysqli $conn, string $table, string $field, int $id): int
{
    $allowed = ['document_menus' => ['menu_id', 'submenu_id'], 'custom_submenus' => ['menu_id']];
    if (!isset($allowed[$table]) || !in_array($field, $allowed[$table], true)) {
        return 0;
    }
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM {$table} WHERE {$field} = ?");
    if (!$stmt) {
        return 0;
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($row['total'] ?? 0);
}

function manageMenusCurrentPageUrl(mysqli $conn, int $menu_id): string
{
    $stmt = $conn->prepare("SELECT url FROM custom_menus WHERE id = ? AND menu_type = 'page' LIMIT 1");
    if (!$stmt) {
        return '';
    }
    $stmt->bind_param('i', $menu_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return trim((string)($row['url'] ?? ''));
}

function manageMenusTitleExists(mysqli $conn, string $title, int $except_id = 0): bool
{
    $stmt = $conn->prepare('SELECT id FROM custom_menus WHERE LOWER(title) = LOWER(?) AND id <> ? LIMIT 1');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('si', $title, $except_id);
    $stmt->execute();
    $exists = (bool)$stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $exists;
}

function manageMenusSubmenuExists(mysqli $conn, int $menu_id, string $title): bool
{
    $stmt = $conn->prepare('SELECT id FROM custom_submenus WHERE menu_id = ? AND LOWER(title) = LOWER(?) LIMIT 1');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('is', $menu_id, $title);
    $stmt->execute();
    $exists = (bool)$stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $exists;
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
        $stmt->close();
        return ((int)($row['max_pos'] ?? 0)) + 1;
    }
    $result = $conn->query('SELECT MAX(order_position) AS max_pos FROM custom_menus');
    $row = $result ? $result->fetch_assoc() : [];
    return ((int)($row['max_pos'] ?? 0)) + 1;
}

function manageMenusList(mysqli $conn): array
{
    $result = $conn->query('SELECT id, title, url, icon, menu_type, order_position, is_active FROM custom_menus ORDER BY order_position ASC');
    $menus = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    foreach ($menus as &$menu) {
        $menu['submenus'] = manageMenusSubmenus($conn, (int)$menu['id']);
    }
    unset($menu);
    return $menus;
}

function manageMenusSubmenus(mysqli $conn, int $menu_id): array
{
    $stmt = $conn->prepare('SELECT id, title, order_position, is_active FROM custom_submenus WHERE menu_id = ? ORDER BY order_position ASC');
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('i', $menu_id);
    $stmt->execute();
    $submenus = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $submenus;
}

function manageMenusStats(array $menus): array
{
    $stats = ['total' => count($menus), 'active' => 0, 'pages' => 0, 'links' => 0, 'submenus' => 0];
    foreach ($menus as $menu) {
        $stats['active'] += !empty($menu['is_active']) ? 1 : 0;
        $stats[$menu['menu_type'] === 'page' ? 'pages' : 'links']++;
        $stats['submenus'] += count($menu['submenus'] ?? []);
    }
    return $stats;
}

function manageMenusShortUrl(string $url): string
{
    return mb_strlen($url, 'UTF-8') > 38 ? mb_substr($url, 0, 38, 'UTF-8') . '...' : $url;
}

function manageMenusResult(string $success, string $error): array
{
    return ['success' => $success, 'error' => $error];
}
