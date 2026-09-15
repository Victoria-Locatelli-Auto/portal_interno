<?php
/**
 * Verify Email Page - Portal MCM
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Session.php';

Session::start();

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$status = 'error';
$message = '';

if (empty($token)) {
    $message = 'Token de verificação não informado.';
} else {
    $stmt = $conn->prepare("SELECT id, email, first_name, email_verified FROM users WHERE verification_token = ? LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (!empty($user['email_verified'])) {
            $status = 'already';
            $message = 'Este email já estava verificado. Você já pode acessar o portal.';
        } else {
            $update = $conn->prepare("UPDATE users SET email_verified = TRUE, verification_token = NULL, can_import = TRUE WHERE id = ?");
            $update->bind_param("i", $user['id']);

            if ($update->execute()) {
                $status = 'success';
                $message = '✅ Email verificado com sucesso! Sua conta está ativa.';
            } else {
                $message = 'Erro ao verificar email. Por favor, tente novamente.';
            }
        }
    } else {
        $message = 'Link de verificação inválido ou expirado.';
    }
}

$alert_class = $status === 'error' ? 'alert-error' : 'alert-success';
$icon_class = $status === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de Email - Portal MCM</title>
    <link rel="icon" type="image/svg+xml" href="/basemcm/icon-mcm.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="./index_files/logo_mcm.webp" alt="MCM Logo" class="login-logo">
                <h1>Verificação de Email</h1>
            </div>

            <div class="alert <?php echo $alert_class; ?>">
                <i class="fas <?php echo $icon_class; ?>"></i> <?php echo htmlspecialchars($message); ?>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Voltar para o início
                </a>
            </div>

            <?php if ($status === 'success' || $status === 'already'): ?>
                <p style="color: #666; margin-top: 15px; text-align: center; font-size: 14px;">
                    Você já pode fazer seu primeiro acesso no portal.
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
