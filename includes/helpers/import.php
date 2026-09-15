<?php

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

    $mime = importFileMime((string)$file['tmp_name']);
    $extension = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));

    if ($mime !== 'application/pdf' || $extension !== 'pdf') {
        return importResult('Por favor, envie apenas arquivos PDF válidos', '');
    }

    if ((int)$file['size'] > MAX_FILE_SIZE) {
        return importResult('O arquivo é muito grande. Máximo: 50MB', '');
    }

    if (!appEnsureUploadDirectory()) {
        return importResult('Diretório de upload indisponível no servidor. Contate o administrador.', '');
    }

    $filename = uniqid('doc_') . '.pdf';
    $filepath = appUploadAbsolutePath($filename);
    $stored_filepath = 'uploads/' . $filename;

    if (!move_uploaded_file((string)$file['tmp_name'], $filepath)) {
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
