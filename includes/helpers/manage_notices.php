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

    if ($title === '' || $description === '') {
        return manageNoticesResult('', 'Título e descrição são obrigatórios.');
    }

    $stmt = $conn->prepare('INSERT INTO notices (title, description, content, is_active) VALUES (?, ?, ?, TRUE)');

    if (!$stmt) {
        return manageNoticesResult('', 'Erro ao preparar aviso: ' . $conn->error);
    }

    $stmt->bind_param('sss', $title, $description, $content);

    if (!$stmt->execute()) {
        return manageNoticesResult('', 'Erro ao criar aviso: ' . $stmt->error);
    }

    return manageNoticesResult('Aviso adicionado com sucesso.', '');
}

function manageNoticesEdit(mysqli $conn, array $post): array
{
    $notice_id = (int)($post['notice_id'] ?? 0);
    $title = trim((string)($post['title'] ?? ''));
    $description = trim((string)($post['description'] ?? ''));
    $content = trim((string)($post['content'] ?? ''));

    if ($notice_id <= 0 || $title === '' || $description === '') {
        return manageNoticesResult('', 'Dados inválidos.');
    }

    $stmt = $conn->prepare('UPDATE notices SET title = ?, description = ?, content = ? WHERE id = ?');

    if (!$stmt) {
        return manageNoticesResult('', 'Erro ao preparar atualização: ' . $conn->error);
    }

    $stmt->bind_param('sssi', $title, $description, $content, $notice_id);

    if (!$stmt->execute()) {
        return manageNoticesResult('', 'Erro ao atualizar aviso: ' . $stmt->error);
    }

    return manageNoticesResult('Aviso atualizado com sucesso.', '');
}

function manageNoticesDelete(mysqli $conn, int $notice_id): array
{
    if ($notice_id <= 0) {
        return manageNoticesResult('', 'Aviso inválido.');
    }

    $stmt = $conn->prepare('DELETE FROM notices WHERE id = ?');

    if (!$stmt) {
        return manageNoticesResult('', 'Erro ao preparar exclusão: ' . $conn->error);
    }

    $stmt->bind_param('i', $notice_id);

    if (!$stmt->execute()) {
        return manageNoticesResult('', 'Erro ao remover aviso: ' . $stmt->error);
    }

    return manageNoticesResult('Aviso removido com sucesso.', '');
}

function manageNoticesToggle(mysqli $conn, int $notice_id): array
{
    if ($notice_id <= 0) {
        return manageNoticesResult('', 'Aviso inválido.');
    }

    $stmt = $conn->prepare('UPDATE notices SET is_active = NOT is_active WHERE id = ?');

    if (!$stmt) {
        return manageNoticesResult('', 'Erro ao preparar alteração: ' . $conn->error);
    }

    $stmt->bind_param('i', $notice_id);

    if (!$stmt->execute()) {
        return manageNoticesResult('', 'Erro ao alterar status do aviso: ' . $stmt->error);
    }

    return manageNoticesResult('Status do aviso alterado.', '');
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
