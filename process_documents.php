<?php
/**
 * Script de Processamento de Documentos PDF
 * Extrai texto dos PDFs e atualiza o banco de dados
 * 
 * Pode ser executado via:
 * - Agendamento (cron job)
 * - PHP CLI: php process_documents.php
 * - Requisição HTTP: http://localhost/portalmcm/process_documents.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Document.php';

// Apenas inclui AI se existir configuração
if (file_exists(__DIR__ . '/config/ai.php')) {
    require_once __DIR__ . '/config/ai.php';
}

// Define como processamento em background
set_time_limit(300); // 5 minutos
ini_set('memory_limit', '512M');

// Log function
function logProcess($message) {
    $timestamp = date('Y-m-d H:i:s');
    $log_file = appUploadAbsolutePath('process.log');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
    echo "$message\n";
}

logProcess("=== Iniciando processamento de documentos ===");

// Busca documentos em processando
$result = $conn->query("
    SELECT id, filename, filepath, original_name, user_id 
    FROM documents 
    WHERE status = 'processando' 
    LIMIT 10
");

if (!$result) {
    logProcess("Erro na query: " . $conn->error);
    exit;
}

$doc = new Document($conn);
$processed_count = 0;
$error_count = 0;

while ($document = $result->fetch_assoc()) {
    $doc_id = $document['id'];
    $filepath = appResolveDocumentPath($document['filepath'] ?? '', $document['filename'] ?? '');
    $original_name = $document['original_name'];
    
    logProcess("Processando: {$original_name} (ID: {$doc_id})");
    
    try {
        // Verifica se arquivo existe
        if ($filepath === null) {
            throw new Exception("Arquivo não encontrado: " . ($document['filepath'] ?? '[vazio]'));
        }
        
        // Extrai texto do PDF
        $extracted_text = extractTextFromPDF($filepath);
        
        // Valida qualidade do texto extraído
        $text_quality = validateTextQuality($extracted_text);
        
        if (empty($extracted_text)) {
            // Se não conseguiu extrair, usa um texto padrão com info do arquivo
            logProcess("  ⚠ Texto não extraído - arquivo pode estar em formato especial");
            $extracted_text = "📄 Arquivo: " . basename($filepath) . "\n"
                . "📊 Tamanho: " . formatBytes(filesize($filepath)) . "\n"
                . "⚠️ Status: Extração de texto não disponível\n\n"
                . "Motivos possíveis:\n"
                . "• PDF está protegido/criptografado\n"
                . "• PDF foi digitalizado sem OCR (contém apenas imagens)\n"
                . "• Formato especial ou corrompido\n\n"
                . "Solução: Tente converter o PDF em https://cloudconvert.com/";
        } elseif ($text_quality < 40) {
            // Texto tem muitos caracteres especiais/corrompido
            logProcess("  ⚠ Texto extraído mas qualidade baixa ($text_quality%) - descartando");
            $extracted_text = "📄 Arquivo: " . basename($filepath) . "\n"
                . "📊 Tamanho: " . formatBytes(filesize($filepath)) . "\n"
                . "⚠️ Status: Extração falhou por qualidade baixa\n\n"
                . "O PDF parece estar:\n"
                . "• Protegido ou criptografado\n"
                . "• Digitalizando sem OCR (imagens em vez de texto)\n"
                . "• Em um formato não padrão\n\n"
                . "Solução: Converta em https://cloudconvert.com/";
        } else {
            logProcess("  ✓ Texto extraído com sucesso (" . strlen($extracted_text) . " caracteres, qualidade: $text_quality%)");
        }
        
        // Se a IA está configurada, faz análise
        $ai_analysis = null;
        if (defined('AI_ENABLED') && AI_ENABLED && !empty(trim($extracted_text))) {
            logProcess("  → Analisando com IA...");
            $ai_analysis = analyzeWithAI($extracted_text, $original_name);
            if ($ai_analysis) {
                logProcess("  ✓ Análise de IA concluída");
            }
        }
        
        // Atualiza status para concluído
        $stmt = $conn->prepare(
            "UPDATE documents SET status = 'concluido', extracted_text = ?, ai_analysis = ?, processed_at = NOW() WHERE id = ?"
        );
        $stmt->bind_param("ssi", $extracted_text, $ai_analysis, $doc_id);
        
        if ($stmt->execute()) {
            logProcess("  ✓ Documento ID {$doc_id} finalizado com sucesso");
            $processed_count++;
        } else {
            throw new Exception("Erro ao atualizar banco: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        logProcess("  ✗ ERRO: $error_message");
        
        // Atualiza status para erro
        $stmt = $conn->prepare(
            "UPDATE documents SET status = 'erro' WHERE id = ?"
        );
        $stmt->bind_param("i", $doc_id);
        $stmt->execute();
        
        $error_count++;
    }
}

logProcess("=== Processamento finalizado ===");
logProcess("Sucesso: $processed_count | Erros: $error_count");

/**
 * Formata bytes para formato legível
 */
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Valida a qualidade do texto extraído
 * Retorna percentual de qualidade (0-100)
 * Textos com muitos caracteres especiais recebem nota baixa
 */
