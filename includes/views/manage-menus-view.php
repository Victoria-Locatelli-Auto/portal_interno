<section class="menu-manager-page">
    <div class="menu-manager-panel">
        <h1 class="manager-title">
            <i class="fas fa-bars"></i>
            Gerenciar Categorias e Subcategorias
        </h1>

        <?php if ($success_msg): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_msg); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <section class="menu-section">
            <h2 class="section-title">
                <i class="fas fa-plus"></i>
                Adicionar Nova Categoria
            </h2>

            <form method="POST" id="addMenuForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="title"><i class="fas fa-heading"></i> Nome da Categoria *</label>
                        <input type="text" id="title" name="title" placeholder="Ex: Contratos, Recursos Humanos" required>
                    </div>

                    <div class="form-group">
                        <label for="menu_type"><i class="fas fa-folder"></i> Tipo *</label>
                        <select id="menu_type" name="menu_type" required data-url-toggle="url_field">
                            <option value="page">Com Subcategorias</option>
                            <option value="link">Link Externo</option>
                        </select>
                    </div>
                </div>

                <div id="url_field" class="form-row is-hidden">
                    <div class="form-group">
                        <label for="url"><i class="fas fa-link"></i> URL / Link Externo *</label>
                        <input type="text" id="url" name="url" placeholder="Ex: https://exemplo.com.br">
                        <small class="form-hint">Apenas para links externos</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="icon"><i class="fas fa-icons"></i> Ícone *</label>
                        <select id="icon" name="icon" required data-icon-preview="icon_preview">
                            <option value="">-- Selecione um ícone --</option>
                            <?php foreach ($icons as $icon_class => $icon_label): ?>
                                <option value="<?php echo htmlspecialchars($icon_class); ?>">
                                    <?php echo htmlspecialchars($icon_label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-eye"></i> Prévia do Ícone</label>
                        <div class="icon-preview-box">
                            <i id="icon_preview" class="fas fa-folder"></i>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="action" value="add_menu">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus"></i>
                    Criar Categoria
                </button>
            </form>
        </section>

        <section class="menu-section">
            <h2 class="section-title">
                <i class="fas fa-list"></i>
                Categorias Existentes
            </h2>

            <?php if (empty($menus_list)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    Nenhuma categoria criada ainda.
                </div>
            <?php else: ?>
                <div class="menus-table-wrap">
                    <table class="menus-table">
                        <thead>
                            <tr>
                                <th>Ícone</th>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Link</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($menus_list as $menu): ?>
                                <tr class="menu-row">
                                    <td>
                                        <?php if (!empty($menu['icon'])): ?>
                                            <i class="<?php echo htmlspecialchars($menu['icon']); ?> icon-preview"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($menu['title']); ?></strong></td>
                                    <td>
                                        <span class="status-badge <?php echo $menu['menu_type'] === 'page' ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $menu['menu_type'] === 'page' ? 'Página' : 'Link'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="muted-text"><?php echo htmlspecialchars(manageMenusShortUrl($menu['url'] ?? '')); ?></small>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $menu['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $menu['is_active'] ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <form method="POST">
                                                <input type="hidden" name="action" value="toggle_menu">
                                                <input type="hidden" name="menu_id" value="<?php echo (int)$menu['id']; ?>">
                                                <button type="submit" class="btn btn-secondary" title="Ativar/Desativar">
                                                    <i class="fas fa-toggle-<?php echo $menu['is_active'] ? 'on' : 'off'; ?>"></i>
                                                </button>
                                            </form>

                                            <button
                                                type="button"
                                                class="btn btn-primary"
                                                title="Editar"
                                                data-edit-menu="<?php echo htmlspecialchars(json_encode($menu, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)); ?>"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <form method="POST" data-confirm="Deletar categoria?">
                                                <input type="hidden" name="action" value="delete_menu">
                                                <input type="hidden" name="menu_id" value="<?php echo (int)$menu['id']; ?>">
                                                <button type="submit" class="btn btn-danger" title="Deletar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <?php if ($menu['menu_type'] === 'page'): ?>
                                    <?php foreach ($menu['submenus'] as $submenu): ?>
                                        <tr class="submenu-row">
                                            <td></td>
                                            <td>
                                                <i class="fas fa-folder-open"></i>
                                                <?php echo htmlspecialchars($submenu['title']); ?>
                                            </td>
                                            <td colspan="2">Subcategoria</td>
                                            <td>
                                                <span class="status-badge <?php echo $submenu['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                    <?php echo $submenu['is_active'] ? 'Ativo' : 'Inativo'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form method="POST" data-confirm="Deletar subcategoria?">
                                                    <input type="hidden" name="action" value="delete_submenu">
                                                    <input type="hidden" name="submenu_id" value="<?php echo (int)$submenu['id']; ?>">
                                                    <button type="submit" class="btn btn-danger" title="Deletar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <tr class="submenu-add-row">
                                        <td colspan="6">
                                            <button
                                                type="button"
                                                class="btn btn-primary"
                                                data-add-submenu
                                                data-menu-id="<?php echo (int)$menu['id']; ?>"
                                                data-menu-name="<?php echo htmlspecialchars($menu['title']); ?>"
                                            >
                                                <i class="fas fa-plus"></i>
                                                Adicionar Subcategoria
                                            </button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</section>

<?php require __DIR__ . '/partials/manage-menus-modals.php'; ?>
