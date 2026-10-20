<?php

function manageBannersCurrentUser(mysqli $conn, int $user_id): ?array
{
    $stmt = $conn->prepare('SELECT id, access_level FROM users WHERE id = ? LIMIT 1');
    if (!$stmt) return null;
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $user ?: null;
}

function manageBannersSidebarItems(mysqli $conn): array
{
    $result = $conn->query('SELECT id, title, url, icon, menu_type FROM custom_menus WHERE is_active = TRUE ORDER BY order_position ASC');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function manageBannersCsrfToken(): string
{
    if (empty($_SESSION['manage_banners_csrf'])) {
        $_SESSION['manage_banners_csrf'] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION['manage_banners_csrf'];
}

function manageBannersHandleAction(mysqli $conn, array $post, array $files, int $user_id, string $project_root): array
{
    $submitted = (string)($post['csrf_token'] ?? '');
    $stored = (string)($_SESSION['manage_banners_csrf'] ?? '');
    if ($stored === '' || !hash_equals($stored, $submitted)) {
        return manageBannersResult('error', 'A sessão do formulário expirou. Atualize a página e tente novamente.');
    }

    $action = (string)($post['action'] ?? '');
    if ($action === 'add_banner') {
        return manageBannersAdd($conn, $post, $files['banner_image'] ?? [], $user_id, $project_root);
    }
    if ($action === 'toggle_banner') {
        return manageBannersToggle($conn, (int)($post['banner_id'] ?? 0));
    }
    if ($action === 'delete_banner') {
        return manageBannersDelete($conn, (int)($post['banner_id'] ?? 0), $project_root);
    }
    return manageBannersResult('error', 'Ação inválida.');
}

function manageBannersAdd(mysqli $conn, array $post, array $file, int $user_id, string $project_root): array
{
    $title = trim((string)($post['title'] ?? ''));
    $alt_text = trim((string)($post['alt_text'] ?? ''));
    $target_url = trim((string)($post['target_url'] ?? ''));
    $starts_at = manageBannersDateTime((string)($post['starts_at'] ?? ''));
    $ends_at = manageBannersDateTime((string)($post['ends_at'] ?? ''));
    $is_active = isset($post['is_active']) ? 1 : 0;

    if (mb_strlen($title, 'UTF-8') > 160) return manageBannersResult('error', 'O título deve ter no máximo 160 caracteres.');
    if ($alt_text === '' || mb_strlen($alt_text, 'UTF-8') > 255) return manageBannersResult('error', 'Informe uma descrição da imagem com até 255 caracteres.');
    if (!manageBannersValidUrl($target_url)) return manageBannersResult('error', 'Informe um link HTTP ou HTTPS válido.');
    if (($post['starts_at'] ?? '') !== '' && $starts_at === null) return manageBannersResult('error', 'A data inicial não é válida.');
    if (($post['ends_at'] ?? '') !== '' && $ends_at === null) return manageBannersResult('error', 'A data final não é válida.');
    if ($starts_at !== null && $ends_at !== null && strtotime($ends_at) <= strtotime($starts_at)) return manageBannersResult('error', 'A data final deve ser posterior à data inicial.');

    $upload = manageBannersStoreImage($file, $project_root);
    if (!$upload['success']) return manageBannersResult('error', $upload['message']);

    $position_result = $conn->query('SELECT COALESCE(MAX(order_position), 0) + 1 AS next_position FROM home_banners');
    $position = (int)(($position_result ? $position_result->fetch_assoc()['next_position'] : 1) ?? 1);
    $stmt = $conn->prepare('INSERT INTO home_banners (title, alt_text, image_path, target_url, starts_at, ends_at, is_active, order_position, created_by) VALUES (NULLIF(?, \'\'), ?, ?, NULLIF(?, \'\'), ?, ?, ?, ?, ?)');
    if (!$stmt) {
        manageBannersDeleteStoredImage($upload['path'], $project_root);
        return manageBannersResult('error', 'Não foi possível preparar o cadastro do banner.');
    }
    $stmt->bind_param('ssssssiii', $title, $alt_text, $upload['path'], $target_url, $starts_at, $ends_at, $is_active, $position, $user_id);
    $saved = $stmt->execute();
    $stmt->close();
    if (!$saved) {
        manageBannersDeleteStoredImage($upload['path'], $project_root);
        return manageBannersResult('error', 'Não foi possível cadastrar o banner.');
    }
    return manageBannersResult('success', 'Banner cadastrado com sucesso.');
}

function manageBannersStoreImage(array $file, string $project_root): array
{
    if ((int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return ['success' => false, 'message' => 'Selecione uma imagem para o banner.'];
    if ((int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return ['success' => false, 'message' => 'Não foi possível receber a imagem.'];
    $size = (int)($file['size'] ?? 0);
    $tmp_name = (string)($file['tmp_name'] ?? '');
    if ($size <= 0 || $size > 5 * 1024 * 1024) return ['success' => false, 'message' => 'A imagem deve ter no máximo 5 MB.'];
    if ($tmp_name === '' || !is_uploaded_file($tmp_name)) return ['success' => false, 'message' => 'O arquivo enviado não é válido.'];

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp_name);
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($types[$mime])) return ['success' => false, 'message' => 'Envie uma imagem JPG, PNG ou WebP.'];
    $image_info = @getimagesize($tmp_name);
    if ($image_info === false) return ['success' => false, 'message' => 'O conteúdo enviado não é uma imagem válida.'];
    [$width, $height] = $image_info;
    if ($width < 800 || $height < 200 || $width > 7000 || $height > 4000) return ['success' => false, 'message' => 'Use uma imagem entre 800 x 200 e 7000 x 4000 pixels.'];

    $upload_dir = rtrim($project_root, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'banners';
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) return ['success' => false, 'message' => 'Não foi possível preparar a pasta dos banners.'];
    $filename = 'banner-' . date('YmdHis') . '-' . bin2hex(random_bytes(10)) . '.' . $types[$mime];
    $destination = $upload_dir . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($tmp_name, $destination)) return ['success' => false, 'message' => 'Não foi possível salvar a imagem.'];
    return ['success' => true, 'path' => 'uploads/banners/' . $filename];
}

function manageBannersToggle(mysqli $conn, int $banner_id): array
{
    if ($banner_id <= 0 || !manageBannersExists($conn, $banner_id)) return manageBannersResult('error', 'Banner não encontrado.');
    $stmt = $conn->prepare('UPDATE home_banners SET is_active = NOT is_active WHERE id = ?');
    if (!$stmt) return manageBannersResult('error', 'Não foi possível preparar a alteração.');
    $stmt->bind_param('i', $banner_id);
    $success = $stmt->execute();
    $stmt->close();
    return $success ? manageBannersResult('success', 'Status do banner atualizado.') : manageBannersResult('error', 'Não foi possível alterar o banner.');
}

function manageBannersDelete(mysqli $conn, int $banner_id, string $project_root): array
{
    $banner = manageBannersFind($conn, $banner_id);
    if (!$banner) return manageBannersResult('error', 'Banner não encontrado.');
    $stmt = $conn->prepare('DELETE FROM home_banners WHERE id = ?');
    if (!$stmt) return manageBannersResult('error', 'Não foi possível preparar a exclusão.');
    $stmt->bind_param('i', $banner_id);
    $success = $stmt->execute();
    $stmt->close();
    if (!$success) return manageBannersResult('error', 'Não foi possível excluir o banner.');
    manageBannersDeleteStoredImage((string)$banner['image_path'], $project_root);
    return manageBannersResult('success', 'Banner excluído com sucesso.');
}

function manageBannersDeleteStoredImage(?string $path, string $project_root): void
{
    $path = str_replace('\\', '/', trim((string)$path));
    if (!preg_match('#^uploads/banners/[a-zA-Z0-9._-]+$#', $path)) return;
    $absolute = rtrim($project_root, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    if (is_file($absolute)) @unlink($absolute);
}

function manageBannersList(mysqli $conn): array
{
    $result = $conn->query('SELECT b.*, u.username AS created_by_name FROM home_banners b LEFT JOIN users u ON u.id = b.created_by ORDER BY b.order_position ASC, b.created_at DESC');
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function manageBannersStats(array $banners): array
{
    $stats = ['total' => count($banners), 'active' => 0, 'scheduled' => 0, 'expired' => 0];
    $now = time();
    foreach ($banners as $banner) {
        if (!empty($banner['is_active'])) $stats['active']++;
        if (!empty($banner['starts_at']) && strtotime($banner['starts_at']) > $now) $stats['scheduled']++;
        if (!empty($banner['ends_at']) && strtotime($banner['ends_at']) < $now) $stats['expired']++;
    }
    return $stats;
}

function manageBannersFind(mysqli $conn, int $banner_id): ?array
{
    if ($banner_id <= 0) return null;
    $stmt = $conn->prepare('SELECT id, image_path FROM home_banners WHERE id = ? LIMIT 1');
    if (!$stmt) return null;
    $stmt->bind_param('i', $banner_id);
    $stmt->execute();
    $banner = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $banner ?: null;
}

function manageBannersExists(mysqli $conn, int $banner_id): bool
{
    return manageBannersFind($conn, $banner_id) !== null;
}

function manageBannersDateTime(string $value): ?string
{
    $value = trim($value);
    if ($value === '') return null;
    $date = DateTime::createFromFormat('Y-m-d\\TH:i', $value);
    return $date && $date->format('Y-m-d\\TH:i') === $value ? $date->format('Y-m-d H:i:s') : null;
}

function manageBannersValidUrl(string $url): bool
{
    if ($url === '') return true;
    $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
    return filter_var($url, FILTER_VALIDATE_URL) !== false && in_array($scheme, ['http', 'https'], true);
}

function manageBannersImageUrl(string $base_url, ?string $path): ?string
{
    $path = str_replace('\\', '/', trim((string)$path));
    if (!preg_match('#^uploads/banners/[a-zA-Z0-9._-]+$#', $path)) return null;
    return rtrim($base_url, '/') . '/' . $path;
}

function manageBannersFormatDate(?string $date): string
{
    if (empty($date)) return 'Sem limite';
    $timestamp = strtotime($date);
    return $timestamp ? date('d/m/Y H:i', $timestamp) : '-';
}

function manageBannersResult(string $type, string $message): array
{
    return ['type' => $type, 'message' => $message];
}
