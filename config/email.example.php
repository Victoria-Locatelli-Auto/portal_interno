<?php
// Copie este arquivo para email.php e configure o SMTP real do ambiente.

return [
    'SMTP_HOST' => getenv('SMTP_HOST') ?: 'smtp.exemplo.com',
    'SMTP_PORT' => (int) (getenv('SMTP_PORT') ?: 587),
    'SMTP_USERNAME' => getenv('SMTP_USERNAME') ?: 'usuario@example.com',
    'SMTP_PASSWORD' => getenv('SMTP_PASSWORD') ?: 'senha-do-smtp',
    'SMTP_FROM_EMAIL' => getenv('SMTP_FROM_EMAIL') ?: 'noreply@example.com',
    'SMTP_FROM_NAME' => getenv('SMTP_FROM_NAME') ?: 'Portal MCM',
    'EMAIL_VERIFICATION_URL' => '/validate-email.php',
    'EMAIL_TEMPLATES' => [
        'verification' => [
            'subject' => 'Verifique seu e-mail',
            'template' => 'emails/verification.html',
        ],
        'password_reset' => [
            'subject' => 'Recuperacao de senha',
            'template' => 'emails/password-reset.html',
        ],
    ],
];