function validateTextQuality($text) {
    if (empty($text)) return 0;
    
    $length = strlen($text);
    if ($length < 10) return 0;
    
    // Conta caracteres "legítimos" (letras, números, espaços, pontuação comum)
    $valid_chars = preg_match_all('/[a-zA-Z0-9\s\.,;:\!\?\-\'\"\(\)\[\]\{\}\n\r\t\/\\\\àáâãäåèéêëìíîïòóôõöùúûüçñ]/u', $text);
    
    // Conta sequências de letras (palavras) com pelo menos 3 caracteres
    $word_count = preg_match_all('/[a-zA-Zàáâãäåèéêëìíîïòóôõöùúûüçñ]{3,}/u', $text);
    
    // Conta números
    $number_count = preg_match_all('/\d+/', $text);
    
    // Calcula porcentagem de qualidade
    $char_quality = ($valid_chars / $length) * 100;
    
    // Penaliza se tem muito poucos números e muitas letras (pode ser símbolo)
    // Valoriza se tem palavras genuínas
    $quality_score = $char_quality;
    
    // Bonus se tem números
    if ($number_count > 0) {
        $quality_score += min(10, $number_count);
    }
    
    // Se tem menos de 5 palavras de 3+ letras, provavelmente é lixo
    if ($word_count < 5) {
        return 0;
    }
    
    // Verificação adicional: não pode ser só símbolos
    $symbol_count = preg_match_all('/[^\w\s]/u', $text);
    $symbol_ratio = $symbol_count / $length;
    
    // Se mais de 50% são símbolos especiais, é suspeito
    if ($symbol_ratio > 0.5) {
        $quality_score -= ($symbol_ratio * 50);
    }
    
    // Capping no máximo 100
    return min(100, max(0, round($quality_score)));
}

/**
 * Extrai texto de um arquivo PDF
 * Tenta diversos métodos em ordem de preferência
 */
function extractTextFromPDF($filepath) {
    // Método 1: tenta usar pdftotext do sistema (mais confiável)
    $extracted = extractWithPdftotext($filepath);
    if ($extracted) return $extracted;
    
    // Método 2: tenta com função nativa se disponível
    if (function_exists('pdf_get_info')) {
        $extracted = extractWithNativePDF($filepath);
        if ($extracted) return $extracted;
    }
    
    // Método 3: extração básica de strings do PDF (fallback)
    $extracted = extractBasicFromPDF($filepath);
    if ($extracted) return $extracted;
    
    return null;
}

/**
 * Tenta usar pdftotext do sistema operacional
 */
function extractWithPdftotext($filepath) {
    // Verifica se pdftotext está disponível
    $which = shell_exec(PHP_OS_FAMILY === 'Windows' ? 'where pdftotext 2>nul' : 'which pdftotext 2>/dev/null');
    
    if (!empty($which)) {
        $output_file = tempnam(sys_get_temp_dir(), 'pdf_');
        $safe_pdf = escapeshellarg($filepath);
        $safe_output = escapeshellarg($output_file);
        
        $command = PHP_OS_FAMILY === 'Windows' 
            ? "pdftotext $safe_pdf $safe_output 2>&1"
            : "pdftotext $safe_pdf $safe_output 2>&1";
        
        exec($command, $output, $return_code);
        
        if ($return_code === 0 && file_exists($output_file)) {
            $text = file_get_contents($output_file);
            @unlink($output_file);
            
            if (!empty(trim($text))) {
                return trim($text);
            }
        }
    }
    
    return null;
}

