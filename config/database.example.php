<?php
// Copie este arquivo para database.php e configure as credenciais do ambiente.

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'usuario_do_banco');
define('DB_PASS', getenv('DB_PASS') ?: 'senha_do_banco');
define('DB_NAME', getenv('DB_NAME') ?: 'nome_do_banco');
