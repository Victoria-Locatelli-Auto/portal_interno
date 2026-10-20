<?php

function manageSystemsCurrentUser(mysqli $conn, int $user_id): ?array
{
    $stmt = $conn->prepare('SELECT id, access_level FROM users WHERE id = ? LIMIT 1');
    if (!$stmt) return null;
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $user ?: null;
}

function manageSystemsSidebarItems(mysqli $conn): array
{
    $result = $conn->query('SELECT id, title, url, icon, menu_type FROM custom_menus WHERE is_active = TRUE ORDER BY order_position ASC');

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function manageSystemsCsrfToken(): string
{
    if (empty($_SESSION['manage_systems_csrf'])) {
        $_SESSION['manage_systems_csrf'] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION['manage_systems_csrf'];
}

function manageSystemsHandleAction(mysqli $conn, array $post, array $files, int $user_id, string $project_root): array
{
    $submitted = (string)($post['csrf_token'] ?? '');
    $stored = (string)($_SESSION['manage_systems_csrf'] ?? '');

    if ($stored === '' || !hash_equals($stored, $submitted)) {
        return manageSystemsResult('error', 'A sessão do formulário expirou. Atualize a página e tente novamente.');
    }

    $action = (string)($post['action'] ?? '');

    if ($action === 'add_system') {
        return manageSystemsAdd($conn, $post, $files['system_logo'] ?? [], $user_id, $project_root);
    }

    if ($action === 'toggle_system') {
        return manageSystemsToggle($conn, (int)($post['system_id'] ?? 0));
    }

    if ($action === 'update_logo') {
        return manageSystemsUpdateLogo(
            $conn,
            (int)($post['system_id'] ?? 0),
            $files['system_logo'] ?? [],
            $project_root
        );
    }

    if ($action === 'delete_system') {
        return manageSystemsDelete($conn, (int)($post['system_id'] ?? 0), $project_root);
    }

    return manageSystemsResult('error', 'Ação inválida.');
}

function manageSystemsAdd(mysqli $conn, array $post, array $file, int $user_id, string $project_root): array
{
    $name = trim((string)($post['name'] ?? ''));
    $description = trim((string)($post['description'] ?? ''));
    $system_url = trim((string)($post['system_url'] ?? ''));
    $order_position = max(0, (int)($post['order_position'] ?? 0));
    $is_active = isset($post['is_active']) ? 1 : 0;

    if ($name === '' || mb_strlen($name, 'UTF-8') > 120) {
        return manageSystemsResult('error', 'Informe um nome com até 120 caracteres.');
    }

    if (mb_strlen($description, 'UTF-8') > 255) {
        return manageSystemsResult('error', 'A descrição deve ter no máximo 255 caracteres.');
    }

    if (!manageSystemsValidUrl($system_url)) {
        return manageSystemsResult('error', 'Informe um link HTTP ou HTTPS válido.');
    }

    if (mb_strlen($system_url, 'UTF-8') > 2048) {
        return manageSystemsResult('error', 'O link deve ter no máximo 2048 caracteres.');
    }

    $upload = manageSystemsStoreLogo($file, $project_root);
    if (!$upload['success']) {
        return manageSystemsResult('error', $upload['message']);
    }

    if ($order_position === 0) {
        $position_result = $conn->query('SELECT COALESCE(MAX(order_position), 0) + 1 AS next_position FROM company_systems');
        $order_position = (int)(($position_result ? $position_result->fetch_assoc()['next_position'] : 1) ?? 1);
    }

    $stmt = $conn->prepare('INSERT INTO company_systems (name, description, system_url, logo_path, is_active, order_position, created_by) VALUES (?, NULLIF(?, \'\'), ?, NULLIF(?, \'\'), ?, ?, ?)');

    if (!$stmt) {
        manageSystemsDeleteStoredLogo($upload['path'], $project_root);
        return manageSystemsResult('error', 'Não foi possível preparar o cadastro do sistema.');
    }

    $logo_path = (string)($upload['path'] ?? '');
    $stmt->bind_param('ssssiii', $name, $description, $system_url, $logo_path, $is_active, $order_position, $user_id);
    $saved = $stmt->execute();
    $stmt->close();

    if (!$saved) {
        manageSystemsDeleteStoredLogo($upload['path'], $project_root);
        return manageSystemsResult('error', 'Não foi possível cadastrar o sistema.');
    }

    return manageSystemsResult('success', 'Sistema cadastrado com sucesso.');
}

function manageSystemsStoreLogo(array $file, string $project_root): array
{
    $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) return ['success' => true, 'path' => null];
    if ($error !== UPLOAD_ERR_OK) return ['success' => false, 'message' => 'Não foi possível receber a logo.'];

    $size = (int)($file['size'] ?? 0);
    $tmp_name = (string)($file['tmp_name'] ?? '');
    if ($size <= 0 || $size > 2 * 1024 * 1024) return ['success' => false, 'message' => 'A logo deve ter no máximo 2 MB.'];
    if ($tmp_name === '' || !is_uploaded_file($tmp_name)) return ['success' => false, 'message' => 'O arquivo enviado não é válido.'];

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp_name);
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($types[$mime])) return ['success' => false, 'message' => 'Envie uma logo JPG, PNG ou WebP.'];

    $image_info = @getimagesize($tmp_name);
    if ($image_info === false) return ['success' => false, 'message' => 'O conteúdo enviado não é uma imagem válida.'];
    [$width, $height] = $image_info;
    if ($width < 64 || $height < 64 || $width > 4000 || $height > 4000) {
        return ['success' => false, 'message' => 'Use uma logo entre 64 x 64 e 4000 x 4000 pixels.'];
    }

    $upload_dir = rtrim($project_root, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'systems';
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
        return ['success' => false, 'message' => 'Não foi possível preparar a pasta das logos.'];
    }

    $filename = 'system-' . date('YmdHis') . '-' . bin2hex(random_bytes(10)) . '.' . $types[$mime];
    $destination = $upload_dir . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($tmp_name, $destination)) return ['success' => false, 'message' => 'Não foi possível salvar a logo.'];

    return ['success' => true, 'path' => 'uploads/systems/' . $filename];
}

