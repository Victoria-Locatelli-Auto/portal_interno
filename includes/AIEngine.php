<?php
/**
 * Motor de IA para busca inteligente baseada nos documentos do portal.
 */

require_once __DIR__ . '/SearchEngine.php';

class AIEngine
{
    private const MAX_KEYWORDS = 5;
    private const MAX_DOCUMENTS_IN_RESPONSE = 3;
    private const CACHE_MIN_CONFIDENCE = 0.7;

    private mysqli $conn;
    private int $user_id;
    private SearchEngine $search_engine;

    public function __construct(mysqli $conn, int $user_id)
    {
        $this->conn = $conn;
        $this->user_id = $user_id;
        $this->search_engine = new SearchEngine($conn, $user_id, true);
    }

    public function answerQuestion(string $question): array
    {
        $question = trim($question);

        if ($question === '') {
            return $this->emptyAnswer('Por favor, digite uma pergunta.');
        }

        $cached = $this->getCachedAnswer($question);
        if ($cached !== null) {
            return $cached;
        }

        $category = $this->categorizeQuestion($question);
        $keywords = $this->extractKeywords($question);
        $documents = $this->findRelatedDocuments($keywords);

        if ($documents) {
            $response = $this->generateAnswerFromDocuments($documents);
            $confidence = min(0.95, 0.5 + (count($documents) * 0.15));
        } else {
            $response = $this->generateDefaultResponse($category);
            $confidence = 0.3;
        }

        $this->saveInteraction($question, $response, $documents);

        return [
            'response' => $response,
            'documents' => array_slice($documents, 0, self::MAX_DOCUMENTS_IN_RESPONSE),
            'confidence' => round($confidence, 2),
            'category' => $category,
            'keywords' => $keywords,
            'from_cache' => false,
        ];
    }

    public function saveFeedback($interaction_id, $rating, $feedback = ''): bool
    {
        $interaction_id = (int)$interaction_id;
        $rating = max(1, min(5, (int)$rating));
        $feedback = trim((string)$feedback);

        if ($interaction_id <= 0) {
            return false;
        }

        $stmt = $this->conn->prepare('UPDATE ai_interactions SET user_rating = ?, feedback = ? WHERE id = ?');

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('isi', $rating, $feedback, $interaction_id);

        return $stmt->execute();
    }

    public function getFrequentlyAsked($limit = 10): array
    {
        $limit = max(1, min(50, (int)$limit));
        $stmt = $this->conn->prepare("
            SELECT question, answer, times_used, confidence_score
            FROM ai_knowledge_base
            WHERE times_used > 0
            ORDER BY times_used DESC
            LIMIT ?
        ");

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $limit);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function emptyAnswer(string $message): array
    {
        return [
            'response' => $message,
            'documents' => [],
            'confidence' => 0,
            'category' => 'Informações Gerais',
            'keywords' => [],
            'from_cache' => false,
        ];
    }

    private function extractKeywords(string $question): array
    {
        $stop_words = [
            'o', 'a', 'os', 'as', 'um', 'uma', 'uns', 'umas',
            'de', 'do', 'da', 'dos', 'das', 'em', 'no', 'na',
            'e', 'ou', 'para', 'por', 'com', 'sem', 'que',
            'como', 'qual', 'quais', 'quando', 'onde', 'porque',
            'sobre', 'isso', 'esse', 'essa', 'este', 'esta',
        ];

        $normalized = $this->lower($question);
        $words = preg_split('/[^\p{L}\p{N}]+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY);
        $keywords = [];

        foreach ($words ?: [] as $word) {
            if ($this->length($word) > 2 && !in_array($word, $stop_words, true)) {
                $keywords[] = $word;
            }
        }

        return array_slice(array_values(array_unique($keywords)), 0, self::MAX_KEYWORDS);
    }

    private function findRelatedDocuments(array $keywords): array
    {
        $results = [];

        foreach ($keywords as $keyword) {
            $search_results = $this->search_engine->search($keyword);

            foreach ($search_results as $result) {
                if (empty($result['document']['id'])) {
                    continue;
                }

                $doc_id = (int)$result['document']['id'];

                if (!isset($results[$doc_id])) {
                    $results[$doc_id] = [
                        'document' => $result['document'],
                        'relevance_score' => 0.0,
                        'matches' => [],
                    ];
                }

                $results[$doc_id]['relevance_score'] += (float)($result['score'] ?? 0);
                $results[$doc_id]['matches'][] = $keyword;
            }
        }

        usort($results, static function (array $a, array $b): int {
            return ($b['relevance_score'] <=> $a['relevance_score']);
        });

        return $results;
    }

    private function generateAnswerFromDocuments(array $documents): string
    {
        $response = "Com base nos documentos encontrados, aqui está a resposta:\n\n";

        foreach (array_slice($documents, 0, self::MAX_DOCUMENTS_IN_RESPONSE) as $doc_info) {
            $doc = $doc_info['document'] ?? [];
            $name = (string)($doc['original_name'] ?? 'Documento sem título');
            $excerpt = $this->extractRelevantExcerpt((string)($doc['extracted_text'] ?? ''), $doc_info['matches'] ?? []);

            $response .= $name . "\n";
            $response .= $this->truncate($excerpt, 200) . "\n\n";
        }

        return $response . 'Para informações mais detalhadas, consulte os documentos acima.';
    }

    private function extractRelevantExcerpt(string $text, array $keywords): string
    {
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        if (!$keywords) {
            return $this->truncate($this->cleanWhitespace($text), 150);
        }

        $text_lower = $this->lower($text);
        $keyword = $this->lower((string)$keywords[0]);
        $position = strpos($text_lower, $keyword);

        if ($position === false) {
            return $this->truncate($this->cleanWhitespace($text), 150);
        }

        $start = max(0, $position - 50);
        $excerpt = substr($text, $start, 150);
        $excerpt = $this->cleanWhitespace($excerpt);

        if ($start > 0) {
            $excerpt = '...' . $excerpt;
        }

        if ($start + 150 < strlen($text)) {
            $excerpt .= '...';
        }

        return $excerpt;
    }

    private function generateDefaultResponse(string $category): string
    {
        $responses = [
            'Benefícios' => 'Para informações sobre benefícios, favor consultar com o departamento de RH ou verificar seu contrato de trabalho.',
            'Procedimentos' => 'Este é um procedimento específico. Recomendo consultar com seu gestor ou o setor responsável.',
            'Informações Gerais' => 'Não encontrei documentos sobre isso. Tente formular a pergunta de outra forma ou contate o suporte.',
            'Documentos e Contratos' => 'Para informações sobre contratos e documentos, entre em contato com o departamento Jurídico.',
            'Financeiro' => 'Para questões financeiras, favor contatar o departamento de Contabilidade ou RH.',
        ];

        return $responses[$category] ?? $responses['Informações Gerais'];
    }

    private function categorizeQuestion(string $question): string
    {
        $question_lower = $this->lower($question);
        $categories = [
            'Benefícios' => ['vale', 'transporte', 'refeição', 'férias', 'décimo', 'saúde', 'seguro'],
            'Procedimentos' => ['solicitar', 'processo', 'procedimento', 'como fazer', 'passos', 'etapas'],
            'Informações Gerais' => ['empresa', 'horário', 'local', 'telefone', 'endereço', 'localização'],
            'Documentos e Contratos' => ['contrato', 'documento', 'acordo', 'termo', 'cláusula'],
            'Financeiro' => ['salário', 'pagamento', 'remuneração', 'desconto', 'imposto', 'folha'],
        ];

        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($question_lower, $this->lower($keyword)) !== false) {
                    return $category;
                }
            }
        }

