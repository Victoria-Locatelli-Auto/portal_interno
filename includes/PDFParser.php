<?php
/**
 * Classe para extração de texto de PDF
 * Usa Smalot\PdfParser se disponível, com fallback para métodos alternativos
 */

class SimplePDFParser {
    
    /**
     * Extrai texto de um arquivo PDF
     * Tenta usar Smalot\PdfParser, depois regex, depois fallback
     * @param string $pdf_path Caminho do arquivo PDF
     * @return string|null Texto extraído
     */
    public static function extractText($pdf_path) {
        if (!file_exists($pdf_path)) {
            return null;
        }
        
        // Tentar método 1: Smalot PdfParser (melhor qualidade)
        $text = self::extractWithSmalot($pdf_path);
        if (!empty($text)) {
            return $text;
        }
        
        // Tentar método 2: pdftotext (ferramenta do sistema)
        $text = self::extractWithPdfToText($pdf_path);
        if (!empty($text)) {
            return $text;
        }
        
        // Tentar método 3: Regex avançado
        $text = self::extractWithRegex($pdf_path);
        if (!empty($text)) {
            return $text;
        }
        
        // Tentar método 4: Fallback simples
        return self::extractTextAlternative($pdf_path);
    }
    
    /**
     * Extrai texto usando Smalot\PdfParser (melhor qualidade)
     * @param string $pdf_path Caminho do arquivo PDF
     * @return string|null Texto extraído
     */
    private static function extractWithSmalot($pdf_path) {
        try {
            // Carregar autoload
            $autoload = __DIR__ . '/../vendor/autoload.php';
            if (!file_exists($autoload)) {
                return null;
            }
            require_once $autoload;
            
            // Verificar se classe existe
            if (!class_exists('Smalot\PdfParser\Parser')) {
                return null;
            }
            
            // Usar Parser (Smalot ou fallback)
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($pdf_path);
            
            if (!$pdf) {
                return null;
            }
            
            // Extrair texto de todas as páginas
            $text = '';
            $pages = $pdf->getPages();
            
            if (is_array($pages) || $pages instanceof \Iterator) {
                foreach ($pages as $page) {
                    if (method_exists($page, 'getText')) {
                        $text .= $page->getText();
                    }
                    $text .= "\n\n"; // Quebra entre páginas
                }
            } else if (method_exists($pages, 'getText')) {
                $text = $pages->getText();
            }
            
            // Limpar texto
            $text = preg_replace('/\s+/', ' ', $text);
            $text = trim($text);
            
            return !empty($text) ? $text : null;
            
        } catch (\Throwable $e) {
            // Log silencioso - trata como fallback
            error_log('Smalot Parser Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Extrai texto usando o comando pdftotext (se disponível)
     * @param string $pdf_path Caminho do arquivo PDF
     * @return string|null Texto extraído
     */
    private static function extractWithPdfToText($pdf_path) {
        try {
            // Criar arquivo temporário para saída
            $temp_file = tempnam(sys_get_temp_dir(), 'pdf_');
            if (!$temp_file) {
                return null;
            }
            
            // Executar pdftotext
            $cmd = "pdftotext \"" . escapeshellarg($pdf_path) . "\" \"$temp_file\" 2>&1";
            @exec($cmd, $output, $return_code);
            
            if ($return_code === 0 && file_exists($temp_file)) {
                $text = file_get_contents($temp_file);
                @unlink($temp_file);
                
                if (!empty($text)) {
                    // Limpar texto
                    $text = preg_replace('/\s+/', ' ', $text);
                    return trim($text);
                }
            }
            
            @unlink($temp_file);
            return null;
            
        } catch (\Throwable $e) {
            return null;
        }
    }
    
    /**
     * Extrai texto usando regex avançado
     * Melhora a extração de PDFs complexos
     * @param string $pdf_path Caminho do arquivo PDF
     * @return string|null Texto extraído
     */
    private static function extractWithRegex($pdf_path) {
        try {
            $content = file_get_contents($pdf_path);
            if (empty($content)) {
                return null;
            }
            
            // Decompress FlateDecode streams se possível
            $content = preg_replace_callback(
                '/stream\\s+(.+?)\\s+endstream/is',
                function($matches) {
                    $stream = $matches[1];
                    // Tentar descomprimir
                    if (strpos($stream, 'QZXY') === 0 || preg_match('/^x\x9c/', $stream)) {
                        $decompressed = @gzuncompress($stream);
                        return $decompressed !== false ? $decompressed : $stream;
                    }
                    return $stream;
                },
                $content
            );
            
            // Extrair texto entre BT/ET
            $text = '';
            if (preg_match_all('/BT(.*?)ET/s', $content, $matches)) {
                foreach ($matches[1] as $block) {
                    $text .= self::extractTextFromBlock($block) . ' ';
                }
            }
            
            // Extrair strings de stream
            if (preg_match_all('/\(([^()]*)\)/s', $content, $string_matches)) {
                foreach ($string_matches[1] as $str) {
                    $decoded = self::decodeString($str);
                    if (!empty($decoded)) {
                        $text .= $decoded . ' ';
                    }
                }
            }
            
            $text = preg_replace('/\s+/', ' ', $text);
            $text = trim($text);
            
            return !empty($text) ? $text : null;
            
        } catch (\Throwable $e) {
            error_log('PDF Regex Extraction Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Extrai texto de um bloco BT/ET
     * @param string $block Bloco de texto do PDF
     * @return string Texto extraído
     */
    private static function extractTextFromBlock($block) {
        $text = '';
        
        // Strings hexadecimais
        if (preg_match_all('/<([0-9A-Fa-f]+)>/i', $block, $hex_matches)) {
            foreach ($hex_matches[1] as $hex) {
                if (strlen($hex) % 2 === 0) {
                    @$decoded = hex2bin($hex);
                    if ($decoded !== false) {
                        // Tentar diferentes encodings
                        $str = @iconv('UTF-16BE', 'UTF-8//IGNORE', $decoded);
                        if (empty($str)) {
                            $str = @iconv('UTF-8', 'UTF-8//IGNORE', $decoded);
                        }
                        if (empty($str)) {
                            $str = @iconv('CP1252', 'UTF-8//IGNORE', $decoded);
                        }
                        if (!empty($str)) {
                            $text .= $str . ' ';
                        }
                    }
                }
            }
        }
        
        // Strings regulares entre parênteses
        if (preg_match_all('/\(([^()]*)\)/s', $block, $str_matches)) {
            foreach ($str_matches[1] as $str) {
                $decoded = self::decodeString($str);
                if (!empty($decoded)) {
                    $text .= $decoded . ' ';
                }
            }
        }
        
        return $text;
    }
    
    /**
     * Decodifica uma string PDF
     * @param string $str String codificada PDF
     * @return string String decodificada
     */
    private static function decodeString($str) {
        // Remove escape sequences
        $str = preg_replace('/\\\\(.)/s', '$1', $str);
        
        // Remove caracteres de controle
        $str = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/s', '', $str);
        
        return trim($str);
    }
    
    /**
     * Extração alternativa usando método simples
     * Para PDFs que falham nos outros métodos
     * @param string $pdf_path Caminho do arquivo PDF
     * @return string|null Texto extraído
     */
    public static function extractTextAlternative($pdf_path) {
        if (!file_exists($pdf_path)) {
            return null;
        }
        
        try {
            $file_content = file_get_contents($pdf_path);
            if (empty($file_content)) {
                return null;
            }
            
            // Estratégia simples: procurar por texto entre strings
            $text = '';
            
            // Padrão 1: Strings entre parênteses
            if (preg_match_all('/\(([^()]*)\)/s', $file_content, $matches)) {
                foreach ($matches[1] as $match) {
                    $cleaned = self::decodeString($match);
                    if (!empty($cleaned) && strlen($cleaned) > 2) {
                        $text .= $cleaned . ' ';
                    }
                }
            }
            
            // Padrão 2: Hex strings
            if (preg_match_all('/<([0-9A-Fa-f]{20,})>/i', $file_content, $hex_matches)) {
                foreach ($hex_matches[1] as $hex) {
                    if (strlen($hex) % 2 === 0) {
                        @$decoded = hex2bin($hex);
                        if ($decoded !== false) {
                            $str = @iconv('UTF-16BE', 'UTF-8//IGNORE', $decoded);
                            if (empty($str)) {
                                $str = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $decoded);
                            }
                            if (!empty($str)) {
                                $text .= $str . ' ';
                            }
                        }
                    }
                }
            }
            
            $text = preg_replace('/\s+/', ' ', $text);
            $text = trim($text);
            
            return !empty($text) ? $text : null;
            
        } catch (\Throwable $e) {
            error_log('PDF Alternative Extraction Error: ' . $e->getMessage());
            return null;
        }
    }
}
