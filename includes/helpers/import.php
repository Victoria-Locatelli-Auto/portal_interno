<?php

function importSidebarItems(mysqli $conn): array
{
    $result = $conn->query("
        SELECT id, title, url, icon, menu_type
        FROM custom_menus
        WHERE is_active = TRUE
        ORDER BY order_position ASC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function importCsrfToken(): string
{
    if (empty($_SESSION['import_csrf'])) {
        $_SESSION['import_csrf'] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION['import_csrf'];
}

function importMaxFileSize(): int
{
    $limits = [
        (int)MAX_FILE_SIZE,
        importIniSizeToBytes((string)ini_get('upload_max_filesize')),
        importIniSizeToBytes((string)ini_get('post_max_size')),
    ];

    $limits = array_values(array_filter($limits, static fn (int $limit): bool => $limit > 0));

    return $limits ? min($limits) : (int)MAX_FILE_SIZE;
}

function importIniSizeToBytes(string $value): int
{
    $value = trim($value);

    if ($value === '') {
        return 0;
    }

    $unit = strtolower(substr($value, -1));
    $number = (float)$value;

    return match ($unit) {
        'g' => (int)($number * 1024 * 1024 * 1024),
        'm' => (int)($number * 1024 * 1024),
        'k' => (int)($number * 1024),
        default => (int)$number,
    };
}

function importCurrentUser(mysqli $conn, int $user_id): ?array
{
    $stmt = $conn->prepare('SELECT access_level, email_verified FROM users WHERE id = ? LIMIT 1');

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    return $user ?: null;
}

function importCanAccess(string $access_level): bool
{
    return in_array($access_level, ['admin', 'editor', 'RH'], true);
}

function importMenus(mysqli $conn): array
{
    $result = $conn->query("
        SELECT id, title
        FROM custom_menus
        WHERE is_active = TRUE
          AND menu_type = 'page'
        ORDER BY order_position ASC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function importHandleRequest(mysqli $conn, int $user_id, array $post, array $files): array
{
    $submitted_token = $post['csrf_token'] ?? '';
    $session_token = $_SESSION['import_csrf'] ?? '';

    if (!is_string($submitted_token)
        || !is_string($session_token)
        || $session_token === ''
        || !hash_equals($session_token, $submitted_token)
    ) {
        return importResult('A sessão expirou. Atualize a página e tente novamente.', '');
    }

    if (isset($files['pdf_file'])) {
        return importHandlePdf($conn, $user_id, $post, $files['pdf_file']);
    }

    if (isset($post['text_title'], $post['text_content'])) {
        return importHandleText($conn, $user_id, $post);
    }

    return importResult('', '');
}

function importHandlePdf(mysqli $conn, int $user_id, array $post, array $file): array
{
    $upload_error = importUploadError((int)($file['error'] ?? UPLOAD_ERR_NO_FILE));

    if ($upload_error !== '') {
        return importResult($upload_error, '');
    }

    $tmp_name = (string)($file['tmp_name'] ?? '');
    $file_size = (int)($file['size'] ?? 0);

    if ($tmp_name === '' || !is_uploaded_file($tmp_name)) {
        return importResult('O arquivo enviado não é válido.', '');
    }

    if ($file_size <= 0) {
        return importResult('O arquivo enviado está vazio.', '');
    }

    $max_file_size = importMaxFileSize();

    if ($file_size > $max_file_size) {
        $max_size_mb = max(1, (int)floor($max_file_size / 1024 / 1024));
        return importResult("O arquivo é muito grande. Máximo: {$max_size_mb} MB", '');
    }

    $mime = importFileMime($tmp_name);
    $extension = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));

    if ($mime !== 'application/pdf' || $extension !== 'pdf' || !importLooksLikePdf($tmp_name)) {
        return importResult('Por favor, envie apenas arquivos PDF válidos', '');
    }

    if (!appEnsureUploadDirectory()) {
        return importResult('Diretório de upload indisponível no servidor. Contate o administrador.', '');
    }

    $filename = 'doc_' . bin2hex(random_bytes(16)) . '.pdf';
    $filepath = appUploadAbsolutePath($filename);
    $stored_filepath = 'uploads/' . $filename;

    if (!move_uploaded_file($tmp_name, $filepath)) {
        return importResult('Erro ao enviar arquivo. Tente novamente.', '');
    }

    $stmt = $conn->prepare("
        INSERT INTO documents (user_id, filename, filepath, original_name, status, uploaded_at)
        VALUES (?, ?, ?, ?, 'pendente', NOW())
    ");

    if (!$stmt) {
        @unlink($filepath);
        return importResult('Erro ao preparar documento. Tente novamente.', '');
    }

    $original_name = (string)$file['name'];
    $stmt->bind_param('isss', $user_id, $filename, $stored_filepath, $original_name);

    if (!$stmt->execute()) {
        @unlink($filepath);
        return importResult('Erro ao salvar documento. Tente novamente.', '');
    }

    $stmt->close();

    $document_id = (int)$conn->insert_id;
    importAttachMenu($conn, $document_id, (int)($post['menu_id'] ?? 0), (int)($post['submenu_id'] ?? 0));

    Session::set('pdf_uploaded', true);
    Session::set('pdf_document_id', $document_id);

    return [
        'error' => '',
        'success' => '',
        'redirect' => 'view_document.php?id=' . $document_id . '&uploaded=1',
    ];
}

function importHandleText(mysqli $conn, int $user_id, array $post): array
{
    $title = trim((string)($post['text_title'] ?? ''));
    $content = trim((string)($post['text_content'] ?? ''));

    if ($title === '') {
        return importResult('Por favor, insira um título para o documento de texto', '');
    }

    if ($content === '') {
        return importResult('Por favor, insira o conteúdo do documento de texto', '');
    }

    if (strlen($title) > 255) {
        return importResult('O título é muito longo. Máximo: 255 caracteres', '');
    }

    if (strlen($content) > 1000000) {
        return importResult('O texto é muito longo. Máximo: 1MB', '');
    }

    $document_model = new Document($conn);
    $result = $document_model->importText($user_id, $title, $content);

    if (empty($result['success'])) {
        return importResult((string)($result['message'] ?? 'Erro ao importar texto.'), '');
    }

    importAttachMenu($conn, (int)$result['document_id'], (int)($post['text_menu_id'] ?? 0), (int)($post['text_submenu_id'] ?? 0));

    return importResult('', 'Documento de texto importado com sucesso!');
}

function importAttachMenu(mysqli $conn, int $document_id, int $menu_id, int $submenu_id): void
{
    if ($menu_id <= 0) {
        return;
    }

    $menu_stmt = $conn->prepare("SELECT id FROM custom_menus WHERE id = ? AND is_active = TRUE AND menu_type = 'page' LIMIT 1");

    if (!$menu_stmt) {
        return;
    }

    $menu_stmt->bind_param('i', $menu_id);
    $menu_stmt->execute();
    $valid_menu = (bool)$menu_stmt->get_result()->fetch_assoc();
    $menu_stmt->close();

    if (!$valid_menu) {
        return;
    }

    if ($submenu_id > 0) {
        $submenu_stmt = $conn->prepare('SELECT id FROM custom_submenus WHERE id = ? AND menu_id = ? AND is_active = TRUE LIMIT 1');

        if (!$submenu_stmt) {
            return;
        }

        $submenu_stmt->bind_param('ii', $submenu_id, $menu_id);
        $submenu_stmt->execute();
        $valid_submenu = (bool)$submenu_stmt->get_result()->fetch_assoc();
        $submenu_stmt->close();

        if (!$valid_submenu) {
            $submenu_id = 0;
        }
    }

    if ($submenu_id > 0) {
        $stmt = $conn->prepare('INSERT INTO document_menus (document_id, menu_id, submenu_id) VALUES (?, ?, ?)');

        if ($stmt) {
            $stmt->bind_param('iii', $document_id, $menu_id, $submenu_id);
            $stmt->execute();
        }

        return;
    }

    $stmt = $conn->prepare('INSERT INTO document_menus (document_id, menu_id) VALUES (?, ?)');

    if ($stmt) {
        $stmt->bind_param('ii', $document_id, $menu_id);
        $stmt->execute();
    }
}

function importFileMime(string $tmp_name): string
{
    $finfo = finfo_open(FILEINFO_MIME_TYPE);

    if (!$finfo) {
        return '';
    }

    $mime = finfo_file($finfo, $tmp_name);
    finfo_close($finfo);

    return (string)$mime;
}

function importLooksLikePdf(string $tmp_name): bool
{
    $handle = @fopen($tmp_name, 'rb');

    if (!$handle) {
        return false;
    }

    $signature = fread($handle, 5);
    fclose($handle);

    return $signature === '%PDF-';
}

function importUploadError(int $error_code): string
{
    if ($error_code === UPLOAD_ERR_OK) {
        return '';
    }

    $messages = [
        UPLOAD_ERR_INI_SIZE => 'O arquivo excede o tamanho máximo permitido pelo servidor',
        UPLOAD_ERR_FORM_SIZE => 'O arquivo excede o tamanho máximo do formulário',
        UPLOAD_ERR_PARTIAL => 'O arquivo foi enviado parcialmente. Tente novamente',
        UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado',
        UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária não encontrada no servidor',
        UPLOAD_ERR_CANT_WRITE => 'Falha ao gravar arquivo no disco',
        UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão PHP',
    ];

    return $messages[$error_code] ?? ('Erro ao processar arquivo: código ' . $error_code);
}

function importResult(string $error, string $success): array
{
    return [
        'error' => $error,
        'success' => $success,
        'redirect' => '',
    ];
}
