<?php

function addManualDefaultFormData(): array
{
    return [
        'document_title' => '',
        'document_content' => '',
    ];
}

function addManualSlug(string $text): string
{
    $slug = trim(mb_strtolower($text, 'UTF-8'));
    $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);

    if ($converted !== false) {
        $slug = $converted;
    }

    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim((string) $slug, '-');

    return $slug !== '' ? $slug : 'documento';
}

function addManualHandleSubmit(mysqli $conn, int $user_id, array $post): array
{
    $form_data = [
        'document_title' => trim((string)($post['document_title'] ?? '')),
        'document_content' => trim((string)($post['document_content'] ?? '')),
    ];

    if ($form_data['document_title'] === '') {
        return addManualResult('Título do documento é obrigatório', '', $form_data);
    }

    if ($form_data['document_content'] === '') {
        return addManualResult('Conteúdo do documento é obrigatório', '', $form_data);
    }

    if (strlen($form_data['document_content']) > 1000000) {
        return addManualResult('Conteúdo muito grande (máximo 1MB)', '', $form_data);
    }

    $stmt = $conn->prepare("
        INSERT INTO documents (
            user_id,
            filename,
            filepath,
            original_name,
            status,
            extracted_text,
            uploaded_at
        ) VALUES (?, ?, ?, ?, 'pendente', ?, NOW())
    ");

    if (!$stmt) {
        return addManualResult('Erro ao preparar documento: ' . $conn->error, '', $form_data);
    }

    $filename = 'manual_' . uniqid('', true) . '_' . addManualSlug($form_data['document_title']);
    $filepath = '[MANUAL]';

    $stmt->bind_param(
        'issss',
        $user_id,
        $filename,
        $filepath,
        $form_data['document_title'],
        $form_data['document_content']
    );

    if (!$stmt->execute()) {
        return addManualResult('Erro ao salvar documento: ' . $stmt->error, '', $form_data);
    }

    return addManualResult('', 'Documento adicionado com sucesso!', addManualDefaultFormData());
}

function addManualResult(string $error, string $success, array $form_data): array
{
    return [
        'error' => $error,
        'success' => $success,
        'form_data' => $form_data,
    ];
}
