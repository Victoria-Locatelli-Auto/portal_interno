<?php

function searchPageAnswer(mysqli $conn, int $user_id, string $question): array
{
    $ai_engine = new AIEngine($conn, $user_id);
    $ai_answer = $ai_engine->answerQuestion($question);
    $documents_used = $ai_answer['documents'] ?? [];
    $results = [];

    foreach ($documents_used as $doc_info) {
        if (isset($doc_info['document']) && is_array($doc_info['document'])) {
            $results[] = $doc_info['document'];
        }
    }

    return [
        'ai_answer' => is_array($ai_answer) ? $ai_answer : [],
        'results' => $results,
    ];
}

function searchPageSidebarItems(mysqli $conn): array
{
    $result = $conn->query("
        SELECT id, title, url, icon, menu_type
        FROM custom_menus
        WHERE is_active = TRUE
        ORDER BY order_position ASC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function searchPageCleanResponse(array $ai_answer): string
{
    return strip_tags((string)($ai_answer['response'] ?? 'Nenhuma resposta gerada.'));
}

function searchPageConfidence(array $ai_answer): int
{
    $confidence = (float)($ai_answer['confidence'] ?? 0);
    $confidence = max(0, min(1, $confidence));

    return (int)round($confidence * 100);
}

function searchPageDocumentIcon(array $document): string
{
    $filename = strtolower((string)($document['filename'] ?? ''));

    if (strpos($filename, '.pdf') !== false) {
        return 'fas fa-file-pdf';
    }

    return 'fas fa-file-alt';
}

function searchPageExcerpt(array $document, string $query, int $limit = 200): string
{
    $text = (string)($document['extracted_text'] ?? '');

    if ($text === '') {
        return '';
    }

    $lower_text = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
    $lower_query = function_exists('mb_strtolower') ? mb_strtolower($query, 'UTF-8') : strtolower($query);
    $start = strpos($lower_text, $lower_query);

    if ($start !== false) {
        $start = max(0, $start - 50);
        $excerpt = substr($text, $start, $limit);

        return '...' . trim($excerpt) . '...';
    }

    return trim(substr($text, 0, $limit)) . '...';
}

function searchPageDate(?string $date): string
{
    if (!$date) {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('d/m/Y', $timestamp) : '-';
}