/**
 * Tenta usar funções nativas de PDF (se disponíveis)
 */
function extractWithNativePDF($filepath) {
    try {
        if (function_exists('fopen')) {
            $pdf = @fopen($filepath, 'r');
            if ($pdf) {
                $content = '';
                while (($line = fgets($pdf, 1024)) !== false) {
                    // Remove caracteres de controle e mantém só texto legível
                    $line = preg_replace('/[^\x20-\x7E\n\r\t]/', '', $line);
                    if (trim($line)) {
                        $content .= $line . "\n";
                    }
                }
                @fclose($pdf);
                
                return !empty(trim($content)) ? trim($content) : null;
            }
        }
    } catch (Exception $e) {
        return null;
    }
    
    return null;
}

/**
 * Extração básica de texto do PDF (último recurso)
 * Usa múltiplas técnicas para máxima compatibilidade
 */
function extractBasicFromPDF($filepath) {
    $pdf_content = @file_get_contents($filepath);
    
    if ($pdf_content === false) {
        return null;
    }
    
    $extracted_text = '';
    
    // Passo 0: Remove headers/footers/metadados ANTES de tentar extrair
    // Isso é crítico para não misturar estrutura PDF com conteúdo real
    $clean_content = removeMetadataFromPDF($pdf_content);
    
    // Passo 1: Tenta extrair objetos de texto (Tj/TJ) - MAIS CONFIÁVEL
    $extracted_text = extractFromTextObjects($clean_content);
    
    // Se conseguiu algo legítimo, retorna logo
    if (!empty(trim($extracted_text)) && validateTextQuality($extracted_text) > 30) {
        return $extracted_text;
    }
    
    // Passo 2: Se não conseguiu bom, tenta entre parênteses
    $extracted_text = extractFromParentheses($clean_content);
    
    if (!empty(trim($extracted_text)) && validateTextQuality($extracted_text) > 30) {
        return $extracted_text;
    }
    
    // Passo 3: Tenta entre aspas
    $extracted_text = extractFromQuotes($clean_content);
    
    if (!empty(trim($extracted_text)) && validateTextQuality($extracted_text) > 30) {
        return $extracted_text;
    }
    
    // Passo 4: Tenta buscar strings visíveis no binário
    $extracted_text = extractVisibleStrings($clean_content);
    
    if (!empty(trim($extracted_text)) && validateTextQuality($extracted_text) > 25) {
        return $extracted_text;
    }
    
    // Se nenhum método funcionou bem
    return null;
}

/**
 * Extrai texto dos objetos de texto PDF (Tj/TJ operators)
 * Este é o método MAIS confiável
 */
function extractFromTextObjects($content) {
    $text = '';
    $count = 0;
    
    // Procura por operadores de texto Tj (show string)
    // Formato: (texto) Tj
    // Captura: grupos de 1+ parênteses antes de Tj
    if (preg_match_all('/\(((?:[^()]|\\\\.)*?)\)\s*Tj/s', $content, $matches)) {
        foreach ($matches[1] as $match) {
            $decoded = decodePDFString($match);
            if (!empty(trim($decoded))) {
                $text .= $decoded . ' ';
                $count++;
            }
        }
    }
    
    // Se encontrou com Tj, retorna
    if ($count > 0) {
        return trim($text);
    }
    
    // Se não achou Tj, procura por TJ (show string with positioning)
    // Formato: [(...) n (...) n] TJ
    if (preg_match_all('/\[((?:[^\[\]]|\\\\.)*?)\]\s*TJ/s', $content, $matches)) {
        foreach ($matches[1] as $match) {
            // Extrai strings entre parênteses dentro dos colchetes
            if (preg_match_all('/\(((?:[^()]|\\\\.)*?)\)/s', $match, $strings)) {
                foreach ($strings[1] as $str) {
                    $decoded = decodePDFString($str);
                    if (!empty(trim($decoded))) {
                        $text .= $decoded . ' ';
                        $count++;
                    }
                }
            }
        }
    }
    
    // Se ainda não conseguiu nada, tenta um padrão mais relaxado
    if ($count === 0) {
        // Procura por qualquer string entre parênteses seguida de espaço e letra maiúscula
        // (que pode ser um comando PDF abreviado)
        if (preg_match_all('/\(((?:[^()]|\\\\.)*?)\)\s*[A-Z]/s', $content, $matches)) {
            foreach ($matches[1] as $match) {
                // Só decodifica se parecer conter caracteres "reais"
                if (preg_match('/[a-zA-Z]/i', $match)) {
                    $decoded = decodePDFString($match);
                    if (!empty(trim($decoded)) && strlen(trim($decoded)) > 2) {
                        $text .= $decoded . ' ';
                        $count++;
                    }
                }
            }
        }
    }
    
    return $count > 0 ? trim($text) : '';
}

