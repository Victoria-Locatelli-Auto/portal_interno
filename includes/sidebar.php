<aside class="sidebar open" id="sidebar">
    <div class="sidebar-top">
        <button type="button" class="sidebar-toggle" onclick="toggleMenu()">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <nav class="sidebar-menu">
        <a href="<?php echo $base_url; ?>/index.php" class="sidebar-item">
            <i class="fas fa-house"></i>
            <span>Inicio</span>
        </a>

        <?php if (!empty($custom_menus)): ?>
            <div class="sidebar-divider"></div>

            <?php foreach ($custom_menus as $menu): ?>
                <?php
                    $href = $menu['menu_type'] === 'page'
                        ? $base_url . '/pages/menu_page.php?menu_id=' . $menu['id']
                        : htmlspecialchars($menu['url']);

                    $target = $menu['menu_type'] === 'page'
                        ? ''
                        : 'target="_blank" rel="noopener noreferrer"';

                    $icon = !empty($menu['icon']) ? $menu['icon'] : 'fas fa-folder';
                ?>
                <a href="<?php echo $href; ?>" class="sidebar-item" <?php echo $target; ?>>
                    <i class="<?php echo htmlspecialchars($icon); ?>"></i>
                    <span><?php echo htmlspecialchars($menu['title']); ?></span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>

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

            <a href="<?php echo $base_url; ?>/pages/import.php" class="sidebar-subitem">
                <i class="fas fa-file-import"></i>
                <span>Importar PDF</span>
            </a>
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
