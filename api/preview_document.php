<?php
/**
 * API para pré-visualizar conteúdo do documento
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/../includes/Document.php';

Session::start();

// Definir header JSON e evitar cache
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

try {
    // Verificar autenticação
    if (!Session::isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Não autenticado']);
        exit;
    }

    // Verificar permissão
    $user_id = Session::getUserId();
    $stmt_user = $conn->prepare("SELECT access_level FROM users WHERE id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result = $stmt_user->get_result();
    $user = $result->fetch_assoc();
    $stmt_user->close();

    if (!$user || ($user['access_level'] !== 'admin' && $user['access_level'] !== 'viewer')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Sem permissão']);
        exit;
    }

    $doc_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($doc_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit;
    }

    // Buscar documento
    $stmt = $conn->prepare("SELECT id, original_name, filename, extracted_text FROM documents WHERE id = ?");
    $stmt->bind_param("i", $doc_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $document = $result->fetch_assoc();
    $stmt->close();

    if (!$document) {
        echo json_encode(['success' => false, 'message' => 'Documento não encontrado']);
        exit;
    }

    // Carregar conteúdo
    $content = '';
    $filename = $document['original_name'];

    // Se é PDF, tentar carregar do campo extracted_text (texto extraído)
    if (strpos($filename, '.pdf') !== false) {
        if (!empty($document['extracted_text'])) {
            // Conteúdo já foi extraído
            $content = $document['extracted_text'];
        } else {
            // Tentar localizar o arquivo no caminho salvo ou em fallback
            $filepath = appResolveDocumentPath($document['filepath'] ?? '', $document['filename'] ?? '');
            if ($filepath !== null) {
                // PDFs precisam ser extraídos (usar biblioteca se disponível)
                $content = "[PDF - Arquivo não foi processado ainda. Clique em 'Processar PDFs Agora' na tela de aprovação para extrair o texto]";
            } else {
                $content = "[PDF - Arquivo não encontrado no servidor]";
            }
        }
    } else {
        // É arquivo de texto, carregar conteúdo do banco (campo extracted_text)
        if (!empty($document['extracted_text'])) {
            $content = $document['extracted_text'];
            // Limitar a 50KB para visualização
            if (strlen($content) > 51200) {
                $truncated_size = round(strlen($content) / 1024, 2);
                $content = substr($content, 0, 51200) . "\n\n[... Arquivo truncado para visualização. Tamanho total: " . $truncated_size . " KB]";
            }
        } else {
            // Tentar ler do arquivo localizado dinamicamente
            $filepath = appResolveDocumentPath($document['filepath'] ?? '', $document['filename'] ?? '');
            if ($filepath !== null) {
                $content = file_get_contents($filepath);
                // Limitar a 50KB para visualização
                if (strlen($content) > 51200) {
                    $truncated_size = round(strlen($content) / 1024, 2);
                    $content = substr($content, 0, 51200) . "\n\n[... Arquivo truncado para visualização. Tamanho total: " . $truncated_size . " KB]";
                }
            } else {
                $content = "[Arquivo não encontrado no servidor]";
            }
        }
    }

    // Validar conteúdo
    if (empty($content)) {
        $content = "[Arquivo sem conteúdo]";
    }

    echo json_encode([
        'success' => true,
        'content' => htmlspecialchars($content),
        'filename' => htmlspecialchars($filename)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar documento',
        'error' => $e->getMessage()
    ]);
}
?>
