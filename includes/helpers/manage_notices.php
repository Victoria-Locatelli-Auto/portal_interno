<?php

function manageNoticesCurrentUser(mysqli $conn, int $user_id): ?array
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

function manageNoticesCanAccess(?array $user): bool
{
    return $user && in_array($user['access_level'] ?? '', ['admin', 'editor', 'RH'], true);
}

function manageNoticesCsrfToken(): string
{
    if (empty($_SESSION['manage_notices_csrf'])) {
        $_SESSION['manage_notices_csrf'] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION['manage_notices_csrf'];
}

function manageNoticesSidebarItems(mysqli $conn): array
{
    $result = $conn->query("
        SELECT id, title, url, icon, menu_type
        FROM custom_menus
        WHERE is_active = TRUE
        ORDER BY order_position ASC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function manageNoticesHandleAction(mysqli $conn, array $post): array
{
    $submitted_token = $post['csrf_token'] ?? '';
    $session_token = $_SESSION['manage_notices_csrf'] ?? '';

    if (!is_string($submitted_token)
        || !is_string($session_token)
        || $session_token === ''
        || !hash_equals($session_token, $submitted_token)
    ) {
        return manageNoticesResult('', 'A sessão expirou. Atualize a página e tente novamente.');
    }

    $action = (string)($post['action'] ?? '');

    if ($action === 'add_notice') {
        return manageNoticesAdd($conn, $post);
    }

    if ($action === 'edit_notice') {
        return manageNoticesEdit($conn, $post);
    }

    if ($action === 'delete_notice') {
        return manageNoticesDelete($conn, (int)($post['notice_id'] ?? 0));
    }

    if ($action === 'toggle_notice') {
        return manageNoticesToggle($conn, (int)($post['notice_id'] ?? 0));
    }

    return manageNoticesResult('', '');
}

function manageNoticesAdd(mysqli $conn, array $post): array
{
    $title = trim((string)($post['title'] ?? ''));
    $description = trim((string)($post['description'] ?? ''));
    $content = trim((string)($post['content'] ?? ''));

    $validation_error = manageNoticesValidateFields($title, $description, $content);

    if ($validation_error !== '') {
        return manageNoticesResult('', $validation_error);
    }

    $stmt = $conn->prepare('INSERT INTO notices (title, description, content, is_active) VALUES (?, ?, ?, TRUE)');

    if (!$stmt) {
        return manageNoticesResult('', 'Não foi possível preparar o novo aviso.');
    }

    $stmt->bind_param('sss', $title, $description, $content);

    if (!$stmt->execute()) {
        return manageNoticesResult('', 'Não foi possível criar o aviso. Tente novamente.');
    }

    $stmt->close();

    return manageNoticesResult('Aviso adicionado com sucesso.', '');
}

function manageNoticesEdit(mysqli $conn, array $post): array
{
    $notice_id = (int)($post['notice_id'] ?? 0);
    $title = trim((string)($post['title'] ?? ''));
    $description = trim((string)($post['description'] ?? ''));
    $content = trim((string)($post['content'] ?? ''));

    if ($notice_id <= 0 || !manageNoticesExists($conn, $notice_id)) {
        return manageNoticesResult('', 'Aviso não encontrado.');
    }

    $validation_error = manageNoticesValidateFields($title, $description, $content);

    if ($validation_error !== '') {
        return manageNoticesResult('', $validation_error);
    }

    $stmt = $conn->prepare('UPDATE notices SET title = ?, description = ?, content = ? WHERE id = ?');

    if (!$stmt) {
        return manageNoticesResult('', 'Não foi possível preparar a atualização.');
    }

    $stmt->bind_param('sssi', $title, $description, $content, $notice_id);

    if (!$stmt->execute()) {
        return manageNoticesResult('', 'Não foi possível atualizar o aviso. Tente novamente.');
    }

    $stmt->close();

    return manageNoticesResult('Aviso atualizado com sucesso.', '');
}

function manageNoticesDelete(mysqli $conn, int $notice_id): array
{
    if ($notice_id <= 0 || !manageNoticesExists($conn, $notice_id)) {
        return manageNoticesResult('', 'Aviso não encontrado.');
    }

    $stmt = $conn->prepare('DELETE FROM notices WHERE id = ?');

    if (!$stmt) {
        return manageNoticesResult('', 'Não foi possível preparar a exclusão.');
    }

    $stmt->bind_param('i', $notice_id);

    if (!$stmt->execute()) {
        return manageNoticesResult('', 'Não foi possível remover o aviso. Tente novamente.');
    }

    $stmt->close();

    return manageNoticesResult('Aviso removido com sucesso.', '');
}

function manageNoticesToggle(mysqli $conn, int $notice_id): array
{
    if ($notice_id <= 0 || !manageNoticesExists($conn, $notice_id)) {
        return manageNoticesResult('', 'Aviso não encontrado.');
    }

    $stmt = $conn->prepare('UPDATE notices SET is_active = NOT is_active WHERE id = ?');

    if (!$stmt) {
        return manageNoticesResult('', 'Não foi possível preparar a alteração.');
    }

    $stmt->bind_param('i', $notice_id);

    if (!$stmt->execute()) {
        return manageNoticesResult('', 'Não foi possível alterar o status do aviso.');
    }

    $stmt->close();

    return manageNoticesResult('Status do aviso alterado.', '');
}

function manageNoticesValidateFields(string $title, string $description, string $content): string
{
    if ($title === '' || $description === '') {
        return 'Título e descrição são obrigatórios.';
    }

    if (mb_strlen($title, 'UTF-8') > 255) {
        return 'O título deve ter no máximo 255 caracteres.';
    }

    if (mb_strlen($description, 'UTF-8') > 1000) {
        return 'A descrição deve ter no máximo 1.000 caracteres.';
    }

    if (mb_strlen($content, 'UTF-8') > 20000) {
        return 'O conteúdo completo deve ter no máximo 20.000 caracteres.';
    }

    return '';
}

function manageNoticesExists(mysqli $conn, int $notice_id): bool
{
    $stmt = $conn->prepare('SELECT id FROM notices WHERE id = ? LIMIT 1');

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $notice_id);
    $stmt->execute();
    $exists = (bool)$stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $exists;
}

function manageNoticesList(mysqli $conn): array
{
    $result = $conn->query("
        SELECT id, title, description, content, is_active, created_at
        FROM notices
        ORDER BY created_at DESC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function manageNoticesStats(array $notices): array
{
    $active = 0;

    foreach ($notices as $notice) {
        if (!empty($notice['is_active'])) {
            $active++;
        }
    }

    return [
        'total' => count($notices),
        'active' => $active,
        'inactive' => count($notices) - $active,
    ];
}

function manageNoticesExcerpt(?string $text, int $limit): string
{
    $text = (string)$text;

    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($text, 0, $limit, '...', 'UTF-8');
    }

    return strlen($text) > $limit ? substr($text, 0, max(0, $limit - 3)) . '...' : $text;
}

function manageNoticesDate(?string $date): string
{
    if (!$date) {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('d/m/Y H:i', $timestamp) : '-';
}

function manageNoticesResult(string $success, string $error): array
{
    return [
        'success' => $success,
        'error' => $error,
    ];
}
