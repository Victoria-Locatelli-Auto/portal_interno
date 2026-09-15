<?php
/**
 * Classe de Busca - Pesquisa em documentos de texto e PDFs
 * Usa SimplePDFParser para extrair texto de PDFs
 */

require_once __DIR__ . '/PDFParser.php';

class SearchEngine {
    private $conn;
    private $user_id;
    private $search_all = true; // Padrão: busca em todos os documentos
    
    public function __construct($conn, $user_id, $search_all = true) {
        $this->conn = $conn;
        $this->user_id = $user_id;
        $this->search_all = $search_all;
    }
    
    /**
     * Define se a busca deve ser em todos os documentos ou apenas do usuário
     * @param bool $search_all true para buscar em todos, false apenas no usuário
     */
    public function setSearchAll($search_all) {
        $this->search_all = $search_all;
    }
    
    /**
     * Busca documentos por query com extração dinâmica de PDFs
     * Busca em TODAS as linhas da base de dados (original_name e extracted_text)
     * @param string $query Termo de busca
     * @return array Documentos encontrados com score de relevância
     */
    public function searchWithDynamicExtraction($query) {
        if (empty($query)) {
            return [];
        }
        
        // Busca em TODA a base de dados da tabela documents
        // Sem filtro de status - consulta todas as vezes
        $stmt = $this->conn->prepare(
            "SELECT id, user_id, filename, original_name, filepath, extracted_text, status, uploaded_at 
             FROM documents 
             WHERE original_name LIKE ? OR extracted_text LIKE ?
             ORDER BY uploaded_at DESC"
        );
        
        $search_pattern = '%' . $query . '%';
        $stmt->bind_param("ss", $search_pattern, $search_pattern);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $documents = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        $query_lower = strtolower($query);
        $search_results = [];
        
        foreach ($documents as $document) {
            $score = 0;
            $matched_text = '';
            $content_to_search = '';
            
            // Busca no nome do arquivo (peso 3 - maior relevância)
            if (stripos($document['original_name'], $query) !== false) {
                $score += 30;
            }
            
            // Determinar conteúdo a buscar
            if (!empty($document['extracted_text'])) {
                // Usar texto já extraído
                $content_to_search = $document['extracted_text'];
            } else if (strpos(strtolower($document['filename']), '.pdf') !== false) {
                // Tentar extrair do PDF em tempo real
                $resolved_path = appResolveDocumentPath($document['filepath'] ?? '', $document['filename'] ?? '');
                if ($resolved_path !== null) {
                    $content_to_search = $this->extractPDFText($resolved_path);
                }
            }
            
            // Busca em conteúdo (peso 2)
            if (!empty($content_to_search)) {
                $content_lower = strtolower($content_to_search);
                if (strpos($content_lower, $query_lower) !== false) {
                    $score += 20;
                    $matched_text = $this->extractSnippet($content_to_search, $query);
                }
            }
            
            // Se encontrou match (score > 0), adiciona aos resultados
            if ($score > 0) {
                $search_results[] = [
                    'document' => $document,
                    'score' => $score,
                    'preview' => $matched_text
                ];
            }
        }
        
        // Ordena por score (relevância) - maior primeiro
        usort($search_results, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        return $search_results;
    }
    
    /**
     * Método compatível com código existente
     * Usa extração dinâmica de PDFs
     * @param string $query Termo de busca
     * @return array Documentos encontrados com score de relevância
     */
    public function search($query) {
        return $this->searchWithDynamicExtraction($query);
    }
    
    /**
     * Busca documentos por query
    
    /**
     * Extrai texto de um PDF
     * @param string $pdf_path Caminho do arquivo PDF
     * @return string Texto extraído
     */
    private function extractPDFText($pdf_path) {
        if (!file_exists($pdf_path)) {
            return '';
        }
        
        // Tenta primeiro método
        $text = SimplePDFParser::extractText($pdf_path);
        
        // Fallback para método alternativo
        if (empty($text)) {
            $text = SimplePDFParser::extractTextAlternative($pdf_path);
        }
        
        return $text ?? '';
    }
    
    /**
     * Extrai um snippet (trecho) do texto contendo o termo buscado
     * @param string $text Texto completo
     * @param string $query Termo buscado
     * @return string Snippet com contexto
     */
    private function extractSnippet($text, $query) {
        $query_lower = strtolower($query);
        $pos = strpos(strtolower($text), $query_lower);
        
        if ($pos === false) {
            // Se não encontrar, retorna início
            return substr($text, 0, 150) . '...';
        }
        
        // Extrai 150 caracteres antes e 150 depois
        $start = max(0, $pos - 75);
        $length = 300;
        
        $snippet = substr($text, $start, $length);
        
        // Remove quebras de linha
        $snippet = preg_replace('/\s+/', ' ', $snippet);
        
        // Adiciona ellipsis se não começar no início
        if ($start > 0) {
            $snippet = '...' . $snippet;
        }
        if ($start + $length < strlen($text)) {
            $snippet = $snippet . '...';
        }
        
        return trim($snippet);
    }
    
    /**
     * Busca por palavra-chave em PDFs especificamente
     * @param string $query Termo de busca
     * @param int $doc_id ID do documento
     * @return array Ocorrências encontradas
     */
    public function searchInPDF($query, $doc_id) {
        if (empty($query)) {
            return [];
        }
        
        // Busca documento
        $stmt = $this->conn->prepare(
            "SELECT filepath, filename FROM documents WHERE id = ? AND user_id = ?"
        );
        $stmt->bind_param("ii", $doc_id, $this->user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $document = $result->fetch_assoc();
        
        if (!$document || strpos($document['filename'], '.pdf') === false) {
            return [];
        }
        
        $resolved_path = appResolveDocumentPath($document['filepath'] ?? '', $document['filename'] ?? '');

        if ($resolved_path === null) {
            return [];
        }
        
        // Extrai texto do PDF
        $pdf_text = $this->extractPDFText($resolved_path);
        
        if (empty($pdf_text)) {
            return [];
        }
        
        // Procura por todas as ocorrências
        $query_lower = strtolower($query);
        $pdf_text_lower = strtolower($pdf_text);
        
        $occurrences = [];
        $offset = 0;
        
        while (($pos = strpos($pdf_text_lower, $query_lower, $offset)) !== false) {
            $snippet = $this->extractSnippet(substr($pdf_text, $pos), $query);
            $occurrences[] = [
                'position' => $pos,
                'snippet' => $snippet
            ];
            $offset = $pos + 1;
        }
        
        return $occurrences;
    }
}
?>