/**
 * Decodifica strings do formato PDF
 * Remove caracteres de escape e converte para texto legível
 */
function decodePDFString($str) {
    if (empty($str)) return '';
    
    // Passo 1: Remove escapes PDF padrão
    $replacements = [
        '\\n' => "\n",  // Newline
        '\\r' => "\r",  // Carriage return
        '\\t' => "\t",  // Tab
        '\\b' => "\b",  // Backspace
        '\\f' => "\f",  // Form feed
        '\\(' => '(',   // Escaped paren
        '\\)' => ')',   // Escaped paren
        '\\\\' => '\\', // Escaped backslash
    ];
    
    foreach ($replacements as $search => $replace) {
        $str = str_replace($search, $replace, $str);
    }
    
    // Passo 2: Remove escapes hexadecimais (#XY)
    $str = preg_replace_callback('/#([0-9A-Fa-f]{2})/', function($matches) {
        return chr(hexdec($matches[1]));
    }, $str);
    
    // Passo 3: Remove zeros nulos
    $str = str_replace("\x00", '', $str);
    
    // Passo 4: Converte HTML entities
    $str = html_entity_decode($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Passo 5: Filtra caracteres inválidos
    $str = filterValidTextFromPDF($str);
    
    return trim($str);
}

/**
 * Extrai texto entre parênteses
 */
function extractFromParentheses($content) {
    $text = '';
    
    if (preg_match_all('/\(((?:[^()]|\\\\.)*?)\)/s', $content, $matches)) {
        foreach ($matches[1] as $match) {
            $decoded = decodePDFString($match);
            if (!empty($decoded) && strlen($decoded) > 2) {
                $text .= $decoded . ' ';
            }
        }
    }
    
    return trim($text);
}

/**
 * Extrai texto entre aspas
 */
function extractFromQuotes($content) {
    $text = '';
    
    if (preg_match_all('/\"((?:[^\"]|\\\\.)*?)\"/s', $content, $matches)) {
        foreach ($matches[1] as $match) {
            $decoded = decodePDFString($match);
            if (!empty($decoded) && strlen($decoded) > 2) {
                $text .= $decoded . ' ';
            }
        }
    }
    
    return trim($text);
}

/**
 * Extrai strings visíveis do binário do PDF
 * Busca sequências de caracteres imprimíveis (último recurso)
 */
function extractVisibleStrings($content) {
    $text = '';
    
    // Remove caracteres de controle
    $cleaned = preg_replace('/[\x00-\x08\x0E-\x1F\x7F]/s', ' ', $content);
    
    // Extrai palavras com 3+ caracteres que pareçam conteúdo real
    // (não números puros ou símbolos)
    if (preg_match_all('/[A-Za-z0-9àáâãäåèéêëìíîïòóôõöùúûüçñÀÁÂÃÄÅÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÇÑ_\-]{4,}/u', $cleaned, $matches)) {
        $words = array_unique($matches[0]);
        
        // Lista de palavras que são DEFINITIVAMENTE estrutura PDF/metadados
        $blacklist = [
            'stream', 'endstream', 'obj', 'endobj', 'xref', 'trailer', 'startxref',
            'BT', 'ET', 'Tj', 'TJ', 'TD', 'Td', 'Tm', 'cm', 'rg', 'RG', 'gs', 'q', 'Q',
            'pdf', 'PDF', 'Adobe', 'Type', 'Font', 'Subtype', 'BaseFont', 'Encoding',
            'Contents', 'Resources', 'MediaBox', 'Parent', 'Kids', 'Count', 'Pages',
            'Filter', 'Length', 'Width', 'Height', 'ColorSpace', 'DeviceRGB', 'DeviceCMYK',
            'FlateDecode', 'DCTDecode', 'ASCII85Decode', 'Helvetica', 'Arial', 'Times',
            'Courier', 'Symbol', 'Producer', 'Creator', 'CreationDate', 'ModDate',
            'viewerpreferences', 'extgstate', 'procset', 'imageb', 'imagec', 'imagei',
            'metadata', 'falsecolor', 'truecolor', 'grayscale', 'indexed'
        ];
        
        foreach ($words as $word) {
            // Pula palavras na blacklist
            if (in_array($word, $blacklist) || in_array(strtolower($word), array_map('strtolower', $blacklist))) {
                continue;
            }
            
            // Pula números puros
            if (is_numeric($word)) {
                continue;
            }
            
            // Pula números com caracteres (coordinates/measurements)
            if (preg_match('/^\d+(\.\d+)?$/', $word)) {
                continue;
            }
            
            // Pula palavras muito comuns de estrutura PDF
            if (preg_match('/^(Page|Font|Image|Glyph|Name|Array|Dict|Stream|Object|Null|Ref)$/i', $word)) {
                continue;
            }
            
            // Se passou em tudo, adiciona à extração
            $text .= $word . ' ';
        }
    }
    
    return trim($text);
}

/**
 * Normaliza texto extraído
 */
function normalizeExtractedText($text) {
    // Remove múltiplos espaços/quebras de linha
    $text = preg_replace('/\s+/', ' ', $text);
    
    // Remove caracteres muito estranhos mantendo acentos e pontuação
    $text = preg_replace('/[^\w\s\.\,\;\:\!\?\-\'\"\(\)\[\]\{\}\/àáâãäåèéêëìíîïòóôõöùúûüçñÀÁÂÃÄÅÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÇÑ]/u', '', $text);
    
    // Remove múltiplos espaços novamente
    $text = preg_replace('/\s+/', ' ', trim($text));
    
    return $text;
}

/**
 * Remove metadados e informações de controle do PDF
 * MUITO agressivo - remove tudo que NÃO é texto real
 */
function removeMetadataFromPDF($content) {
    // Passo 1: Remove objetos stream (dados binários grandes)
    $content = preg_replace('/\bstream\b.*?\bendstream\b/is', '', $content);
    
    // Passo 2: Remove XML metadata
    $content = preg_replace('/<\?xpacket.*?\?>/is', '', $content);
    $content = preg_replace('/<rdf:RDF.*?<\/rdf:RDF>/is', '', $content);
    
    // Passo 3: Remove comentários PDF (linhas começando com %)
    $content = preg_replace('/^%.*?$/m', '', $content);
    
    // Passo 4: Remove dicionários (<<...>>) - IMPORTANTE
    $content = preg_replace('/<<.*?>>/s', '', $content);
    
    // Passo 5: Remove números de referência PDF (1 0 R, 2 0 R, etc)
    $content = preg_replace('/\b\d+\s+\d+\s+R\b/s', '', $content);
    
    // Passo 6: Remove comandos PDF
    // BT = Begin Text, ET = End Text, Tj = Show String, TJ = Show String with positioning
    $content = preg_replace('/\s+(BT|ET|Tm|Td|TD|T\*|Tj|TJ|Tw|Tz|TL|Tf|Tr|Ts|cm|d|re|S|s|f|F|B|b|rg|RG|gs|q|Q|Do|EI|ID|EX|BX)\s+/s', ' ', $content);
    
    // Passo 7: Remove object declarations (obj endobj)
    $content = preg_replace('/\b\d+\s+\d+\s+(obj|endobj)\b/i', '', $content);
    
    // Passo 8: Remove xref, trailer, startxref
    $content = preg_replace('/(xref|trailer|startxref).*$/mis', '', $content);
    
    // Passo 9: Remove padrões de PDF structure
    // Nomes como /Type, /Subtype, /FontName, /MediaBox, /Parent, etc
    $content = preg_replace('/\/[A-Za-z]+(?:\s+[0-9]+)+\b/', '', $content);
    $content = preg_replace('/\/[A-Za-z]+\s+(?:\[|<<|[0-9])/', '', $content);
    
    // Passo 10: Remove caracteres de controle e bytes nulos
    $content = preg_replace('/[\x00-\x08\x0E-\x1F\x7F]/s', ' ', $content);
    
    // Passo 11: Limpa espaços múltiplos
    $content = preg_replace('/\s+/', ' ', $content);
    
    // Passo 12: Remove linhas que são só nomes de dicionários
    $lines = explode(' ', trim($content));
    $filtered = [];
    
    $pdf_dict_names = [
        'Type', 'Subtype', 'BaseFont', 'Encoding', 'FontName', 'FontFamily', 'FontDescriptor',
        'Font', 'XObject', 'Image', 'MediaBox', 'CropBox', 'Contents', 'Resources', 'Parent',
        'Kids', 'Count', 'Pages', 'Page', 'Catalog', 'Pages', 'PageMode', 'PageLayout',
        'ViewerPreferences', 'ExtGState', 'ProcSet', 'Text', 'ImageB', 'ImageC', 'ImageI',
        'Metadata', 'ID', 'Filter', 'Length', 'Width', 'Height', 'ColorSpace', 'BitsPerComponent',
        'DecodeParms', 'FlateDecode', 'DCTDecode', 'ASCII85Decode', 'LZWDecode',
        'Producer', 'Creator', 'CreationDate', 'ModDate', 'Title', 'Subject', 'Author',
        'Keywords', 'Trapped', 'PTEX', 'pdf', 'Adobe', 'Helvetica', 'Arial', 'Times',
        'Courier', 'Symbol', 'ZapfDingbats', 'Endstream', 'Stream', 'Obj', 'Endobj',
        'Xref', 'Trailer', 'Startxref', 'Null', 'True', 'False'
    ];
    
    foreach ($lines as $word) {
        $word = trim($word);
        if (empty($word)) continue;
        
        // Pula palavras de estrutura PDF
        if (in_array($word, $pdf_dict_names) || in_array(strtolower($word), array_map('strtolower', $pdf_dict_names))) {
            continue;
        }
        
        // Pula números puros
        if (is_numeric($word)) {
            continue;
        }
        
        // Pula coordenadas (números com ponto)
        if (preg_match('/^\d+(\.\d+)?$/', $word)) {
            continue;
        }
        
        $filtered[] = $word;
    }
    
    return implode(' ', $filtered);
}

/**
 * Filtra caracteres inválidos mantendo apenas texto legítimo
 */
function filterValidTextFromPDF($text) {
    if (empty($text)) return '';
    
    // Decodifica HTML entities
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Mantém apenas caracteres válidos
    // Caracteres válidos: a-z, A-Z, 0-9, acentos, pontuação comum, espaços, quebras de linha
    $text = preg_replace(
        '/[^\w\s\.\,\;\:\!\?\-\'\"\(\)\[\]\{\}\/\\\\\@\#\$\%\&\*\+\=\~\`\^\|àáâãäåèéêëìíîïòóôõöùúûüçñÀÁÂÃÄÅÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÇÑ\n\r\t]/u', 
        '', 
        $text
    );
    
    // Remove sequências muito longas de caracteres especiais (mais de 2 símbolos seguidos)
    $text = preg_replace('/[\W_]{3,}/u', ' ', $text);
    
    // Quebra em linhas e filtra cada uma
    $lines = preg_split('/[\n\r]+/', $text);
    $filtered_lines = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Pula linhas muito curtas
        if (strlen($line) < 2) continue;
        
        // Conta letras vs símbolos
        $letter_count = preg_match_all('/[a-zA-Z0-9àáâãäåèéêëìíîïòóôõöùúûüçñ]/u', $line);
        $total_count = strlen($line);
        
        // Se menos de 25% são letras/números, provavelmente é lixo
        if ($total_count > 0 && ($letter_count / $total_count) < 0.25) {
            continue;
        }
        
        // Filtra linhas que parecem ser comandos PDF
        if (preg_match('/^(stream|endstream|obj|endobj|xref|trailer|startxref|BT|ET|q|Q|cm|rg|RG|gs|Tj|TJ|Td|TD)$/i', $line)) {
            continue;
        }
        
        $filtered_lines[] = $line;
    }
    
    return implode("\n", $filtered_lines);
}

