<?php

function profileSidebarItems(mysqli $conn): array
{
    $result = $conn->query("
        SELECT id, title, url, icon, menu_type
        FROM custom_menus
        WHERE is_active = TRUE
        ORDER BY order_position ASC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function profileFindUser(mysqli $conn, int $user_id): ?array
{
    $stmt = $conn->prepare("
        SELECT
            usr.id,
            usr.first_name,
            usr.last_name,
            usr.email,
            usr.access_level,
            usr.created_at,
            usr.is_active,
            usr.email_verified,
            usr.profile_photo,
            usr.id_funcao,
            funcao.descricao,
            usr.wpp_corporativo,
            usr.id_filial,
            fi.nome_filial
        FROM users usr
        LEFT JOIN funcao ON funcao.id = usr.id_funcao
        LEFT JOIN filiais fi ON fi.id = usr.id_filial
        WHERE usr.id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $user ?: null;
}

function profilePhotoCsrfToken(): string
{
    if (empty($_SESSION['profile_photo_csrf'])) {
        $_SESSION['profile_photo_csrf'] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION['profile_photo_csrf'];
}

function profilePhotoFeedback(): ?array
{
    $feedback = $_SESSION['profile_photo_feedback'] ?? null;
    unset($_SESSION['profile_photo_feedback']);

    return is_array($feedback) ? $feedback : null;
}

function profilePhotoUrl(string $base_url, ?string $path): ?string
{
    $path = str_replace('\\', '/', trim((string)$path));

    if (!preg_match('#^uploads/profile/[a-zA-Z0-9._-]+$#', $path)) {
        return null;
    }

    return rtrim($base_url, '/') . '/' . $path;
}

function profileHandlePhotoRequest(
    mysqli $conn,
    int $user_id,
    array $post,
    array $files,
    string $project_root
): array {
    $submitted_token = $post['csrf_token'] ?? '';
    $session_token = $_SESSION['profile_photo_csrf'] ?? '';

    if (!is_string($submitted_token)
        || !is_string($session_token)
        || $session_token === ''
        || !hash_equals($session_token, $submitted_token)
    ) {
        return ['type' => 'error', 'message' => 'A sessão expirou. Atualize a página e tente novamente.'];
    }

    $current_photo = profileCurrentPhoto($conn, $user_id);
    $action = (string)($post['profile_photo_action'] ?? 'upload');

    if ($action === 'remove') {
        return profileRemovePhoto($conn, $user_id, $current_photo, $project_root);
    }

    return profileUploadPhoto(
        $conn,
        $user_id,
        $current_photo,
        $files['profile_photo'] ?? [],
        $project_root
    );
}

function profileCurrentPhoto(mysqli $conn, int $user_id): ?string
{
    $stmt = $conn->prepare('SELECT profile_photo FROM users WHERE id = ? LIMIT 1');

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return !empty($row['profile_photo']) ? (string)$row['profile_photo'] : null;
}

function profileUploadPhoto(
    mysqli $conn,
    int $user_id,
    ?string $current_photo,
    array $file,
    string $project_root
): array {
    $upload_error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($upload_error === UPLOAD_ERR_NO_FILE) {
        return ['type' => 'error', 'message' => 'Selecione uma imagem antes de salvar.'];
    }

    if ($upload_error !== UPLOAD_ERR_OK) {
        return ['type' => 'error', 'message' => 'Não foi possível receber a imagem. Tente novamente.'];
    }

    $size = (int)($file['size'] ?? 0);
    $tmp_name = (string)($file['tmp_name'] ?? '');

    if ($size <= 0 || $size > 2 * 1024 * 1024) {
        return ['type' => 'error', 'message' => 'A imagem deve ter no máximo 2 MB.'];
    }

    if ($tmp_name === '' || !is_uploaded_file($tmp_name)) {
        return ['type' => 'error', 'message' => 'O arquivo enviado não é válido.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp_name);
    $allowed_types = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed_types[$mime])) {
        return ['type' => 'error', 'message' => 'Envie uma imagem JPG, PNG ou WebP.'];
    }

    $image_info = @getimagesize($tmp_name);

    if ($image_info === false) {
        return ['type' => 'error', 'message' => 'O conteúdo do arquivo não é uma imagem válida.'];
    }

    [$width, $height] = $image_info;
    if ($width < 100 || $height < 100 || $width > 6000 || $height > 6000) {
        return ['type' => 'error', 'message' => 'Use uma imagem entre 100 x 100 e 6000 x 6000 pixels.'];
    }

    $upload_dir = rtrim($project_root, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profile';

    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
        return ['type' => 'error', 'message' => 'Não foi possível preparar a pasta das imagens.'];
    }

    $filename = sprintf('user-%d-%s.%s', $user_id, bin2hex(random_bytes(12)), $allowed_types[$mime]);
    $destination = $upload_dir . DIRECTORY_SEPARATOR . $filename;
    $relative_path = 'uploads/profile/' . $filename;

    if (!move_uploaded_file($tmp_name, $destination)) {
        return ['type' => 'error', 'message' => 'Não foi possível salvar a imagem enviada.'];
    }

    $stmt = $conn->prepare('UPDATE users SET profile_photo = ? WHERE id = ?');

    if (!$stmt) {
        @unlink($destination);
        return ['type' => 'error', 'message' => 'Não foi possível atualizar a foto do perfil.'];
    }

    $stmt->bind_param('si', $relative_path, $user_id);
    $updated = $stmt->execute();
    $stmt->close();

    if (!$updated) {
        @unlink($destination);
        return ['type' => 'error', 'message' => 'Não foi possível atualizar a foto do perfil.'];
    }

    profileDeleteStoredPhoto($current_photo, $project_root);

    return ['type' => 'success', 'message' => 'Foto de perfil atualizada com sucesso.'];
}

function profileRemovePhoto(mysqli $conn, int $user_id, ?string $current_photo, string $project_root): array
{
    $stmt = $conn->prepare('UPDATE users SET profile_photo = NULL WHERE id = ?');

    if (!$stmt) {
        return ['type' => 'error', 'message' => 'Não foi possível remover a foto do perfil.'];
    }

    $stmt->bind_param('i', $user_id);
    $removed = $stmt->execute();
    $stmt->close();

    if (!$removed) {
        return ['type' => 'error', 'message' => 'Não foi possível remover a foto do perfil.'];
    }

    profileDeleteStoredPhoto($current_photo, $project_root);

    return ['type' => 'success', 'message' => 'Foto de perfil removida.'];
}

function profileDeleteStoredPhoto(?string $relative_path, string $project_root): void
{
    $relative_path = str_replace('\\', '/', trim((string)$relative_path));

    if (!preg_match('#^uploads/profile/[a-zA-Z0-9._-]+$#', $relative_path)) {
        return;
    }

    $absolute_path = rtrim($project_root, '/\\') . DIRECTORY_SEPARATOR
        . str_replace('/', DIRECTORY_SEPARATOR, $relative_path);

    if (is_file($absolute_path)) {
        @unlink($absolute_path);
    }
}

function formatProfileText(?string $text): string
{
    $text = trim((string)$text);

    if ($text === '') {
        return '-';
    }

    $text = mb_convert_case(mb_strtolower($text, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

    foreach (['Da', 'De', 'Do', 'Das', 'Dos', 'E'] as $word) {
        $text = preg_replace('/\b' . $word . '\b/u', mb_strtolower($word, 'UTF-8'), $text);
    }

    foreach (['Rh', 'Ti', 'Mcm'] as $word) {
        $text = preg_replace('/\b' . $word . '\b/u', strtoupper($word), $text);
    }

    return $text;
}

function formatProfileName(string $text): string
{
    return formatProfileText($text);
}

function formatProfileRole(?string $text): string
{
    return formatProfileText($text);
}

function formatProfileWhatsApp(?string $phone): string
{
    $original = trim((string)$phone);

    if ($original === '') {
        return '-';
    }

    $digits = preg_replace('/\D+/', '', $original);

    if ((strlen($digits) === 12 || strlen($digits) === 13) && str_starts_with($digits, '55')) {
        $digits = substr($digits, 2);
    }

    if (strlen($digits) === 11) {
        return sprintf(
            '(%s) %s %s-%s',
            substr($digits, 0, 2),
            substr($digits, 2, 1),
            substr($digits, 3, 4),
            substr($digits, 7, 4)
        );
    }

    if (strlen($digits) === 10) {
        return sprintf(
            '(%s) %s-%s',
            substr($digits, 0, 2),
            substr($digits, 2, 4),
            substr($digits, 6, 4)
        );
    }

    return $original;
}

function formatProfileDate(?string $date): string
{
    if (!$date) {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp === false ? '-' : date('d/m/Y H:i', $timestamp);
}