function manageSystemsToggle(mysqli $conn, int $system_id): array
{
    if ($system_id <= 0 || !manageSystemsFind($conn, $system_id)) {
        return manageSystemsResult('error', 'Sistema não encontrado.');
    }

    $stmt = $conn->prepare('UPDATE company_systems SET is_active = NOT is_active WHERE id = ?');
    if (!$stmt) return manageSystemsResult('error', 'Não foi possível preparar a alteração.');
    $stmt->bind_param('i', $system_id);
    $success = $stmt->execute();
    $stmt->close();

    return $success
        ? manageSystemsResult('success', 'Status do sistema atualizado.')
        : manageSystemsResult('error', 'Não foi possível alterar o sistema.');
}

function manageSystemsUpdateLogo(mysqli $conn, int $system_id, array $file, string $project_root): array
{
    $system = manageSystemsFind($conn, $system_id);
    if (!$system) return manageSystemsResult('error', 'Sistema não encontrado.');

    $upload = manageSystemsStoreLogo($file, $project_root);
    if (!$upload['success'] || empty($upload['path'])) {
        return manageSystemsResult('error', $upload['message'] ?? 'Selecione uma logo para o sistema.');
    }

    $stmt = $conn->prepare('UPDATE company_systems SET logo_path = ? WHERE id = ?');
    if (!$stmt) {
        manageSystemsDeleteStoredLogo($upload['path'], $project_root);
        return manageSystemsResult('error', 'Não foi possível preparar a atualização da logo.');
    }

    $stmt->bind_param('si', $upload['path'], $system_id);
    $success = $stmt->execute();
    $stmt->close();

    if (!$success) {
        manageSystemsDeleteStoredLogo($upload['path'], $project_root);
        return manageSystemsResult('error', 'Não foi possível atualizar a logo.');
    }

    manageSystemsDeleteStoredLogo($system['logo_path'] ?? null, $project_root);

    return manageSystemsResult('success', 'Logo atualizada com sucesso.');
}

function manageSystemsDelete(mysqli $conn, int $system_id, string $project_root): array
{
    $system = manageSystemsFind($conn, $system_id);
    if (!$system) return manageSystemsResult('error', 'Sistema não encontrado.');

    $stmt = $conn->prepare('DELETE FROM company_systems WHERE id = ?');
    if (!$stmt) return manageSystemsResult('error', 'Não foi possível preparar a exclusão.');
    $stmt->bind_param('i', $system_id);
    $success = $stmt->execute();
    $stmt->close();

    if (!$success) return manageSystemsResult('error', 'Não foi possível excluir o sistema.');
    manageSystemsDeleteStoredLogo((string)$system['logo_path'], $project_root);

    return manageSystemsResult('success', 'Sistema excluído com sucesso.');
}

function manageSystemsFind(mysqli $conn, int $system_id): ?array
{
    if ($system_id <= 0) return null;
    $stmt = $conn->prepare('SELECT id, logo_path FROM company_systems WHERE id = ? LIMIT 1');
    if (!$stmt) return null;
    $stmt->bind_param('i', $system_id);
    $stmt->execute();
    $system = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $system ?: null;
}

function manageSystemsDeleteStoredLogo(?string $path, string $project_root): void
{
    $path = str_replace('\\', '/', trim((string)$path));
    if (!preg_match('#^uploads/systems/[a-zA-Z0-9._-]+$#', $path)) return;
    $absolute = rtrim($project_root, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    if (is_file($absolute)) @unlink($absolute);
}

function manageSystemsList(mysqli $conn): array
{
    $result = $conn->query("
        SELECT s.*, u.username AS created_by_name
        FROM company_systems s
        LEFT JOIN users u ON u.id = s.created_by
        ORDER BY s.order_position ASC, s.name ASC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function manageSystemsStats(array $systems): array
{
    $active = 0;
    foreach ($systems as $system) {
        if (!empty($system['is_active'])) $active++;
    }

    return ['total' => count($systems), 'active' => $active, 'inactive' => count($systems) - $active];
}

function manageSystemsValidUrl(string $url): bool
{
    $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));

    return $url !== ''
        && filter_var($url, FILTER_VALIDATE_URL) !== false
        && in_array($scheme, ['http', 'https'], true);
}

function manageSystemsLogoUrl(string $base_url, ?string $path): ?string
{
    $path = str_replace('\\', '/', trim((string)$path));
    if (!preg_match('#^uploads/systems/[a-zA-Z0-9._-]+$#', $path)) return null;

    return rtrim($base_url, '/') . '/' . $path;
}

function manageSystemsResult(string $type, string $message): array
{
    return ['type' => $type, 'message' => $message];
}
