<?php

function jwtBase64UrlEncode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function jwtCreateHs256(array $payload, string $secret): string
{
    $header = [
        'alg' => 'HS256',
        'typ' => 'JWT',
    ];

    $headerEncoded = jwtBase64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    $payloadEncoded = jwtBase64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $secret, true);

    return $headerEncoded . '.' . $payloadEncoded . '.' . jwtBase64UrlEncode($signature);
}
