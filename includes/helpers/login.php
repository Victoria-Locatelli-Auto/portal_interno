<?php

function loginBaseUrl(): string
{
    $base_url = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/login.php')), '/');

    return ($base_url === '/' || $base_url === '.') ? '' : $base_url;
}

function loginDefaultRedirect(string $base_url): string
{
    return $base_url . '/index.php';
}

function sanitizeRedirectTarget(string $target, string $base_url): string
{
    $default_redirect = loginDefaultRedirect($base_url);
    $target = trim($target);

    if ($target === '') {
        return $default_redirect;
    }

    // Permite apenas caminhos locais absolutos, sem host externo.
    if ($target[0] !== '/' || preg_match('/^\/\//', $target) === 1) {
        return $default_redirect;
    }

    $target_path = parse_url($target, PHP_URL_PATH) ?: $target;

    if ($base_url !== '' && strpos($target_path, $base_url . '/') === 0) {
        return $target;
    }

    if ($base_url === '' && (
        $target_path === '/index.php' ||
        $target_path === '/admin.php' ||
        strpos($target_path, '/pages/') === 0
    )) {
        return $target;
    }

    if (strpos($target_path, '/agendoulevou/') === 0 || strpos($target_path, '/gestao/') === 0) {
        return $target;
    }

    return $default_redirect;
}

function loginLookupCacheFile(): string
{
    return sys_get_temp_dir() . '/portalmcm_login_lookup_cache.json';
}

function loginReadLookupCache(): array
{
    $file = loginLookupCacheFile();

    if (!is_file($file)) {
        return ['fresh' => false, 'funcoes' => [], 'filiais' => []];
    }

    $data = json_decode((string) @file_get_contents($file), true);

    if (!is_array($data)) {
        return ['fresh' => false, 'funcoes' => [], 'filiais' => []];
    }

    $created_at = (int) ($data['created_at'] ?? 0);

    return [
        'fresh' => $created_at > 0 && (time() - $created_at) < 86400,
        'funcoes' => is_array($data['funcoes'] ?? null) ? $data['funcoes'] : [],
        'filiais' => is_array($data['filiais'] ?? null) ? $data['filiais'] : [],
    ];
}

function loginWriteLookupCache(array $funcoes, array $filiais): void
{
    $file = loginLookupCacheFile();

    @file_put_contents($file, json_encode([
        'created_at' => time(),
        'funcoes' => $funcoes,
        'filiais' => $filiais,
    ], JSON_UNESCAPED_UNICODE), LOCK_EX);
    @chmod($file, 0664);
}

