<?php

function adminCsrfToken(): string
{
    if (empty($_SESSION['admin_csrf'])) {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION['admin_csrf'];
}

function adminCurrentUser(mysqli $conn, int $user_id): ?array
{
    $stmt = $conn->prepare('SELECT id, username, access_level FROM users WHERE id = ? LIMIT 1');
    if (!$stmt) return null;
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $user ?: null;
}

function adminSidebarItems(mysqli $conn): array
{
    $result = $conn->query('SELECT id, title, url, icon, menu_type FROM custom_menus WHERE is_active = TRUE ORDER BY order_position ASC');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function adminHandleAction(mysqli $conn, array $post, int $current_user_id): array
{
    $submitted = (string)($post['csrf_token'] ?? '');
    $stored = (string)($_SESSION['admin_csrf'] ?? '');
    if ($stored === '' || !hash_equals($stored, $submitted)) {
        return adminResult('', 'A sessão do formulário expirou. Atualize a página e tente novamente.');
    }

    $action = (string)($post['action'] ?? '');
    $target_id = (int)($post['user_id'] ?? 0);
    $target = adminFindUser($conn, $target_id);
    if (!$target) {
        return adminResult('', 'Colaborador não encontrado.');
    }

    $roles = [
        'set_admin' => 'admin', 'set_editor' => 'editor', 'set_viewer' => 'viewer',
        'set_gestor' => 'Gestor', 'set_analista' => 'Analista',
        'set_coordenador' => 'Coordenador', 'set_marketing' => 'Marketing', 'set_rh' => 'RH',
    ];

    if (isset($roles[$action])) {
        if ($target_id === $current_user_id && $roles[$action] !== 'admin') {
            return adminResult('', 'Você não pode retirar o próprio acesso de administrador.');
        }
        return adminUpdateField($conn, $target_id, 'access_level', $roles[$action], 'Perfil atualizado com sucesso.');
    }

    if ($action === 'approve_import') {
        return adminUpdateImport($conn, $target_id, true);
    }
    if ($action === 'revoke_import') {
        return adminUpdateImport($conn, $target_id, false);
    }
    if ($action === 'inativar_usuario') {
        if ($target_id === $current_user_id) {
            return adminResult('', 'Você não pode inativar o próprio usuário.');
        }
        return adminUpdateActive($conn, $target_id, false);
    }
    if ($action === 'ativar_usuario') {
        return adminUpdateActive($conn, $target_id, true);
    }
    if ($action === 'resetar_senha') {
        return adminResetPassword($conn, $target);
    }

    return adminResult('', 'Ação administrativa inválida.');
}

function adminFindUser(mysqli $conn, int $user_id): ?array
{
    if ($user_id <= 0) return null;
    $stmt = $conn->prepare('SELECT id, username, first_name, last_name FROM users WHERE id = ? LIMIT 1');
    if (!$stmt) return null;
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $user ?: null;
}

function adminUpdateField(mysqli $conn, int $user_id, string $field, string $value, string $message): array
{
    if ($field !== 'access_level') return adminResult('', 'Alteração não permitida.');
    $stmt = $conn->prepare("UPDATE users SET {$field} = ? WHERE id = ?");
    if (!$stmt) return adminResult('', 'Não foi possível preparar a atualização.');
    $stmt->bind_param('si', $value, $user_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success ? adminResult($message, '') : adminResult('', 'Não foi possível atualizar o colaborador.');
}

function adminUpdateImport(mysqli $conn, int $user_id, bool $allowed): array
{
    $value = $allowed ? 1 : 0;
    $stmt = $allowed
        ? $conn->prepare('UPDATE users SET can_import = ?, email_verified = TRUE WHERE id = ?')
        : $conn->prepare('UPDATE users SET can_import = ? WHERE id = ?');
    if (!$stmt) return adminResult('', 'Não foi possível preparar a alteração da permissão.');
    $stmt->bind_param('ii', $value, $user_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success ? adminResult('Permissão de importação atualizada.', '') : adminResult('', 'Não foi possível alterar a permissão de importação.');
}

function adminUpdateActive(mysqli $conn, int $user_id, bool $active): array
{
    $value = $active ? 1 : 0;
    $stmt = $conn->prepare('UPDATE users SET is_active = ? WHERE id = ?');
    if (!$stmt) return adminResult('', 'Não foi possível preparar a alteração de status.');
    $stmt->bind_param('ii', $value, $user_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success ? adminResult('Status do colaborador atualizado.', '') : adminResult('', 'Não foi possível alterar o status.');
}

function adminResetPassword(mysqli $conn, array $target): array
{
    $temporary_password = 'Mcm@2026';
    $hash = password_hash($temporary_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
    if (!$stmt) return adminResult('', 'Não foi possível preparar a redefinição da senha.');
    $target_id = (int)$target['id'];
    $stmt->bind_param('si', $hash, $target_id);
    $success = $stmt->execute();
    $stmt->close();
    if (!$success) return adminResult('', 'Não foi possível redefinir a senha.');
    return adminResult('Senha de ' . adminUserName($target) . ' redefinida para a senha temporária Mcm@2026.', '');
}

function adminFilters(array $query): array
{
    $orders = ['username', 'email', 'access_level', 'created_at', 'is_active'];
    $per_page_options = [10, 20, 50];
    $order = (string)($query['order'] ?? 'created_at');
    $per_page = (int)($query['per_page'] ?? 10);
    return [
        'search' => trim((string)($query['search'] ?? '')),
        'order' => in_array($order, $orders, true) ? $order : 'created_at',
        'dir' => strtolower((string)($query['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC',
        'per_page' => in_array($per_page, $per_page_options, true) ? $per_page : 10,
        'page' => max(1, (int)($query['page'] ?? 1)),
    ];
}

function adminUsersData(mysqli $conn, array $filters): array
{
    $search = $filters['search'];
    $where = '';
    if ($search !== '') {
        $where = ' WHERE username LIKE ? OR email LIKE ? OR access_level LIKE ? OR first_name LIKE ? OR last_name LIKE ?';
    }

    $count_stmt = $conn->prepare('SELECT COUNT(*) AS total FROM users' . $where);
    if (!$count_stmt) return ['users' => [], 'total' => 0, 'pages' => 1, 'page' => 1];
    if ($search !== '') {
        $pattern = '%' . $search . '%';
        $count_stmt->bind_param('sssss', $pattern, $pattern, $pattern, $pattern, $pattern);
    }
    $count_stmt->execute();
    $total = (int)($count_stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $count_stmt->close();

    $pages = max(1, (int)ceil($total / $filters['per_page']));
    $page = min($filters['page'], $pages);
    $offset = ($page - 1) * $filters['per_page'];
    $sql = 'SELECT id, username, first_name, last_name, email, access_level, email_verified, can_import, created_at, is_active, profile_photo FROM users'
        . $where . " ORDER BY {$filters['order']} {$filters['dir']} LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return ['users' => [], 'total' => $total, 'pages' => $pages, 'page' => $page];
    if ($search !== '') {
        $pattern = '%' . $search . '%';
        $stmt->bind_param('sssssii', $pattern, $pattern, $pattern, $pattern, $pattern, $filters['per_page'], $offset);
    } else {
        $stmt->bind_param('ii', $filters['per_page'], $offset);
    }
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return ['users' => $users, 'total' => $total, 'pages' => $pages, 'page' => $page];
}

function adminStats(mysqli $conn): array
{
    $result = $conn->query('SELECT COUNT(*) AS total, SUM(is_active = TRUE) AS active, SUM(email_verified = FALSE) AS pending, SUM(can_import = TRUE) AS importers FROM users');
    $row = $result ? $result->fetch_assoc() : [];
    return ['total' => (int)($row['total'] ?? 0), 'active' => (int)($row['active'] ?? 0), 'pending' => (int)($row['pending'] ?? 0), 'importers' => (int)($row['importers'] ?? 0)];
}

function adminUserName(array $user): string
{
    $name = trim((string)($user['first_name'] ?? '') . ' ' . (string)($user['last_name'] ?? ''));
    return $name !== '' ? mb_convert_case(mb_strtolower($name, 'UTF-8'), MB_CASE_TITLE, 'UTF-8') : (string)($user['username'] ?? 'Usuário');
}

function adminProfilePhotoUrl(string $base_url, ?string $path): ?string
{
    $path = str_replace('\\', '/', trim((string)$path));
    if (!preg_match('#^uploads/profile/[a-zA-Z0-9._-]+$#', $path)) return null;
    return rtrim($base_url, '/') . '/' . $path;
}

function adminBuildQuery(array $filters, array $overrides = []): string
{
    $params = ['search' => $filters['search'], 'order' => $filters['order'], 'dir' => strtolower($filters['dir']), 'per_page' => $filters['per_page'], 'page' => $filters['page']];
    foreach ($overrides as $key => $value) $params[$key] = $value;
    return http_build_query(array_filter($params, static fn($value) => $value !== ''));
}

function adminSortDirection(string $field, array $filters): string
{
    return $field === $filters['order'] && $filters['dir'] === 'ASC' ? 'desc' : 'asc';
}

function adminResult(string $success, string $error): array
{
    return ['success' => $success, 'error' => $error];
}
