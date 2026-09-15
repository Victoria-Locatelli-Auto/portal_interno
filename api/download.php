<?php
/**
 * API para download de documento
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/Document.php';

Session::start();

// Se não está autenticado
if (!Session::isAuthenticated()) {
    http_response_code(401);
    die('Não autorizado');
}

$document_id = (int)($_GET['id'] ?? 0);

if ($document_id > 0) {
    $doc = new Document($conn);
    $document = $doc->getById($document_id);
    
    // Verifica se documento pertence ao usuário
    if ($document && $document['user_id'] === Session::getUserId()) {
        // Se é documento adicionado manualmente, não tem arquivo para download
        if ($document['filepath'] === '[MANUAL]') {
            http_response_code(400);
            die('Este é um documento adicionado manualmente e não possui arquivo PDF para download. Você pode copiar o conteúdo da página de documentos.');
        }
        
        $filepath = appResolveDocumentPath($document['filepath'] ?? '', $document['filename'] ?? '');

        if ($filepath !== null) {
            // Obtém informações do arquivo
            $filename = !empty($document['original_name']) ? $document['original_name'] : basename($filepath);
            $filesize = filesize($filepath);

            // Envia headers para download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . $filesize);
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Accept-Ranges: bytes');

            // Envia arquivo
            readfile($filepath);
            exit;
        } else {
            error_log("Arquivo não encontrado para ID $document_id. Path salvo: " . ($document['filepath'] ?? '[vazio]'));

            // Se nenhum caminho funcionou
            http_response_code(404);
            die('Arquivo não encontrado no servidor. Entre em contato com o administrador.');
        }
    }
}

http_response_code(404);
die('Documento não encontrado ou acesso negado');
?>
