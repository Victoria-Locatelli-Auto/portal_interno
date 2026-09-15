<?php
/**
 * Forgot Password Page - Portal MCM
 * Página para solicitar recuperação de senha via email
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Session.php';
require_once __DIR__ . '/includes/emailmanager.php';

Session::start();

// Se já está autenticado, redireciona para home
if (Session::isAuthenticated()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

function ensurePasswordResetTable(mysqli $conn): bool
{
    $sql = "CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `email` varchar(255) NOT NULL,
        `token` varchar(64) NOT NULL,
        `expires_at` datetime NOT NULL,
        `used` tinyint(1) DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `token` (`token`),
        KEY `email` (`email`),
        KEY `expires_at` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    return $conn->query($sql) === true;
}

// Processa solicitação de reset de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!empty($email)) {
        // Valida formato do email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Por favor, informe um email válido';
        } else {
            // Verifica se o email existe no banco de dados
            $stmt = $conn->prepare("SELECT id, first_name, username, email FROM users WHERE email = ? AND is_active = TRUE");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // Gera token único
                $token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

                if (!ensurePasswordResetTable($conn)) {
                    $error = 'Erro ao preparar recuperação de senha. Tente novamente em instantes.';
                }

                if (empty($error)) {
                    // Remove tokens antigos do mesmo email
                    $delete_stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE email = ?");
                    $delete_stmt->bind_param("s", $email);
                    $delete_stmt->execute();

                    // Insere novo token
                    $insert_stmt = $conn->prepare("INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (?, ?, ?)");
                    $insert_stmt->bind_param("sss", $email, $token, $expires_at);

                    if ($insert_stmt->execute()) {
                        $first_name = trim((string)($user['first_name'] ?? ''));
                        if ($first_name === '') {
                            $first_name = (string)($user['username'] ?? 'usuário');
                        }

                        $email_manager = new EmailManager();
                        $email_result = $email_manager->sendPasswordResetEmail($email, $first_name, $token);

                        if (!empty($email_result['success'])) {
                            $success = 'Se o email informado estiver cadastrado, você receberá instruções para recuperar sua senha.';
                        } else {
                            $error = 'Erro ao enviar email de recuperação. Tente novamente mais tarde.';
                            error_log('ForgotPassword send error: ' . ($email_result['message'] ?? 'erro desconhecido'));
                        }
                    } else {
                        $error = 'Erro ao processar solicitação. Por favor, tente novamente.';
                    }
                }
            } else {
                // Por segurança, mostra a mesma mensagem mesmo se o email não existir
                $success = 'Se o email informado estiver cadastrado, você receberá instruções para recuperar sua senha.';
            }
        }
    } else {
        $error = 'Por favor, informe seu email';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci a Senha - Portal MCM</title>
    <link rel="icon" type="image/svg+xml" href="/basemcm/icon-mcm.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/forgot_password.css">
</head>
<body>
    <div class="login-wrap">
        <section class="login-art" aria-label="Frota MCM">
            <div class="art-copy"></div>
        </section>

        <main class="login-box">
            <section class="login-card">
                <div class="login-logo-box">
                    <img src="./index_files/logo_mcm.webp" alt="MCM">
                </div>

                <h2>Recuperar Senha</h2>
                <p>
                    Solicite a recuperação de senha de forma rápida e segura.
                    Informe seu e-mail corporativo para receber as instruções de acesso.
                </p>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="forgot-form">
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> E-mail
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="seu.nome@mcmtocantins.com"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            required
                            autofocus
                        >
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Enviar Link de Recuperação
                    </button>
                </form>

                <div class="actions">
                    <a href="login.php">
                        <i class="fas fa-arrow-left"></i> Voltar ao Login
                    </a>
                </div>
            </section>

            <?php require __DIR__ . '/includes/footer.php'; ?>
        </main>
    </div>
</body>
</html>
