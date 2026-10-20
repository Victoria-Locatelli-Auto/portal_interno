<?php
$sidebar_access_level = (string)Session::getAccessLevel();
$sidebar_submenus_by_menu = [];
$sidebar_current_menu_id = (int)($_GET['menu_id'] ?? 0);
$sidebar_current_submenu_id = (int)($_GET['submenu_id'] ?? 0);

if (isset($conn) && $conn instanceof mysqli && !empty($custom_menus)) {
    $sidebar_submenus_result = $conn->query("
        SELECT id, menu_id, title
        FROM custom_submenus
        WHERE is_active = TRUE
        ORDER BY menu_id ASC, order_position ASC
    ");

    if ($sidebar_submenus_result) {
        while ($sidebar_submenu = $sidebar_submenus_result->fetch_assoc()) {
            $sidebar_submenus_by_menu[(int)$sidebar_submenu['menu_id']][] = $sidebar_submenu;
        }
    }
}
?>
<aside class="sidebar open" id="sidebar">
    <div class="sidebar-brand-header">
        <a class="sidebar-brand" href="<?php echo $base_url; ?>/index.php" aria-label="Portal MCM - Início">
            <img src="<?php echo $base_url; ?>/index_files/mcm-symbol-white.png" width="36" height="36" alt="" aria-hidden="true">
            <span class="sidebar-brand-copy">
                <strong>PORTAL MCM</strong>
                <small>MCM - Caminhões e Ônibus</small>
            </span>
        </a>

        <button type="button" class="sidebar-toggle" onclick="toggleMenu()" title="Abrir ou recolher menu" aria-label="Abrir ou recolher menu">
            <i class="fas fa-bars" aria-hidden="true"></i>
        </button>
    </div>

    <nav class="sidebar-menu">
        <a href="<?php echo $base_url; ?>/index.php" class="sidebar-item">
            <i class="fas fa-house"></i>
            <span>Inicio</span>
        </a>

        <?php if (!empty($custom_menus)): ?>
            <div class="sidebar-divider"></div>

            <?php foreach ($custom_menus as $sidebar_menu): ?>
                <?php
                    $sidebar_menu_id = (int)$sidebar_menu['id'];
                    $sidebar_menu_submenus = $sidebar_submenus_by_menu[$sidebar_menu_id] ?? [];
                    $has_sidebar_submenus = $sidebar_menu['menu_type'] === 'page' && !empty($sidebar_menu_submenus);
                    $sidebar_menu_is_open = $has_sidebar_submenus && $sidebar_current_menu_id === $sidebar_menu_id;
                    $sidebar_submenu_dom_id = 'menu-submenu-' . $sidebar_menu_id;
                    $href = $sidebar_menu['menu_type'] === 'page'
                        ? $base_url . '/pages/menu_page.php?menu_id=' . $sidebar_menu_id
                        : $sidebar_menu['url'];

                    $target = $sidebar_menu['menu_type'] === 'page'
                        ? ''
                        : 'target="_blank" rel="noopener noreferrer"';

                    $icon = !empty($sidebar_menu['icon']) ? $sidebar_menu['icon'] : 'fas fa-folder';
                ?>
                <?php if ($has_sidebar_submenus): ?>
                    <button
                        type="button"
                        class="sidebar-dropdown-toggle sidebar-menu-parent <?php echo $sidebar_menu_is_open ? 'active' : ''; ?>"
                        onclick="toggleSubmenu('<?php echo $sidebar_submenu_dom_id; ?>', this)"
                        aria-controls="<?php echo $sidebar_submenu_dom_id; ?>"
                        aria-expanded="<?php echo $sidebar_menu_is_open ? 'true' : 'false'; ?>"
                    >
                        <i class="<?php echo htmlspecialchars($icon); ?>"></i>
                        <span><?php echo htmlspecialchars($sidebar_menu['title']); ?></span>
                        <i class="fas fa-chevron-down sidebar-arrow"></i>
                    </button>

                    <div class="sidebar-submenu sidebar-menu-children <?php echo $sidebar_menu_is_open ? 'open' : ''; ?>" id="<?php echo $sidebar_submenu_dom_id; ?>">
                        <?php foreach ($sidebar_menu_submenus as $sidebar_submenu): ?>
                            <?php
                                $sidebar_submenu_id = (int)$sidebar_submenu['id'];
                                $sidebar_submenu_active = $sidebar_current_menu_id === $sidebar_menu_id
                                    && $sidebar_current_submenu_id === $sidebar_submenu_id;
                                $sidebar_submenu_href = $base_url . '/pages/menu_page.php?menu_id=' . $sidebar_menu_id
                                    . '&submenu_id=' . $sidebar_submenu_id;
                            ?>
                            <a
                                href="<?php echo htmlspecialchars($sidebar_submenu_href); ?>"
                                class="sidebar-subitem <?php echo $sidebar_submenu_active ? 'is-active' : ''; ?>"
                                data-sidebar-submenu="<?php echo $sidebar_submenu_id; ?>"
                            >
                                <i class="far fa-folder"></i>
                                <span><?php echo htmlspecialchars($sidebar_submenu['title']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars($href); ?>" class="sidebar-item" <?php echo $target; ?>>
                        <i class="<?php echo htmlspecialchars($icon); ?>"></i>
                        <span><?php echo htmlspecialchars($sidebar_menu['title']); ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="<?php echo $base_url; ?>/pages/sistemas.php" class="sidebar-item">
            <i class="fas fa-table-cells-large"></i>
            <span>Sistemas da empresa</span>
        </a>

        <a href="<?php echo $base_url; ?>/pages/contatos.php" class="sidebar-item">
            <i class="fas fa-address-book"></i>
            <span>Contatos</span>
        </a>

        <a href="<?php echo $base_url; ?>/pages/institucional.php" class="sidebar-item">
            <i class="fas fa-building"></i>
            <span>Institucional</span>
        </a>

        <div class="sidebar-divider"></div>

        <button type="button" class="sidebar-dropdown-toggle" onclick="toggleSubmenu('config-submenu', this)">
            <i class="fas fa-gear"></i>
            <span>Configurações</span>
            <i class="fas fa-chevron-down sidebar-arrow"></i>
        </button>

        <div class="sidebar-submenu" id="config-submenu">
            <a href="<?php echo $base_url; ?>/admin.php" class="sidebar-subitem">
                <i class="fas fa-user-shield"></i>
                <span>Gerenciar Usuários</span>
            </a>

            <a href="<?php echo $base_url; ?>/pages/manage_menus.php" class="sidebar-subitem">
                <i class="fas fa-list"></i>
                <span>Menus</span>
            </a>

            <a href="<?php echo $base_url; ?>/pages/manage_notices.php" class="sidebar-subitem">
                <i class="fas fa-bell"></i>
                <span>Avisos</span>
            </a>

            <?php if ($sidebar_access_level === 'admin'): ?>
                <a href="<?php echo $base_url; ?>/pages/manage_banners.php" class="sidebar-subitem">
                    <i class="fas fa-images"></i>
                    <span>Banners da home</span>
                </a>

                <a href="<?php echo $base_url; ?>/pages/manage_systems.php" class="sidebar-subitem">
                    <i class="fas fa-laptop-code"></i>
                    <span>Gerenciar sistemas</span>
                </a>
            <?php endif; ?>

            <a href="<?php echo $base_url; ?>/pages/import.php" class="sidebar-subitem">
                <i class="fas fa-file-import"></i>
                <span>Importar PDF</span>
            </a>

            <?php if (in_array($sidebar_access_level, ['admin', 'editor'], true)): ?>
                <a href="<?php echo $base_url; ?>/pages/approve_documents.php" class="sidebar-subitem">
                    <i class="fas fa-file-circle-check"></i>
                    <span>Aprovar documentos</span>
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="sidebar-footer">
        <a href="<?php echo $base_url; ?>/pages/perfil.php" class="sidebar-item">
            <i class="fas fa-user-circle"></i>
            <span><?php echo htmlspecialchars($first_name); ?></span>
        </a>

        <a href="<?php echo $base_url; ?>/logout.php" class="sidebar-item sidebar-logout">
            <i class="fas fa-right-from-bracket"></i>
            <span>Sair</span>
        </a>
    </div>
</aside>
