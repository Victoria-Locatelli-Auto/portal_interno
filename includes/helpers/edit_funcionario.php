<?php

function editFuncionarioSidebarItems(mysqli $conn): array
{
    $result = $conn->query("
        SELECT id, title, url, icon, menu_type
        FROM custom_menus
        WHERE is_active = TRUE
        ORDER BY order_position ASC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function editFuncionarioFormatName($text) {
    $text = trim($text);
    $text = mb_strtolower($text, 'UTF-8');
    $text = mb_convert_case($text, MB_CASE_TITLE, 'UTF-8');

    $lower_words = ['Da', 'De', 'Do', 'Das', 'Dos', 'E'];
    foreach ($lower_words as $word) {
        $text = preg_replace('/\b' . $word . '\b/u', mb_strtolower($word, 'UTF-8'), $text);
    }

    return $text;
}

function editFuncionarioFormatCargo($text) {
    if (!$text) {
        return '—';
    }

    $text = trim($text);
    $text = mb_strtolower($text, 'UTF-8');
    $text = mb_convert_case($text, MB_CASE_TITLE, 'UTF-8');

    $text = str_replace(" Jr", " JR", $text);
    $text = str_replace(" Ti", " TI", $text);
    $text = str_replace(" Rh", " RH", $text);
    $text = str_replace(" Mcm", " MCM", $text);

    return $text;
}

function editFuncionarioFormatFilial($text) {
    $text = trim((string)$text);

    if ($text === '') {
        return '—';
    }

    return editFuncionarioFormatCargo($text);
}

function editFuncionarioNormalizeFilialCodigo($text) {
    return mb_strtoupper(trim((string)$text), 'UTF-8');
}

function editFuncionarioFormatWhatsApp(?string $phone): string
{
    $original = trim((string)$phone);

    if ($original === '') {
        return '';
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

function editFuncionarioPhotoUrl(string $base_url, ?string $path): ?string
{
    $path = str_replace('\\', '/', trim((string)$path));

    if (!preg_match('#^uploads/profile/[a-zA-Z0-9._-]+$#', $path)) {
        return null;
    }

    return rtrim($base_url, '/') . '/' . $path;
}

function editFuncionarioCsrfToken(): string
{
    if (empty($_SESSION['edit_funcionario_csrf'])) {
        $_SESSION['edit_funcionario_csrf'] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION['edit_funcionario_csrf'];
}

function editFuncionarioLoggedUser(mysqli $conn, int $user_id): ?array
{
    $stmt = $conn->prepare("
        SELECT id, first_name, last_name, email, access_level
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    return $user ?: null;
}

function editFuncionarioCanEdit(array $logged_user, int $logged_user_id, int $target_user_id): bool
{
    return editFuncionarioIsAdmin($logged_user) || $target_user_id === $logged_user_id;
}

function editFuncionarioIsAdmin(array $user): bool
{
    return ($user['access_level'] ?? '') === 'admin';
}

function editFuncionarioFuncoes(mysqli $conn): array
{
    $result = $conn->query("
        SELECT id, descricao
        FROM funcao
        ORDER BY descricao ASC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function editFuncionarioFiliais(mysqli $conn): array
{
    $result = $conn->query("
        SELECT id, codigo_filial, nome_filial
        FROM filiais
        WHERE ativo = 1
        ORDER BY nome_filial ASC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function editFuncionarioFindUser(mysqli $conn, int $user_id): ?array
{
    $stmt = $conn->prepare("
        SELECT
            u.id,
            u.first_name,
            u.last_name,
            u.email,
            u.access_level,
            u.id_funcao,
            u.id_filial,
            u.is_active,
            u.email_verified,
            u.created_at,
            u.profile_photo,
            f.descricao AS job_title,
            fi.codigo_filial,
            fi.nome_filial,
            u.wpp_corporativo
        FROM users u
        LEFT JOIN funcao f
            ON u.id_funcao = f.id
        LEFT JOIN filiais fi
            ON u.id_filial = fi.id
        WHERE u.id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    return $user ?: null;
}

function editFuncionarioAccessLevels(): array
{
    return ['admin', 'editor', 'viewer', 'Gestor', 'Analista', 'Coordenador', 'Marketing', 'RH'];
}

function editFuncionarioHandleSubmit(
    mysqli $conn,
    array $post,
    array $logged_user,
    int $logged_user_id,
    int $target_user_id
): array
{
    $post_user_id = (int)($post['user_id'] ?? 0);
    $is_admin = editFuncionarioIsAdmin($logged_user);

    $submitted_token = $post['csrf_token'] ?? '';
    $session_token = $_SESSION['edit_funcionario_csrf'] ?? '';

    if (!is_string($submitted_token)
        || !is_string($session_token)
        || $session_token === ''
        || !hash_equals($session_token, $submitted_token)
    ) {
        return ['success' => '', 'error' => 'A sessão expirou. Atualize a página e tente novamente.'];
    }

    if ($post_user_id <= 0 || $post_user_id !== $target_user_id) {
        return ['success' => '', 'error' => 'Funcionário inválido.'];
    }

    if (!$is_admin && $post_user_id !== $logged_user_id) {
        return ['success' => '', 'error' => 'Você não tem permissão para editar este usuário.'];
    }

    $form_data = editFuncionarioFormData($post);
    $error = editFuncionarioValidate($conn, $form_data, $is_admin, $target_user_id);

    if ($error !== '') {
        return ['success' => '', 'error' => $error];
    }

    return $is_admin
        ? editFuncionarioUpdateAsAdmin($conn, $post_user_id, $form_data)
        : editFuncionarioUpdateOwnData($conn, $post_user_id, $form_data);
}

function editFuncionarioFormData(array $post): array
{
    return [
        'first_name' => trim((string)($post['first_name'] ?? '')),
        'last_name' => trim((string)($post['last_name'] ?? '')),
        'email' => trim((string)($post['email'] ?? '')),
        'wpp_corporativo' => editFuncionarioFormatWhatsApp($post['wpp_corporativo'] ?? null),
        'id_funcao' => (int)($post['id_funcao'] ?? 0),
        'id_filial' => (int)($post['id_filial'] ?? 0),
        'access_level' => trim((string)($post['access_level'] ?? '')),
        'is_active' => isset($post['is_active']) ? 1 : 0,
        'email_verified' => isset($post['email_verified']) ? 1 : 0,
    ];
}

function editFuncionarioValidate(mysqli $conn, array $data, bool $is_admin, int $user_id): string
{
    if ($data['first_name'] === '' || $data['last_name'] === '') {
        return 'Nome e sobrenome são obrigatórios.';
    }

    if ($data['email'] === '') {
        return 'O e-mail é obrigatório.';
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return 'Informe um e-mail válido.';
    }

    if ($data['wpp_corporativo'] !== '') {
        $phone_digits = preg_replace('/\D+/', '', $data['wpp_corporativo']);
        if (!in_array(strlen($phone_digits), [10, 11], true)) {
            return 'Informe um WhatsApp com DDD válido.';
        }
    }

    if ($data['id_filial'] <= 0) {
        return 'Selecione uma filial.';
    }

    if ($data['id_funcao'] <= 0) {
        return 'Selecione uma função.';
    }

    if (!editFuncionarioExists($conn, 'filiais', $data['id_filial'], 'ativo = 1')) {
        return 'Selecione uma filial válida.';
    }

    if (!editFuncionarioExists($conn, 'funcao', $data['id_funcao'])) {
        return 'Selecione uma função válida.';
    }

    if ($is_admin && !in_array($data['access_level'], editFuncionarioAccessLevels(), true)) {
        return 'Selecione um nível de acesso válido.';
    }

    if ($is_admin && editFuncionarioEmailInUse($conn, $data['email'], $user_id)) {
        return 'Este e-mail já está vinculado a outro colaborador.';
    }

    return '';
}

function editFuncionarioEmailInUse(mysqli $conn, string $email, int $user_id): bool
{
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');

    if (!$stmt) {
        return true;
    }

    $stmt->bind_param('si', $email, $user_id);
    $stmt->execute();
    $exists = (bool)$stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $exists;
}

function editFuncionarioExists(mysqli $conn, string $table, int $id, string $extra_condition = ''): bool
{
    $allowed_tables = ['filiais', 'funcao'];

    if (!in_array($table, $allowed_tables, true)) {
        return false;
    }

    $extra_sql = $extra_condition !== '' ? ' AND ' . $extra_condition : '';
    $stmt = $conn->prepare("SELECT id FROM {$table} WHERE id = ?{$extra_sql} LIMIT 1");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();

    return (bool)$stmt->get_result()->fetch_assoc();
}

function editFuncionarioUpdateAsAdmin(mysqli $conn, int $user_id, array $data): array
{
    $stmt = $conn->prepare("
        UPDATE users
        SET
            first_name = ?,
            last_name = ?,
            email = ?,
            access_level = ?,
            wpp_corporativo = ?,
            id_funcao = ?,
            id_filial = ?,
            is_active = ?,
            email_verified = ?
        WHERE id = ?
    ");

    if (!$stmt) {
        return ['success' => '', 'error' => 'Erro ao preparar atualização: ' . $conn->error];
    }

    $stmt->bind_param(
        'sssssiiiii',
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['access_level'],
        $data['wpp_corporativo'],
        $data['id_funcao'],
        $data['id_filial'],
        $data['is_active'],
        $data['email_verified'],
        $user_id
    );

    if (!$stmt->execute()) {
        return ['success' => '', 'error' => 'Erro ao atualizar funcionário: ' . $stmt->error];
    }

    return ['success' => 'Funcionário atualizado com sucesso!', 'error' => ''];
}

function editFuncionarioUpdateOwnData(mysqli $conn, int $user_id, array $data): array
{
    $stmt = $conn->prepare("
        UPDATE users
        SET
            first_name = ?,
            last_name = ?,
            wpp_corporativo = ?,
            id_funcao = ?,
            id_filial = ?
        WHERE id = ?
    ");

    if (!$stmt) {
        return ['success' => '', 'error' => 'Erro ao preparar atualização: ' . $conn->error];
    }

    $stmt->bind_param(
        'sssiii',
        $data['first_name'],
        $data['last_name'],
        $data['wpp_corporativo'],
        $data['id_funcao'],
        $data['id_filial'],
        $user_id
    );

    if (!$stmt->execute()) {
        return ['success' => '', 'error' => 'Erro ao atualizar dados: ' . $stmt->error];
    }

    return ['success' => 'Seus dados foram atualizados com sucesso!', 'error' => ''];
}