        return 'Informações Gerais';
    }

    private function getCachedAnswer(string $question): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT answer, documents_referenced, confidence_score
            FROM ai_knowledge_base
            WHERE LOWER(question) = LOWER(?)
              AND confidence_score >= ?
            LIMIT 1
        ");

        if (!$stmt) {
            return null;
        }

        $min_confidence = self::CACHE_MIN_CONFIDENCE;
        $stmt->bind_param('sd', $question, $min_confidence);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            return null;
        }

        $this->updateTimesUsed($question);
        $documents = json_decode((string)$result['documents_referenced'], true);

        return [
            'response' => (string)$result['answer'],
            'documents' => is_array($documents) ? $documents : [],
            'confidence' => (float)$result['confidence_score'],
            'category' => $this->categorizeQuestion($question),
            'keywords' => $this->extractKeywords($question),
            'from_cache' => true,
        ];
    }

    private function saveInteraction(string $question, string $response, array $documents): bool
    {
        $doc_ids = [];

        foreach ($documents as $doc) {
            if (!empty($doc['document']['id'])) {
                $doc_ids[] = (int)$doc['document']['id'];
            }
        }

        $stmt = $this->conn->prepare("
            INSERT INTO ai_interactions (user_id, question, ai_response, documents_used)
            VALUES (?, ?, ?, ?)
        ");

        if (!$stmt) {
            return false;
        }

        $doc_json = json_encode($doc_ids, JSON_UNESCAPED_UNICODE);
        $stmt->bind_param('isss', $this->user_id, $question, $response, $doc_json);

        return $stmt->execute();
    }

    private function updateTimesUsed(string $question): void
    {
        $stmt = $this->conn->prepare('UPDATE ai_knowledge_base SET times_used = times_used + 1 WHERE LOWER(question) = LOWER(?)');

        if (!$stmt) {
            return;
        }

        $stmt->bind_param('s', $question);
        $stmt->execute();
    }

    private function lower(string $value): string
    {
        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }

    private function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }

    private function truncate(string $value, int $limit): string
    {
        $value = trim($value);

        if ($this->length($value) <= $limit) {
            return $value;
        }

        if (function_exists('mb_substr')) {
            return rtrim(mb_substr($value, 0, $limit, 'UTF-8')) . '...';
        }

        return rtrim(substr($value, 0, $limit)) . '...';
    }

    private function cleanWhitespace(string $value): string
    {
        return preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);
    }
}
