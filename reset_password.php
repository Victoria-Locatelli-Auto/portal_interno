<?php
/**
 * Reset Password Page - Portal MCM
 * Página para redefinir senha usando token enviado por email
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/Session.php';

Session::start();

// Se já está autenticado, redireciona para home
if (Session::isAuthenticated()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$token = trim((string)($_GET['token'] ?? ''));
$valid_token = false;
$email = '';

// Verifica se o token é válido
if (!empty($token)) {
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        $error = 'Link de recuperação inválido.';
    }

    if (empty($error)) {
    $stmt = $conn->prepare("SELECT email, expires_at FROM password_reset_tokens WHERE token = ? AND used = FALSE");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $token_data = $result->fetch_assoc();
        $email = $token_data['email'];
        $expires_at = $token_data['expires_at'];
        
        // Verifica se o token não expirou
        if (strtotime($expires_at) > time()) {
            $valid_token = true;
        } else {
            $error = 'Este link de recuperação expirou. Por favor, solicite um novo.';
        }
    } else {
        $error = 'Link de recuperação inválido ou já utilizado.';
    }
    }
} else {
    $error = 'Token de recuperação não fornecido.';
}

// Processa a redefinição de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['password_confirm'] ?? '';
    
    if (empty($new_password) || empty($confirm_password)) {
        $error = 'Por favor, preencha todos os campos';
    } elseif ($new_password !== $confirm_password) {
        $error = 'As senhas não correspondem';
    } elseif (strlen($new_password) < 6) {
        $error = 'A senha deve ter no mínimo 6 caracteres';
    } else {
        // Hash da nova senha
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Atualiza a senha do usuário
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update_stmt->bind_param("ss", $password_hash, $email);
        
        if ($update_stmt->execute()) {
            // Marca o token como usado
            $mark_used = $conn->prepare("UPDATE password_reset_tokens SET used = TRUE WHERE token = ?");
            $mark_used->bind_param("s", $token);
            $mark_used->execute();
            
            $success = 'Senha redefinida com sucesso! Você já pode fazer login com sua nova senha.';
            $valid_token = false; // Esconde o formulário
        } else {
            $error = 'Erro ao redefinir senha. Por favor, tente novamente.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Redefinir Senha - Portal MCM</title>

<link rel="icon" type="image/svg+xml" href="/basemcm/icon-mcm.svg">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --azul: #03285f;
    --azul-borda: #1e90ff;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #d9d9d9;
    min-height: 100vh;
    padding: 14px;
}

.page {

    min-height: calc(100vh - 28px);

    display: flex;
    flex-direction: column;

    background:
        linear-gradient(rgba(3,40,95,0.4), rgba(3,40,95,0.3)),
        url('./index_files/bg-login.jpg') center / cover no-repeat;
}

.topbar {
    background: var(--azul);
    height: 70px;
    display: flex;
    align-items: center;
    padding: 0 20px;
}

.topbar img {
    height: 40px;
}

.main {
    flex: 1;
    display: flex;
    justify-content: space-between;
    padding: 50px;
}

.left {
    color: white;
    max-width: 550px;
}

.left h1 {
    font-size: 40px;
}

.card {

    width: 420px;

    background: white;
    border: 3px solid var(--azul-borda);
    border-radius: 18px;

    padding: 25px;
}

.card h2 {
    text-align: center;
    color: var(--azul);
    margin-bottom: 15px;
}

.form-group {
    margin-bottom: 12px;
}

.form-group input {

    width: 100%;
    height: 42px;

    border-radius: 10px;
    border: 1px solid #16396b;

    padding: 10px;
}

.btn {

    width: 100%;
    height: 42px;

    background: var(--azul);
    color: white;

    border: none;
    border-radius: 10px;
    cursor: pointer;
}

.alert {
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 10px;
}

.alert-error {
    background: #fee2e2;
}

.alert-success {
    background: #dcfce7;
}

.footer {
    height: 30px;
    background: var(--azul);
    color: white;
    text-align: center;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

</style>
</head>

<body>

<div class="page">

<div class="topbar">
<img src="./index_files/logo_mcm.webp">
</div>

<div class="main">

<div class="left">

<h1>Portal MCM</h1>
<p>Redefinição de senha segura</p>

</div>


<div class="card">

<h2>Redefinir Senha</h2>


<?php if ($error): ?>
<div class="alert alert-error">
<?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>


<?php if ($success): ?>

<div class="alert alert-success">
<?= $success ?>
</div>

<a href="login.php">
<button class="btn">Ir para login</button>
</a>

<?php endif; ?>


<?php if ($valid_token && !$success): ?>

<form method="POST">

<div class="form-group">
<input type="password" name="password" placeholder="Nova senha" required>
</div>

<div class="form-group">
<input type="password" name="password_confirm" placeholder="Confirmar senha" required>
</div>

<button class="btn">
Redefinir senha
</button>

</form>

<?php endif; ?>


<div style="margin-top:10px;text-align:center">

<a href="login.php">
Voltar ao login
</a>

</div>

</div>

</div>

<div class="footer">
© 2026 Desenvolvido por PPGC/TI
</div>

</div>

</body>
</html>
