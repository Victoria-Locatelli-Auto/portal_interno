<main class="menu-manager-page">
    <header class="manager-header">
        <div class="manager-header-icon" aria-hidden="true"><i class="fas fa-layer-group"></i></div>
        <div>
            <span class="manager-kicker">Configurações do portal</span>
            <h1>Gerenciar menus</h1>
        </div>
    </header>

    <?php if ($success_msg): ?>
        <div class="manager-alert manager-alert-success" role="status">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <span><?php echo htmlspecialchars($success_msg, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="manager-alert manager-alert-error" role="alert">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
            <span><?php echo htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    <?php endif; ?>

    <section class="manager-stats" aria-label="Resumo dos menus">
        <div class="stat-item"><strong><?php echo (int)$menu_stats['total']; ?></strong><span>Categorias</span></div>
        <div class="stat-item"><strong><?php echo (int)$menu_stats['active']; ?></strong><span>Ativas</span></div>
        <div class="stat-item"><strong><?php echo (int)$menu_stats['submenus']; ?></strong><span>Subcategorias</span></div>
        <div class="stat-item"><strong><?php echo (int)$menu_stats['links']; ?></strong><span>Links externos</span></div>
    </section>

    <section class="manager-panel">
        <div class="panel-heading">
            <div>
                <h2>Nova categoria</h2>
                <p>Crie uma seção interna ou um atalho para outro sistema.</p>
            </div>
        </div>

        <form method="POST" id="addMenuForm" class="manager-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($manage_menus_csrf, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="action" value="add_menu">

            <div class="form-grid">
                <div class="form-field">
                    <label for="title">Nome da categoria <span aria-hidden="true">*</span></label>
                    <input type="text" id="title" name="title" maxlength="120" placeholder="Ex: Recursos Humanos" required>
                </div>
                <div class="form-field">
                    <label for="menu_type">Tipo <span aria-hidden="true">*</span></label>
                    <select id="menu_type" name="menu_type" required data-url-toggle="url_field">
                        <option value="page">Página com subcategorias</option>
                        <option value="link">Link externo</option>
                    </select>
                </div>
                <div id="url_field" class="form-field form-field-wide is-hidden">
                    <label for="url">Endereço do link <span aria-hidden="true">*</span></label>
                    <input type="url" id="url" name="url" maxlength="500" placeholder="https://exemplo.com.br">
                </div>
                <div class="form-field">
                    <label for="icon">Ícone <span aria-hidden="true">*</span></label>
                    <select id="icon" name="icon" required data-icon-preview="icon_preview">
                        <option value="">Selecione um ícone</option>
                        <?php foreach ($icons as $icon_class => $icon_label): ?>
                            <option value="<?php echo htmlspecialchars($icon_class, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($icon_label, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field">
                    <span class="preview-label">Prévia</span>
                    <div class="icon-preview-box"><i id="icon_preview" class="fas fa-folder" aria-hidden="true"></i></div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="manager-button manager-button-primary">
                    <i class="fas fa-plus" aria-hidden="true"></i> Criar categoria
                </button>
            </div>
        </form>
    </section>

    <section class="manager-panel">
        <div class="panel-heading">
            <div><h2>Categorias cadastradas</h2></div>
            <span class="panel-count"><?php echo (int)$menu_stats['total']; ?> cadastradas</span>
        </div>

        <?php if (empty($menus_list)): ?>
            <div class="empty-state"><i class="fas fa-folder-open" aria-hidden="true"></i><strong>Nenhuma categoria cadastrada</strong></div>
        <?php else: ?>
            <div class="menus-table-wrap">
                <table class="menus-table">
                    <thead><tr><th>Categoria</th><th>Tipo</th><th>Destino</th><th>Status</th><th class="actions-column">Ações</th></tr></thead>
                    <tbody>
                    <?php foreach ($menus_list as $menu): ?>
                        <tr class="menu-row">
                            <td>
                                <div class="menu-identity">
                                    <span class="menu-icon"><i class="<?php echo htmlspecialchars($menu['icon'] ?: 'fas fa-folder', ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i></span>
                                    <div><strong><?php echo htmlspecialchars($menu['title'], ENT_QUOTES, 'UTF-8'); ?></strong><small><?php echo count($menu['submenus']); ?> subcategorias</small></div>
                                </div>
                            </td>
                            <td><span class="type-badge"><?php echo $menu['menu_type'] === 'page' ? 'Página interna' : 'Link externo'; ?></span></td>
                            <td><span class="destination" title="<?php echo htmlspecialchars($menu['url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(manageMenusShortUrl($menu['url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <td><span class="status-badge <?php echo $menu['is_active'] ? 'is-active' : 'is-inactive'; ?>"><i class="fas fa-circle" aria-hidden="true"></i><?php echo $menu['is_active'] ? 'Ativa' : 'Inativa'; ?></span></td>
                            <td>
                                <div class="row-actions">
                                    <?php if ($menu['menu_type'] === 'page'): ?>
                                        <button type="button" class="icon-button" data-add-submenu data-menu-id="<?php echo (int)$menu['id']; ?>" data-menu-name="<?php echo htmlspecialchars($menu['title'], ENT_QUOTES, 'UTF-8'); ?>" title="Adicionar subcategoria" aria-label="Adicionar subcategoria"><i class="fas fa-folder-plus"></i></button>
                                    <?php endif; ?>
                                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($manage_menus_csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="action" value="toggle_menu"><input type="hidden" name="menu_id" value="<?php echo (int)$menu['id']; ?>">
                                        <button type="submit" class="icon-button" title="<?php echo $menu['is_active'] ? 'Desativar' : 'Ativar'; ?>" aria-label="<?php echo $menu['is_active'] ? 'Desativar' : 'Ativar'; ?>"><i class="fas fa-toggle-<?php echo $menu['is_active'] ? 'on' : 'off'; ?>"></i></button>
                                    </form>
                                    <button type="button" class="icon-button" data-edit-menu="<?php echo htmlspecialchars(json_encode($menu, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>" title="Editar categoria" aria-label="Editar categoria"><i class="fas fa-pen"></i></button>
                                    <form method="POST" data-confirm="Excluir esta categoria? Essa ação não poderá ser desfeita.">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($manage_menus_csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="action" value="delete_menu"><input type="hidden" name="menu_id" value="<?php echo (int)$menu['id']; ?>">
                                        <button type="submit" class="icon-button icon-button-danger" title="Excluir categoria" aria-label="Excluir categoria"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php foreach ($menu['submenus'] as $submenu): ?>
                            <tr class="submenu-row">
                                <td><div class="submenu-identity"><i class="fas fa-turn-up" aria-hidden="true"></i><i class="far fa-folder" aria-hidden="true"></i><span><?php echo htmlspecialchars($submenu['title'], ENT_QUOTES, 'UTF-8'); ?></span></div></td>
                                <td><span class="type-badge">Subcategoria</span></td><td><span class="destination">Vinculada a <?php echo htmlspecialchars($menu['title'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><span class="status-badge <?php echo $submenu['is_active'] ? 'is-active' : 'is-inactive'; ?>"><i class="fas fa-circle"></i><?php echo $submenu['is_active'] ? 'Ativa' : 'Inativa'; ?></span></td>
                                <td><div class="row-actions"><form method="POST" data-confirm="Excluir esta subcategoria? Essa ação não poderá ser desfeita."><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($manage_menus_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="delete_submenu"><input type="hidden" name="submenu_id" value="<?php echo (int)$submenu['id']; ?>"><button type="submit" class="icon-button icon-button-danger" title="Excluir subcategoria" aria-label="Excluir subcategoria"><i class="fas fa-trash"></i></button></form></div></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/partials/manage-menus-modals.php'; ?>
