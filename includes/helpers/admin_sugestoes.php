<?php

function adminSuggestionsCanView(): bool
{
    return Session::getAccessLevel() === 'admin';
}

function adminSuggestionsApiUrl(string $base_url): string
{
    return $base_url . '/api/sugestoes.php';
}