/**
 * Analisa texto com IA (se configurado)
 */
function analyzeWithAI($text, $filename) {
    // Se não tem configuração de IA, retorna null
    if (!defined('AI_API_KEY') || empty(AI_API_KEY)) {
        return null;
    }
    
    try {
        // Limita texto para economizar tokens
        $truncated_text = substr($text, 0, 2000);
        
        // Prompt padrão para análise
        $prompt = "Analise este documento de forma concisa:\n\nNome: $filename\n\nConteúdo:\n$truncated_text\n\nForneça:\n1. Resumo\n2. Pontos principais\n3. Tipo de documento";
        
        // Chamada para OpenAI (exemplo)
        if (defined('AI_PROVIDER') && AI_PROVIDER === 'openai') {
            return analyzeWithOpenAI($prompt);
        }
        
    } catch (Exception $e) {
        logProcess("Erro na análise de IA: " . $e->getMessage());
    }
    
    return null;
}

/**
 * Analisa com OpenAI (exemplo)
 */
function analyzeWithOpenAI($prompt) {
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . AI_API_KEY
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 500
        ])
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response === false) {
        throw new Exception("Erro na requisição OpenAI");
    }
    
    $data = json_decode($response, true);
    
    if (isset($data['choices'][0]['message']['content'])) {
        return $data['choices'][0]['message']['content'];
    }
    
    throw new Exception("Resposta inesperada da IA");
}

