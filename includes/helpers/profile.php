<?php

function formatProfileName(string $text): string
{
    $text = trim($text);
    $text = mb_strtolower($text, 'UTF-8');
    $text = mb_convert_case($text, MB_CASE_TITLE, 'UTF-8');

    $lower_words = ['Da', 'De', 'Do', 'Das', 'Dos', 'E'];
    foreach ($lower_words as $word) {
        $text = preg_replace('/\b' . $word . '\b/u', mb_strtolower($word, 'UTF-8'), $text);
    }

    return $text;
}

function formatProfileRole(?string $text): string
{
    if (!$text) {
        return '-';
    }

    $text = trim($text);
    $text = mb_strtolower($text, 'UTF-8');
    $text = mb_convert_case($text, MB_CASE_TITLE, 'UTF-8');

    $replacements = [
        ' Jr' => ' JR',
        ' Ti' => ' TI',
        ' Rh' => ' RH',
        ' Mcm' => ' MCM',
    ];

    return str_replace(array_keys($replacements), array_values($replacements), $text);
}
