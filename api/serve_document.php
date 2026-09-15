<?php
/**
 * API para servir documentos com headers corretos
 * Acessa documentos através de IDs ao invés de caminhos diretos
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/Document.php';

Session::start();

// Verifica autenticação
if (!Session::isAuthenticated()) {
    header('HTTP/1.0 401 Unauthorized');
    exit('Não autorizado');
}

// Valida ID do documento
if (!isset($_GET['id'])) {
    header('HTTP/1.0 400 Bad Request');
    exit('ID do documento não fornecido');
}

$doc_id = (int)$_GET['id'];

// Busca documento no banco (aceita documentos de outros usuários se forem compartilhados)
$stmt = $conn->prepare("SELECT id, filename, filepath, original_name FROM documents WHERE id = ?");
$stmt->bind_param("i", $doc_id);
$stmt->execute();
$result = $stmt->get_result();
$document = $result->fetch_assoc();

// Valida acesso
if (!$document) {
    header('HTTP/1.0 404 Not Found');
    exit('Documento não encontrado');
}

$file_path = appResolveDocumentPath($document['filepath'] ?? '', $document['filename'] ?? '');

// Valida que o arquivo existe
if ($file_path === null) {
    header('HTTP/1.0 404 Not Found');
    exit('Arquivo não encontrado no servidor');
}

// Determina tipo de mídia
$file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
$mime_types = [
    'pdf' => 'application/pdf',
    'txt' => 'text/plain',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls' => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif'
];

$mime_type = $mime_types[$file_extension] ?? 'application/octet-stream';

// Verifica se deve fazer download ou exibir inline
$force_download = isset($_GET['download']) && $_GET['download'] === '1';

// Headers para exibir inline (não fazer download) ou forçar download
if ($force_download) {
    header('Content-Disposition: attachment; filename="' . basename($document['original_name']) . '"');
} else {
    header('Content-Disposition: inline; filename="' . basename($document['original_name']) . '"');
}

header('Content-Type: ' . $mime_type);
header('Content-Length: ' . filesize($file_path));
header('Cache-Control: public, max-age=3600');
header('Pragma: public');

// Serve o arquivo
readfile($file_path);
exit;