// Se foi chamado via HTTP e não é CLI, mostra resultado
if (php_sapi_name() !== 'cli' && basename($_SERVER['PHP_SELF'] ?? '') === 'process_documents.php') {
    ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processamento de Documentos - Portal MCM</title>
    <link rel="icon" type="image/svg+xml" href="/basemcm/icon-mcm.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .processing-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
        }
        .processing-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .processing-header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        .processing-header p {
            opacity: 0.9;
        }
        .processing-log {
            padding: 2rem;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        .log-content {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 1.5rem;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            line-height: 1.6;
            max-height: 400px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .log-line {
            padding: 0.25rem 0;
        }
        .log-line.success {
            color: #28a745;
        }
        .log-line.warning {
            color: #ffc107;
        }
        .log-line.error {
            color: #dc3545;
        }
        .processing-footer {
            padding: 2rem;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .summary {
            background: #e8f4f8;
            border-left: 4px solid #667eea;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }
        .summary-item {
            display: inline-block;
            margin-right: 2rem;
        }
        .summary-label {
            font-weight: 500;
            color: #495057;
        }
        .summary-value {
            font-weight: bold;
            color: #667eea;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="processing-container">
        <div class="processing-header">
            <h1><i class="fas fa-cogs"></i> Processamento de Documentos</h1>
            <p>Extração de texto concluída</p>
        </div>
        <div class="processing-log">
            <?php
            $log_file = appUploadAbsolutePath('process.log');
            $log_content = @file_get_contents($log_file);
            
            if ($log_content) {
                // Parse log para extrair estatísticas
                preg_match('/Sucesso: (\d+) \| Erros: (\d+)/', $log_content, $matches);
                $success = $matches[1] ?? 0;
                $errors = $matches[2] ?? 0;
                
                echo '<div class="summary">';
                echo '<div class="summary-item"><span class="summary-label">✓ Sucesso:</span> <span class="summary-value">' . $success . '</span></div>';
                if ($errors > 0) {
                    echo '<div class="summary-item"><span class="summary-label">✗ Erros:</span> <span class="summary-value" style="color: #dc3545;">' . $errors . '</span></div>';
                }
                echo '</div>';
                
                // Mostra log colorido
                echo '<div class="log-content">';
                $lines = explode("\n", trim($log_content));
                foreach ($lines as $line) {
                    $class = '';
                    if (strpos($line, '✓') !== false) {
                        $class = 'success';
                    } elseif (strpos($line, '⚠') !== false || strpos($line, '→') !== false) {
                        $class = 'warning';
                    } elseif (strpos($line, '✗') !== false) {
                        $class = 'error';
                    }
                    echo '<div class="log-line ' . $class . '">' . htmlspecialchars($line) . '</div>';
                }
                echo '</div>';
            } else {
                echo '<p style="text-align: center; color: #6c757d;">Nenhum log disponível. Processamento ainda não foi executado.</p>';
            }
            ?>
        </div>
        <div class="processing-footer">
            <div class="btn-group">
                <a href="pages/approve_documents.php" class="btn btn-primary">
                    <i class="fas fa-check"></i> Voltar para Aprovação
                </a>
                <a href="pages/documentos.php" class="btn btn-secondary">
                    <i class="fas fa-file"></i> Ver Documentos
                </a>
            </div>
        </div>
    </div>
</body>
</html>
    <?php
    exit;
}
?>
