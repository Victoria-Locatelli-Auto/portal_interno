<?php
/**
 * Header/Menu Padrão do Portal MCM
 * Usado em todas as páginas com controle de permissões
 */

require_once __DIR__ . '/../includes/Session.php';

Session::start();

// Se não está autenticado, redireciona para login
if (!Session::isAuthenticated()) {
    header('Location: ' . (dirname(__FILE__) === __DIR__ ? '../login.php' : 'login.php'));
    exit;
}

require_once __DIR__ . '/../config/database.php';

// Obter informações do usuário
$user_id = Session::getUserId();
$user_result = $conn->query("SELECT access_level FROM users WHERE id = $user_id");
$user = $user_result->fetch_assoc();
$user_level = $user['access_level'] ?? 'viewer';

// Determinar página atual para ativar link
$current_page = basename($_SERVER['PHP_SELF']);
$is_pages_dir = strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
?>

<header>
    <a href="<?php echo $is_pages_dir ? '../index.php' : 'index.php'; ?>" class="header-logo-link">
        <img src="<?php echo $is_pages_dir ? '../img/logo_mcm.webp' : 'img/logo_mcm.webp'; ?>" alt="MCM Logo" class="logo">
    </a>
    
    <nav>
        <!-- Início - Para todos -->
        <a href="<?php echo $is_pages_dir ? '../index.php' : 'index.php'; ?>" 
           class="nav-link <?php echo ($current_page === 'index.php') ? 'active' : ''; ?>">
            Início
        </a>
        
        <!-- Importar PDF - Admin, Editor e RH -->
        <?php if ($user_level === 'admin' || $user_level === 'editor' || $user_level === 'RH'): ?>
            <a href="<?php echo $is_pages_dir ? 'import.php' : 'pages/import.php'; ?>" 
               class="nav-link <?php echo ($current_page === 'import.php') ? 'active' : ''; ?>">
                Importar PDF
            </a>
        <?php endif; ?>
        
        <!-- Avisos - Admin, Editor e RH -->
        <?php if ($user_level === 'admin' || $user_level === 'editor' || $user_level === 'RH'): ?>
            <a href="<?php echo $is_pages_dir ? 'manage_notices.php' : 'pages/manage_notices.php'; ?>" 
               class="nav-link <?php echo ($current_page === 'manage_notices.php') ? 'active' : ''; ?>">
                Avisos
            </a>
        <?php endif; ?>
        
        <!-- Menu - Apenas Admin -->
        <?php if ($user_level === 'admin'): ?>
            <a href="<?php echo $is_pages_dir ? 'manage_menus.php' : 'pages/manage_menus.php'; ?>" 
               class="nav-link <?php echo ($current_page === 'manage_menus.php') ? 'active' : ''; ?>">
                Menus
            </a>
        <?php endif; ?>

        <!-- Base MCM - Admin, Gestor, Marketing e RH -->
        <?php if ($user_level === 'admin' || $user_level === 'Gestor' || $user_level === 'Marketing' || $user_level === 'RH'): ?>
            <a href="http://portalmcm.mcmtocantins.com.br/basemcm/" target="_blank" class="nav-link">
                Base MCM
            </a>
        <?php endif; ?>
        
        <!-- Aprovar - Apenas Admin e Editor -->
        <?php if ($user_level === 'admin' || $user_level === 'editor'): ?>
            <a href="<?php echo $is_pages_dir ? 'approve_documents.php' : 'pages/approve_documents.php'; ?>" 
               class="nav-link <?php echo ($current_page === 'approve_documents.php') ? 'active' : ''; ?>">
                Aprovar
            </a>
        <?php endif; ?>
        

        <!-- Admin - Apenas Admin -->
        <?php if ($user_level === 'admin'): ?>
            <a href="<?php echo $is_pages_dir ? '../admin.php' : 'admin.php'; ?>" 
               class="nav-link <?php echo ($current_page === 'admin.php') ? 'active' : ''; ?>">
                Admin
            </a>
        <?php endif; ?>
        
        <span class="user-info">
            <?php echo htmlspecialchars(Session::getUsername()); ?>
        </span>
        <a href="<?php echo $is_pages_dir ? '../logout.php' : 'logout.php'; ?>" class="nav-link logout">
            Sair
        </a>
    </nav>
</header>
