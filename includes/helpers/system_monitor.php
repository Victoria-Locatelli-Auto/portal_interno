<?php

function systemMonitorCurrentUser(mysqli $conn, int $user_id): ?array
{
    $stmt = $conn->prepare('SELECT access_level FROM users WHERE id = ? LIMIT 1');

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    return $user ?: null;
}

function systemMonitorCanAccess(?array $user): bool
{
    return strtolower(trim((string)($user['access_level'] ?? ''))) === 'admin';
}

function systemMonitorSidebarItems(mysqli $conn): array
{
    $result = $conn->query("
        SELECT id, title, url, icon, menu_type
        FROM custom_menus
        WHERE is_active = TRUE
        ORDER BY order_position ASC
    ");

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function systemMonitorParseDefineValue(string $file_path, string $constant_name): ?string
{
    if (!is_file($file_path)) {
        return null;
    }

    $content = @file_get_contents($file_path);

    if ($content === false) {
        return null;
    }

    $pattern = "/define\\(\\s*'" . preg_quote($constant_name, '/') . "'\\s*,\\s*'([^']*)'\\s*\\)/";

    if (preg_match($pattern, $content, $match) === 1) {
        return $match[1];
    }

    return null;
}

function systemMonitorCheckMysqlConnection(?string $host, ?string $user, ?string $pass, ?string $db): array
{
    if (!$host || !$user || $pass === null || !$db) {
        return [
            'ok' => false,
            'message' => 'Credenciais incompletas para teste.',
        ];
    }

    mysqli_report(MYSQLI_REPORT_OFF);

    $test_conn = @new mysqli($host, $user, $pass, $db);

    if ($test_conn->connect_errno) {
        return [
            'ok' => false,
            'message' => $test_conn->connect_error . ' (code: ' . $test_conn->connect_errno . ')',
        ];
    }

    $test_conn->close();

    return [
        'ok' => true,
        'message' => 'Conexao OK',
    ];
}

function systemMonitorDefaultDatabaseStatus(): array
{
    $config = __DIR__ . '/../../config/database.php';

    return systemMonitorCheckMysqlConnection(
        systemMonitorParseDefineValue($config, 'DB_HOST'),
        systemMonitorParseDefineValue($config, 'DB_USER'),
        systemMonitorParseDefineValue($config, 'DB_PASS'),
        systemMonitorParseDefineValue($config, 'DB_NAME')
    );
}

function systemMonitorAuthDatabaseStatus(): array
{
    $config = '/var/www/basemcm/config/auth.php';

    return systemMonitorCheckMysqlConnection(
        systemMonitorParseDefineValue($config, 'AUTH_DB_HOST'),
        systemMonitorParseDefineValue($config, 'AUTH_DB_USER'),
        systemMonitorParseDefineValue($config, 'AUTH_DB_PASS'),
        systemMonitorParseDefineValue($config, 'AUTH_DB_NAME')
    );
}

function systemMonitorApacheLogLines(): array
{
    $log_path = '/var/log/apache2/error.log';

    if (is_file($log_path)) {
        $lines = @file($log_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        return $lines ? array_slice($lines, -400) : [];
    }

    if (!function_exists('shell_exec')) {
        return [];
    }

    $log_raw = (string)@shell_exec('tail -n 400 /var/log/apache2/error.log 2>/dev/null');

    return $log_raw !== '' ? explode("\n", trim($log_raw)) : [];
}

function systemMonitorFilteredLogLines(string $needle, int $limit = 20): array
{
    $matches = [];

    foreach (systemMonitorApacheLogLines() as $line) {
        if (strpos($line, $needle) !== false) {
            $matches[] = $line;
        }
    }

    return array_slice($matches, -$limit);
}
