<?php
/**
 * Painel de Administração - Gerenciar Usuários
 */

require_once __DIR__ . '/includes/Session.php';
require_once __DIR__ . '/config/database.php';

Session::start();

if (!Session::isAuthenticated()) {
    header('Location: login.php');
    exit;
}

$base_url = appBaseUrl();

$user_id = Session::getUserId();

$stmt = $conn->prepare("SELECT access_level FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current_user = $stmt->get_result()->fetch_assoc();

if (!$current_user || $current_user['access_level'] !== 'admin') {
    die('<h1>❌ Acesso Negado</h1><p>Apenas administradores podem acessar esta página.</p>');
}

$logged_username = Session::getUsername();
$first_name = Session::get('first_name') ?? 'Usuário';

$custom_menus = [];
$menus_result = $conn->query("
    SELECT id, title, url, icon, menu_type
    FROM custom_menus
    WHERE is_active = TRUE
    ORDER BY order_position ASC
");

if ($menus_result) {
    $custom_menus = $menus_result->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $target_user_id = (int)($_POST['user_id'] ?? 0);

    if ($target_user_id > 0) {
        $role_actions = [
            'set_admin' => 'admin',
            'set_editor' => 'editor',
            'set_viewer' => 'viewer',
            'set_gestor' => 'Gestor',
            'set_analista' => 'Analista',
            'set_coordenador' => 'Coordenador',
            'set_marketing' => 'Marketing',
            'set_rh' => 'RH',
        ];

        if (isset($role_actions[$action])) {
            $new_access_level = $role_actions[$action];

            $update_stmt = $conn->prepare("
                UPDATE users
                SET access_level = ?
                WHERE id = ?
            ");
            $update_stmt->bind_param("si", $new_access_level, $target_user_id);
            $update_stmt->execute();

        } elseif ($action === 'approve_import') {
            $update_stmt = $conn->prepare("
                UPDATE users
                SET can_import = TRUE, email_verified = TRUE
                WHERE id = ?
            ");
            $update_stmt->bind_param("i", $target_user_id);
            $update_stmt->execute();

        } elseif ($action === 'revoke_import') {
            $update_stmt = $conn->prepare("
                UPDATE users
                SET can_import = FALSE
                WHERE id = ?
            ");
            $update_stmt->bind_param("i", $target_user_id);
            $update_stmt->execute();

        } elseif ($action === 'inativar_usuario') {
            $update_stmt = $conn->prepare("
                UPDATE users
                SET is_active = FALSE
                WHERE id = ?
            ");
            $update_stmt->bind_param("i", $target_user_id);
            $update_stmt->execute();

        } elseif ($action === 'ativar_usuario') {
            $update_stmt = $conn->prepare("
                UPDATE users
                SET is_active = TRUE
                WHERE id = ?
            ");
            $update_stmt->bind_param("i", $target_user_id);
            $update_stmt->execute();

        } elseif ($action === 'resetar_senha') {
            $senha_padrao = 'Mcm@2026';
            $senha_hash = password_hash($senha_padrao, PASSWORD_DEFAULT);

            $user_stmt = $conn->prepare("
                SELECT first_name, last_name, username
                FROM users
                WHERE id = ?
                LIMIT 1
            ");
            $user_stmt->bind_param("i", $target_user_id);
            $user_stmt->execute();
            $target_user = $user_stmt->get_result()->fetch_assoc();

            $nome_usuario = trim(
                (string)($target_user['first_name'] ?? '') . ' ' . (string)($target_user['last_name'] ?? '')
            );

            if ($nome_usuario === '') {
                $nome_usuario = $target_user['username'] ?? 'usuário';
            }

            $update_stmt = $conn->prepare("
                UPDATE users
                SET password = ?
                WHERE id = ?
            ");
            $update_stmt->bind_param("si", $senha_hash, $target_user_id);

            if ($update_stmt->execute()) {
                Session::set(
                    'reset_password_modal',
                    'Senha do usuário ' . $nome_usuario . ' alterada com sucesso.'
                );
            }
        }
    }

    $redirect_params = $_GET;
    $redirect_url = 'admin.php';

    if (!empty($redirect_params)) {
        $redirect_url .= '?' . http_build_query($redirect_params);
    }

    header('Location: ' . $redirect_url);
    exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$order = isset($_GET['order']) ? $_GET['order'] : 'created_at';
$dir = (isset($_GET['dir']) && strtolower($_GET['dir']) === 'asc') ? 'ASC' : 'DESC';
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$allowed_orders = ['username', 'email', 'access_level', 'created_at', 'is_active'];

if (!in_array($order, $allowed_orders, true)) {
    $order = 'created_at';
}

$allowed_per_page = [10, 20, 50];

if (!in_array($per_page, $allowed_per_page, true)) {
    $per_page = 10;
}

if ($search !== '') {
    $search_pattern = '%' . $search . '%';

    $count_stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM users
        WHERE username LIKE ?
           OR email LIKE ?
           OR access_level LIKE ?
           OR first_name LIKE ?
           OR last_name LIKE ?
    ");
    $count_stmt->bind_param(
        "sssss",
        $search_pattern,
        $search_pattern,
        $search_pattern,
        $search_pattern,
        $search_pattern
    );
    $count_stmt->execute();
    $total_users = (int)($count_stmt->get_result()->fetch_assoc()['total'] ?? 0);
} else {
    $total_users = (int)($conn->query("
        SELECT COUNT(*) AS total
        FROM users
    ")->fetch_assoc()['total'] ?? 0);
}

$total_pages = max(1, (int)ceil($total_users / $per_page));

if ($page > $total_pages) {
    $page = $total_pages;
}

$offset = ($page - 1) * $per_page;

if ($search !== '') {
    $search_pattern = '%' . $search . '%';

    $users_stmt = $conn->prepare("
        SELECT id, username, first_name, last_name, email, access_level, email_verified, created_at, is_active
        FROM users
        WHERE username LIKE ?
           OR email LIKE ?
           OR access_level LIKE ?
           OR first_name LIKE ?
           OR last_name LIKE ?
        ORDER BY $order $dir
        LIMIT ? OFFSET ?
    ");
    $users_stmt->bind_param(
        "sssssii",
        $search_pattern,
        $search_pattern,
        $search_pattern,
        $search_pattern,
        $search_pattern,
        $per_page,
        $offset
    );
} else {
    $users_stmt = $conn->prepare("
        SELECT id, username, first_name, last_name, email, access_level, email_verified, created_at, is_active
        FROM users
        ORDER BY $order $dir
        LIMIT ? OFFSET ?
    ");
    $users_stmt->bind_param("ii", $per_page, $offset);
}

$users_stmt->execute();
$users = $users_stmt->get_result();

function buildAdminQuery(array $overrides = []): string
{
    $params = $_GET;

    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
        } else {
            $params[$key] = $value;
        }
    }

    return http_build_query($params);
}

function sortDirection(string $field, string $currentField, string $currentDir): string
{
    if ($field === $currentField && $currentDir === 'ASC') {
        return 'desc';
    }

    return 'asc';
}

$reset_password_modal = Session::get('reset_password_modal');

if (!empty($reset_password_modal)) {
    Session::remove('reset_password_modal');
}

$page_title = 'Painel Admin';
$page_css = 'css/pages/admin.css';
$page_js = 'js/pages/admin.js';

require __DIR__ . '/includes/layout_start.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <div class="admin-title">
            <h1>Gerenciar Usuários</h1>
            <p class="admin-subtitle">Controle de permissões e status de usuários</p>
        </div>

        <div class="admin-meta">
            <div>
                Admin logado: <strong><?php echo htmlspecialchars((string)$logged_username); ?></strong>
            </div>

            <a class="btn-link" href="pages/system_monitor.php">
                Monitor do Sistema
            </a>
        </div>
    </div>

    <form method="GET" class="filters">
        <div class="filter-item search">
            <label for="search">Busca</label>
            <input
                id="search"
                type="text"
                name="search"
                placeholder="Usuário, nome, e-mail ou perfil"
                value="<?php echo htmlspecialchars($search); ?>"
            >
        </div>

        <div class="filter-item">
            <label for="order">Ordenar por</label>
            <select id="order" name="order">
                <option value="created_at" <?php echo $order === 'created_at' ? 'selected' : ''; ?>>Data de cadastro</option>
                <option value="username" <?php echo $order === 'username' ? 'selected' : ''; ?>>Usuário</option>
                <option value="email" <?php echo $order === 'email' ? 'selected' : ''; ?>>E-mail</option>
                <option value="access_level" <?php echo $order === 'access_level' ? 'selected' : ''; ?>>Perfil</option>
                <option value="is_active" <?php echo $order === 'is_active' ? 'selected' : ''; ?>>Status</option>
            </select>
        </div>

        <div class="filter-item">
            <label for="dir">Direção</label>
            <select id="dir" name="dir">
                <option value="desc" <?php echo $dir === 'DESC' ? 'selected' : ''; ?>>Descendente</option>
                <option value="asc" <?php echo $dir === 'ASC' ? 'selected' : ''; ?>>Ascendente</option>
            </select>
        </div>

        <div class="filter-item">
            <label for="per_page">Itens por página</label>
            <select id="per_page" name="per_page">
                <option value="10" <?php echo $per_page === 10 ? 'selected' : ''; ?>>10</option>
                <option value="20" <?php echo $per_page === 20 ? 'selected' : ''; ?>>20</option>
                <option value="50" <?php echo $per_page === 50 ? 'selected' : ''; ?>>50</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit">
                <i class="fas fa-search"></i> Aplicar
            </button>

            <a class="btn-link" href="admin.php">
                Limpar
            </a>
        </div>
    </form>

    <div class="summary">
        Exibindo <strong><?php echo $users->num_rows; ?></strong> de
        <strong><?php echo $total_users; ?></strong> usuários · Página
        <strong><?php echo $page; ?></strong> de
        <strong><?php echo $total_pages; ?></strong>
    </div>

    <table class="users-table">
        <thead>
            <tr>
                <th>
                    <a href="?<?php echo buildAdminQuery(['order' => 'username', 'dir' => sortDirection('username', $order, $dir), 'page' => 1]); ?>" class="sort-link">
                        Usuário <?php echo $order === 'username' ? ($dir === 'ASC' ? '↑' : '↓') : ''; ?>
                    </a>
                </th>

                <th class="col-nome">Nome</th>

                <th class="col-email">
                    <a href="?<?php echo buildAdminQuery(['order' => 'email', 'dir' => sortDirection('email', $order, $dir), 'page' => 1]); ?>" class="sort-link">
                        E-mail <?php echo $order === 'email' ? ($dir === 'ASC' ? '↑' : '↓') : ''; ?>
                    </a>
                </th>

                <th>
                    <a href="?<?php echo buildAdminQuery(['order' => 'access_level', 'dir' => sortDirection('access_level', $order, $dir), 'page' => 1]); ?>" class="sort-link">
                        Nível de Acesso <?php echo $order === 'access_level' ? ($dir === 'ASC' ? '↑' : '↓') : ''; ?>
                    </a>
                </th>

                <th>
                    <a href="?<?php echo buildAdminQuery(['order' => 'created_at', 'dir' => sortDirection('created_at', $order, $dir), 'page' => 1]); ?>" class="sort-link">
                        Data de Cadastro <?php echo $order === 'created_at' ? ($dir === 'ASC' ? '↑' : '↓') : ''; ?>
                    </a>
                </th>

                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>

        <tbody>
            <?php while ($user = $users->fetch_assoc()): ?>
                <?php
                    $full_name = trim(
                        (string)($user['first_name'] ?? '') . ' ' . (string)($user['last_name'] ?? '')
                    );

                    if ($full_name === '') {
                        $full_name = '—';
                    }

                    $role_class = strtolower((string)$user['access_level']);

                    if (!preg_match('/^[a-z0-9_-]+$/', $role_class)) {
                        $role_class = 'role-unknown';
                    }
                ?>

                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars((string)$user['username']); ?></strong>
                    </td>

                    <td>
                        <span class="full-name"><?php echo htmlspecialchars($full_name); ?></span>
                    </td>

                    <td>
                        <?php echo htmlspecialchars((string)$user['email']); ?>
                    </td>

                    <td>
                        <span class="status-badge <?php echo htmlspecialchars($role_class); ?>">
                            <?php echo htmlspecialchars((string)$user['access_level']); ?>
                        </span>
                    </td>

                    <td>
                        <?php echo date('d/m/Y', strtotime((string)$user['created_at'])); ?>
                    </td>

                    <td>
                        <div class="status-stack">
                            <span class="status-badge <?php echo $user['email_verified'] ? 'verified' : 'unverified'; ?>">
                                <?php echo $user['email_verified'] ? 'E-mail verificado' : 'E-mail pendente'; ?>
                            </span>

                            <span class="status-badge <?php echo $user['is_active'] ? 'verified' : 'unverified'; ?>">
                                <?php echo $user['is_active'] ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </div>
                    </td>

                    <td>
                        <div class="action-buttons">
                            <div class="dropdown">
                                <button class="dropdown-toggle" type="button">
                                    Alterar Perfil
                                </button>

                                <div class="dropdown-content">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="set_admin">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                        <button type="submit">Admin</button>
                                    </form>

                                    <form method="POST">
                                        <input type="hidden" name="action" value="set_editor">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                        <button type="submit">Editor</button>
                                    </form>

                                    <form method="POST">
                                        <input type="hidden" name="action" value="set_viewer">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                        <button type="submit">Viewer</button>
                                    </form>

                                    <form method="POST">
                                        <input type="hidden" name="action" value="set_gestor">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                        <button type="submit">Gestor</button>
                                    </form>

                                    <form method="POST">
                                        <input type="hidden" name="action" value="set_analista">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                        <button type="submit">Analista</button>
                                    </form>

                                    <form method="POST">
                                        <input type="hidden" name="action" value="set_coordenador">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                        <button type="submit">Coordenador</button>
                                    </form>

                                    <form method="POST">
                                        <input type="hidden" name="action" value="set_marketing">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                        <button type="submit">Marketing</button>
                                    </form>

                                    <form method="POST">
                                        <input type="hidden" name="action" value="set_rh">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                        <button type="submit">RH</button>
                                    </form>
                                </div>
                            </div>

                            <div class="dropdown">
                                <button class="dropdown-toggle" type="button">
                                    Alterar Status
                                </button>

                                <div class="dropdown-content">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="inativar_usuario">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                        <button type="submit">Inativar</button>
                                    </form>

                                    <form method="POST">
                                        <input type="hidden" name="action" value="ativar_usuario">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                        <button type="submit">Ativar</button>
                                    </form>
                                </div>
                            </div>

                            <form method="POST" class="reset-password-form">
                                <input type="hidden" name="action" value="resetar_senha">
                                <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">

                                <button
                                    type="submit"
                                    class="dropdown-toggle reset-password-btn"
                                    data-confirm-reset="true"
                                    data-user-name="<?php echo htmlspecialchars($full_name); ?>"
                                >
                                    Resetar Senha
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>

            <?php if ($total_users === 0): ?>
                <tr>
                    <td colspan="7" class="empty-table">
                        Nenhum usuário encontrado com os filtros atuais.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php
        $window = 2;
        $start_page = max(1, $page - $window);
        $end_page = min($total_pages, $page + $window);
    ?>

    <div class="pagination">
        <a
            class="page-link <?php echo $page <= 1 ? 'disabled' : ''; ?>"
            href="?<?php echo buildAdminQuery(['page' => max(1, $page - 1)]); ?>"
        >
            &laquo;
        </a>

        <?php if ($start_page > 1): ?>
            <a class="page-link" href="?<?php echo buildAdminQuery(['page' => 1]); ?>">1</a>

            <?php if ($start_page > 2): ?>
                <span class="page-link disabled">...</span>
            <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
            <a
                class="page-link <?php echo $i === $page ? 'active' : ''; ?>"
                href="?<?php echo buildAdminQuery(['page' => $i]); ?>"
            >
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($end_page < $total_pages): ?>
            <?php if ($end_page < $total_pages - 1): ?>
                <span class="page-link disabled">...</span>
            <?php endif; ?>

            <a class="page-link" href="?<?php echo buildAdminQuery(['page' => $total_pages]); ?>">
                <?php echo $total_pages; ?>
            </a>
        <?php endif; ?>

        <a
            class="page-link <?php echo $page >= $total_pages ? 'disabled' : ''; ?>"
            href="?<?php echo buildAdminQuery(['page' => min($total_pages, $page + 1)]); ?>"
        >
            &raquo;
        </a>
    </div>
</div>

<?php if (!empty($reset_password_modal)): ?>
    <div class="modal-overlay" id="resetPasswordModal">
        <div class="modal-box">
            <div class="modal-icon">
                <i class="fas fa-check-circle"></i>
            </div>

            <h2>Senha Alterada</h2>

            <p><?php echo htmlspecialchars($reset_password_modal); ?></p>

            <button type="button" class="modal-btn" data-close-reset-modal>
                OK
            </button>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_end.php'; ?>