<?php
// Copie este arquivo para ai.php e configure chaves reais apenas no ambiente.

return [
    'api_key' => getenv('AI_API_KEY') ?: '',
    'model' => getenv('AI_MODEL') ?: 'gpt-4o-mini',
    'max_tokens' => (int) (getenv('AI_MAX_TOKENS') ?: 1000),
];
