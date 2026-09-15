<?php
$base_url = $base_url ?? '';
$requested_redirect = $requested_redirect ?? loginDefaultRedirect($base_url);
$error = $error ?? '';
$success = $success ?? '';
$funcoes_data = $funcoes ?? [];
$filiais_data = $filiais ?? [];
$funcoes = is_array($funcoes_data) ? $funcoes_data : [];
$filiais = is_array($filiais_data) ? $filiais_data : [];
$show_register_tab = !empty($show_register_tab);
$show_login_tab = !empty($show_login_tab);
$login_css_version = file_exists(__DIR__ . '/../../css/login.css') ? filemtime(__DIR__ . '/../../css/login.css') : time();
$login_js_version = file_exists(__DIR__ . '/../../js/pages/login.js') ? filemtime(__DIR__ . '/../../js/pages/login.js') : time();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Portal MCM</title>

    <link rel="icon" type="image/svg+xml" href="/basemcm/icon-mcm.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/login.css?v=<?php echo $login_css_version; ?>">
</head>
<body>
    <div class="login-wrap">
        <section class="login-art" aria-label="Frota MCM">
            <div class="art-copy"></div>
        </section>

        <main class="login-box">
            <section class="login-card">
                <span class="login-logo-box">
                    <img src="index_files/logo_mcm.webp" alt="MCM Logo">
                </span>

                <h2 id="card-title">Bem-vindo</h2>
                <p>Um espaço para colaboradores acessarem, de forma rápida e prática, informações, solicitações, documentos e ferramentas que facilitam o dia a dia de trabalho.</p>

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

                <div class="login-tabs">
                    <button type="button" class="tab-btn active" data-target="login-form" data-title="Login">
                        Login
                    </button>

                    <button type="button" class="tab-btn" data-target="register-form" data-title="Primeiro Acesso">
                        Primeiro Acesso
                    </button>
                </div>

                <form id="login-form" class="login-form active" method="POST">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($requested_redirect); ?>">

                    <div class="form-group">
                        <label for="login_email">
                            Usuário ou e-mail
                        </label>
                        <input
                            type="email"
                            id="login_email"
                            name="email"
                            placeholder="seu.nome@mcmtocantins.com"
                            value="<?php echo isset($_POST['action']) && $_POST['action'] === 'login' ? htmlspecialchars($_POST['email'] ?? '') : ''; ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="login_password">
                            Senha
                        </label>
                        <input
                            type="password"
                            id="login_password"
                            name="password"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Entrar
                    </button>

                    <div class="forgot-password-link">
                        <a href="forgot_password.php">
                            Esqueci minha senha
                        </a>
                    </div>
                </form>

                <form id="register-form" class="login-form" method="POST">
                    <input type="hidden" name="action" value="register">

                    <div class="form-group">
                        <label for="register_first_name">
                            <i class="fas fa-user"></i> Primeiro Nome <span class="required-mark">*</span>
                        </label>
                        <input
                            type="text"
                            id="register_first_name"
                            name="first_name"
                            value="<?php echo isset($_POST['action']) && $_POST['action'] === 'register' ? htmlspecialchars($_POST['first_name'] ?? '') : ''; ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="register_last_name">
                            <i class="fas fa-user"></i> Sobrenome <span class="required-mark">*</span>
                        </label>
                        <input
                            type="text"
                            id="register_last_name"
                            name="last_name"
                            value="<?php echo isset($_POST['action']) && $_POST['action'] === 'register' ? htmlspecialchars($_POST['last_name'] ?? '') : ''; ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="register_email">
                            <i class="fa-regular fa-envelope"></i> E-mail (@mcmtocantins.com) <span class="required-mark">*</span>
                        </label>
                        <input
                            type="email"
                            id="register_email"
                            name="email"
                            placeholder="seu.nome@mcmtocantins.com"
                            value="<?php echo isset($_POST['action']) && $_POST['action'] === 'register' ? htmlspecialchars($_POST['email'] ?? '') : ''; ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="wpp_corporativo">
                            <i class="fab fa-whatsapp"></i> WhatsApp Corporativo
                        </label>
                        <input
                            type="text"
                            id="wpp_corporativo"
                            name="wpp_corporativo"
                            placeholder="Ex: (63) 99999-9999"
                            value="<?php echo isset($_POST['action']) && $_POST['action'] === 'register' ? htmlspecialchars($_POST['wpp_corporativo'] ?? '') : ''; ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="id_filial">
                            <i class="fas fa-briefcase"></i> Filial <span class="required-mark">*</span>
                        </label>
                        <select id="id_filial" name="id_filial" required>
                            <option value="0">-- Selecione sua filial --</option>
                            <?php foreach ($filiais as $filial): ?>
                                <option
                                    value="<?php echo (int)$filial['id']; ?>"
                                    <?php echo (isset($_POST['action']) && $_POST['action'] === 'register' && (int)($_POST['id_filial'] ?? 0) === (int)$filial['id']) ? 'selected' : ''; ?>
                                >
                                    <?php echo htmlspecialchars($filial['nome_filial']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_funcao">
                            <i class="fas fa-briefcase"></i> Função <span class="required-mark">*</span>
                        </label>
                        <select id="id_funcao" name="id_funcao" required>
                            <option value="0">-- Selecione sua função --</option>
                            <?php foreach ($funcoes as $funcao): ?>
                                <option
                                    value="<?php echo (int)$funcao['id']; ?>"
                                    <?php echo (isset($_POST['action']) && $_POST['action'] === 'register' && (int)($_POST['id_funcao'] ?? 0) === (int)$funcao['id']) ? 'selected' : ''; ?>
                                >
                                    <?php echo htmlspecialchars($funcao['descricao']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="register_password">
                            <i class="fas fa-lock"></i> Senha <span class="required-mark">*</span>
                        </label>
                        <input
                            type="password"
                            id="register_password"
                            name="password"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="register_password_confirm">
                            <i class="fas fa-lock"></i> Confirmar Senha <span class="required-mark">*</span>
                        </label>
                        <input
                            type="password"
                            id="register_password_confirm"
                            name="password_confirm"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        REGISTRAR
                    </button>
                </form>
            </section>

            <?php require __DIR__ . '/../footer.php'; ?>
        </main>
    </div>

    <script>
        window.loginPageState = {
            showRegisterTab: <?php echo $show_register_tab ? 'true' : 'false'; ?>,
            showLoginTab: <?php echo $show_login_tab ? 'true' : 'false'; ?>
        };
    </script>
    <script src="js/pages/login.js?v=<?php echo $login_js_version; ?>"></script>
</body>
</html>