function loginLoadLookups(mysqli $conn): array
{
    $funcoes = [];
    $filiais = [];

    $funcoes_result = $conn->query("
        SELECT id, descricao
        FROM funcao
        WHERE ativo = 1
        ORDER BY descricao ASC
    ");

    if ($funcoes_result) {
        $funcoes = $funcoes_result->fetch_all(MYSQLI_ASSOC);
    }

    $filiais_result = $conn->query("
        SELECT id, nome_filial
        FROM filiais
        WHERE ativo = 1
        ORDER BY nome_filial ASC
    ");

    if ($filiais_result) {
        $filiais = $filiais_result->fetch_all(MYSQLI_ASSOC);
    }

    if (!empty($funcoes) || !empty($filiais)) {
        loginWriteLookupCache($funcoes, $filiais);
    }

    return [
        'funcoes' => $funcoes,
        'filiais' => $filiais,
    ];
}

function loginApplySession(array $result): void
{
    Session::set('user_id', $result['user_id']);
    Session::set('username', $result['username']);
    Session::set('first_name', $result['first_name']);
    Session::set('last_name', $result['last_name']);
    Session::set('email', $result['email']);
    Session::set('access_level', $result['access_level'] ?? 'viewer');
    Session::set('can_import', $result['can_import'] ?? false);
    Session::set('id_funcao', $result['id_funcao'] ?? null);
    Session::set('id_filial', $result['id_filial'] ?? null);
}

function loginHandleLogin(mysqli $conn, array $post, string $base_url): string
{
    $email = strtolower(trim($post['email'] ?? ''));
    $password = $post['password'] ?? '';

    if ($email === '' || $password === '') {
        return 'Por favor, preencha todos os campos';
    }

    $user = new User($conn);
    $result = $user->login($email, $password);

    if (!empty($result['success'])) {
        loginApplySession($result);

        $redirect_after_login = sanitizeRedirectTarget(
            (string)($post['redirect'] ?? $_SESSION['redirect_after_login'] ?? ''),
            $base_url
        );

        unset($_SESSION['redirect_after_login']);

        header('Location: ' . $redirect_after_login);
        exit;
    }

    return $result['message'] ?? 'Erro ao fazer login';
}

function loginHandleRegister(mysqli $conn, array $post): string
{
    $first_name = trim($post['first_name'] ?? '');
    $last_name = trim($post['last_name'] ?? '');
    $email = strtolower(trim($post['email'] ?? ''));
    $password = $post['password'] ?? '';
    $password_confirm = $post['password_confirm'] ?? '';
    $wpp_corporativo = trim($post['wpp_corporativo'] ?? '');
    $id_funcao = (int)($post['id_funcao'] ?? 0);
    $id_filial = (int)($post['id_filial'] ?? 0);

    if (
        $first_name === '' ||
        $last_name === '' ||
        $email === '' ||
        $password === '' ||
        $password_confirm === '' ||
        $id_funcao <= 0 ||
        $id_filial <= 0
    ) {
        return 'Por favor, preencha todos os campos obrigatórios';
    }

    if (!preg_match('/@mcmtocantins\.com$/', $email)) {
        return '⚠️ Apenas emails @mcmtocantins.com são permitidos!';
    }

    if ($password !== $password_confirm) {
        return 'As senhas não correspondem';
    }

    if (strlen($password) < 6) {
        return 'A senha deve ter no mínimo 6 caracteres';
    }

    try {
        $username = explode('@', $email)[0];

        if ($username === '') {
            throw new Exception('Não foi possível gerar o nome de usuário.');
        }

        $check_stmt = $conn->prepare("
            SELECT id
            FROM users
            WHERE email = ? OR username = ?
            LIMIT 1
        ");

        if (!$check_stmt) {
            throw new Exception('Erro ao verificar usuário: ' . $conn->error);
        }

        $check_stmt->bind_param('ss', $email, $username);
        $check_stmt->execute();
        $existing_user = $check_stmt->get_result()->fetch_assoc();

        if ($existing_user) {
            return 'Já existe um usuário cadastrado com esse e-mail.';
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $wpp_corporativo = $wpp_corporativo !== '' ? $wpp_corporativo : null;

        $register_stmt = $conn->prepare("
            INSERT INTO users (
                id_funcao,
                id_filial,
                username,
                email,
                first_name,
                last_name,
                password,
                email_verified,
                verification_token,
                access_level,
                can_import,
                created_at,
                last_login,
                is_active,
                wpp_corporativo
            ) VALUES (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                0,
                NULL,
                'viewer',
                0,
                NOW(),
                NULL,
                1,
                ?
            )
        ");

        if (!$register_stmt) {
            throw new Exception('Erro ao preparar cadastro: ' . $conn->error);
        }

        $register_stmt->bind_param(
            'iissssss',
            $id_funcao,
            $id_filial,
            $username,
            $email,
            $first_name,
            $last_name,
            $password_hash,
            $wpp_corporativo
        );

        if ($register_stmt->execute()) {
            Session::set('register_success', 'Cadastro realizado com sucesso! Faça login para continuar.');
            header('Location: login.php?registered=1');
            exit;
        }

        return 'Erro ao registrar usuário: ' . $register_stmt->error;
    } catch (Exception $e) {
        return 'Erro ao registrar: ' . $e->getMessage();
    }
}

function loginPreparePage(string $root_dir): array
{
    $base_url = loginBaseUrl();
    $requested_redirect = sanitizeRedirectTarget(
        (string)($_GET['redirect'] ?? $_GET['return_url'] ?? $_POST['redirect'] ?? $_SESSION['redirect_after_login'] ?? ''),
        $base_url
    );

    $_SESSION['redirect_after_login'] = $requested_redirect;

    if (Session::isAuthenticated()) {
        header('Location: ' . $requested_redirect);
        exit;
    }

    $error = '';
    $success = '';
    $lookup_cache = loginReadLookupCache();
    $funcoes = $lookup_cache['funcoes'];
    $filiais = $lookup_cache['filiais'];
    $db_unavailable = false;
    $should_load_db = $_SERVER['REQUEST_METHOD'] === 'POST' || !$lookup_cache['fresh'];

    if ($should_load_db) {
        require_once $root_dir . '/config/database.php';
        require_once $root_dir . '/includes/user.php';
        $db_unavailable = !isset($conn) || !($conn instanceof mysqli) || !empty($GLOBALS['db_unavailable']);
    }

    if ($db_unavailable) {
        $error = 'Login temporariamente indisponível por limite de conexões com o banco. Tente novamente em alguns minutos.';
    }

    if (!$db_unavailable && $should_load_db) {
        $lookups = loginLoadLookups($conn);
        $funcoes = $lookups['funcoes'];
        $filiais = $lookups['filiais'];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($db_unavailable) {
            $error = 'Não foi possível autenticar agora porque o banco atingiu o limite temporário de conexões. Aguarde alguns minutos e tente novamente.';
        } elseif ($_POST['action'] === 'login') {
            $error = loginHandleLogin($conn, $_POST, $base_url);
        } elseif ($_POST['action'] === 'register') {
            $error = loginHandleRegister($conn, $_POST);
        }
    }

    if (isset($_GET['registered']) && $_GET['registered'] === '1') {
        $success = Session::get('register_success', '');
        Session::remove('register_success');
    }

    return [
        'base_url' => $base_url,
        'requested_redirect' => $requested_redirect,
        'error' => $error,
        'success' => $success,
        'funcoes' => $funcoes,
        'filiais' => $filiais,
        'show_register_tab' => (isset($_GET['register']) && $_GET['register'] === '1') ||
            ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'register' && $error !== ''),
        'show_login_tab' => $success !== '' && isset($_GET['registered']),
    ];
}
