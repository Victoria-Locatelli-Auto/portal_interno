<?php
// Copie este arquivo para training.php e configure os dados reais do ambiente.

if (!defined('TRAINING_URL')) {
    define('TRAINING_URL', getenv('TRAINING_URL') ?: 'http://localhost:5174/');
}

if (!defined('TRAINING_JWT_SECRET')) {
    define('TRAINING_JWT_SECRET', getenv('TRAINING_JWT_SECRET') ?: 'troque-esta-chave-no-ambiente');
}

if (!defined('TRAINING_JWT_AUDIENCE')) {
    define('TRAINING_JWT_AUDIENCE', getenv('TRAINING_JWT_AUDIENCE') ?: 'treinamentos');
}

if (!defined('TRAINING_JWT_TTL')) {
    define('TRAINING_JWT_TTL', (int) (getenv('TRAINING_JWT_TTL') ?: 300));
}
