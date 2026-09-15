<?php
/**
 * Classe para gerenciar documentos PDF
 */

class Document {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Importa um novo documento (PDF)
     */
    public function import($user_id, $filename, $filepath, $original_name) {
        $stmt = $this->conn->prepare(
            "INSERT INTO documents (user_id, filename, filepath, original_name, status, uploaded_at) 
             VALUES (?, ?, ?, ?, 'pendente', NOW())"
        );
        
        $status = 'pendente';
        $stored_filepath = 'uploads/' . basename($filename ?: $filepath);
        $stmt->bind_param("isss", $user_id, $filename, $stored_filepath, $original_name);
        
        if ($stmt->execute()) {
            return ['success' => true, 'document_id' => $this->conn->insert_id];
        } else {
            return ['success' => false, 'message' => 'Erro ao salvar documento'];
        }
    }
    
    /**
     * Importa um novo documento de texto (sem arquivo)
     */
    public function importText($user_id, $title, $content) {
        $stmt = $this->conn->prepare(
            "INSERT INTO documents (user_id, filename, filepath, original_name, extracted_text, status, uploaded_at) 
             VALUES (?, ?, ?, ?, ?, 'concluido', NOW())"
        );
        
        $filename = 'text_' . uniqid() . '.txt';
        $filepath = '[texto direto]';  // Valor padrão para documentos de texto
        
        $stmt->bind_param("issss", $user_id, $filename, $filepath, $title, $content);
        
        if ($stmt->execute()) {
            return ['success' => true, 'document_id' => $this->conn->insert_id];
        } else {
            return ['success' => false, 'message' => 'Erro ao salvar documento de texto'];
        }
    }
    
    /**
     * Lista documentos do usuário
     */
    public function listByUser($user_id) {
        $stmt = $this->conn->prepare(
            "SELECT id, original_name, status, uploaded_at, extracted_text FROM documents 
             WHERE user_id = ? ORDER BY uploaded_at DESC"
        );
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Atualiza status do documento
     */
    public function updateStatus($document_id, $status, $extracted_text = null) {
        if ($extracted_text) {
            $stmt = $this->conn->prepare(
                "UPDATE documents SET status = ?, extracted_text = ?, processed_at = NOW() WHERE id = ?"
            );
            $stmt->bind_param("ssi", $status, $extracted_text, $document_id);
        } else {
            $stmt = $this->conn->prepare(
                "UPDATE documents SET status = ? WHERE id = ?"
            );
            $stmt->bind_param("si", $status, $document_id);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Busca documento por ID
     */
    public function getById($document_id) {
        $stmt = $this->conn->prepare(
            "SELECT id, user_id, filename, filepath, original_name, status, extracted_text, ai_analysis, uploaded_at, processed_at FROM documents WHERE id = ?"
        );
        $stmt->bind_param("i", $document_id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * Lista todos os documentos pendentes para aprovação
     */
    public function listPending() {
        $stmt = $this->conn->prepare(
            "SELECT d.id, d.user_id, d.original_name, d.status, d.uploaded_at, u.username, u.email 
             FROM documents d 
             JOIN users u ON d.user_id = u.id 
             WHERE d.status = 'pendente' 
             ORDER BY d.uploaded_at ASC"
        );
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Aprova um documento
     * PDFs vão para "processando", textos vão para "aprovado"
     */
    public function approve($document_id) {
        // Buscar documento para verificar tipo
        $doc = $this->getById($document_id);
        
        if (!$doc) {
            return false;
        }
        
        // Verificar se é PDF
        $is_pdf = (strpos($doc['original_name'], '.pdf') !== false);
        
        // PDFs: status = processando (será processado depois)
        // Textos: status = aprovado (não precisa processamento)
        $status = $is_pdf ? 'processando' : 'aprovado';
        
        $stmt = $this->conn->prepare(
            "UPDATE documents SET status = ? WHERE id = ?"
        );
        $stmt->bind_param("si", $status, $document_id);
        
        return $stmt->execute();
    }
    
    /**
     * Rejeita um documento
     */
    public function reject($document_id, $reason = null) {
        $stmt = $this->conn->prepare(
            "UPDATE documents SET status = 'rejeitado' WHERE id = ?"
        );
        $stmt->bind_param("i", $document_id);
        
        return $stmt->execute();
    }
}
?>
