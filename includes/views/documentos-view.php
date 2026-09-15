<section
    class="documents-page"
    data-submenus-url="<?php echo htmlspecialchars($base_url); ?>/api/get_submenus.php"
>
    <div class="documents-panel">
        <header class="documents-header">
            <h1><i class="fas fa-file-alt"></i> Documentos</h1>

            <?php if ($can_import_documents): ?>
                <a href="<?php echo htmlspecialchars($base_url); ?>/pages/import.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Importar PDF
                </a>
            <?php endif; ?>
        </header>

        <section class="panel-box">
            <h2><i class="fas fa-search"></i> Pesquisar Documentos</h2>

            <form method="GET" class="search-form">
                <div class="field">
                    <label for="search">Buscar por título</label>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Digite o nome do documento..."
                    >
                </div>

                <?php if ($menu_id > 0): ?>
                    <input type="hidden" name="menu_id" value="<?php echo (int)$menu_id; ?>">
                <?php endif; ?>

                <?php if ($submenu_id > 0): ?>
                    <input type="hidden" name="submenu_id" value="<?php echo (int)$submenu_id; ?>">
                <?php endif; ?>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Buscar
                    </button>

                    <?php if ($search !== ''): ?>
                        <a href="<?php echo htmlspecialchars(documentosFilterLink(['menu_id' => $menu_id, 'submenu_id' => $submenu_id])); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Limpar
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <?php if (!empty($menus)): ?>
            <section class="panel-box">
                <h2><i class="fas fa-filter"></i> Filtrar por Categoria</h2>

                <form method="GET" class="filter-form">
                    <div class="field">
                        <label for="filter_menu">Menu / Categoria</label>
                        <select id="filter_menu" name="menu_id">
                            <option value="">-- Todos os Menus --</option>
                            <?php foreach ($menus as $menu): ?>
                                <option value="<?php echo (int)$menu['id']; ?>" <?php echo $menu_id === (int)$menu['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($menu['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field <?php echo empty($submenus) ? 'is-hidden' : ''; ?>" id="submenuField">
                        <label for="filter_submenu">Subcategoria</label>
                        <select id="filter_submenu" name="submenu_id" data-selected="<?php echo (int)$submenu_id; ?>">
                            <option value="">-- Todas as Subcategorias --</option>
                            <?php foreach ($submenus as $submenu): ?>
                                <option value="<?php echo (int)$submenu['id']; ?>" <?php echo $submenu_id === (int)$submenu['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($submenu['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Filtrar
                        </button>
                        <a href="<?php echo htmlspecialchars($base_url); ?>/pages/documentos.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Limpar
                        </a>
                    </div>
                </form>
            </section>
        <?php endif; ?>

        <?php if ($search !== '' || $menu_id > 0 || $submenu_id > 0): ?>
            <div class="filter-info">
                <i class="fas fa-info-circle"></i>
                <strong>Mostrando <?php echo (int)$total_documents; ?> documento(s)</strong>

                <?php if ($search !== ''): ?>
                    para a pesquisa: <strong>"<?php echo htmlspecialchars($search); ?>"</strong>
                <?php endif; ?>

                <?php if ($selected_menu_name !== ''): ?>
                    na categoria: <strong><?php echo htmlspecialchars($selected_menu_name); ?></strong>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($documents)): ?>
            <div class="documents-table-wrap">
                <table class="documents-table">
                    <thead>
                        <tr>
                            <th class="icon-heading"></th>
                            <th>Nome do Documento</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $document): ?>
                            <tr>
                                <td class="icon-cell">
                                    <i class="fas fa-file-pdf"></i>
                                </td>
                                <td class="name-cell">
                                    <?php echo htmlspecialchars(documentosFormatTitle($document['original_name'] ?? 'Documento')); ?>
                                </td>
                                <td class="date-cell">
                                    <?php echo htmlspecialchars(documentosDateLabel($document['uploaded_at'] ?? null)); ?>
                                </td>
                                <td class="status-cell">
                                    <span class="badge status-<?php echo htmlspecialchars($document['status']); ?>">
                                        <?php echo htmlspecialchars(documentosStatusLabel($document['status'])); ?>
                                    </span>
                                </td>
                                <td class="action-cell">
                                    <a href="<?php echo htmlspecialchars($base_url); ?>/pages/view_document.php?id=<?php echo (int)$document['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <nav class="pagination" aria-label="Paginação de documentos">
                    <?php if ($current_page > 1): ?>
                        <a href="<?php echo htmlspecialchars(documentosPageLink(1, $menu_id, $submenu_id, $search)); ?>" class="pagination-link" title="Primeira página">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                        <a href="<?php echo htmlspecialchars(documentosPageLink($current_page - 1, $menu_id, $submenu_id, $search)); ?>" class="pagination-link" title="Página anterior">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <a href="<?php echo htmlspecialchars(documentosPageLink($i, $menu_id, $submenu_id, $search)); ?>" class="pagination-link <?php echo $i === $current_page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="<?php echo htmlspecialchars(documentosPageLink($current_page + 1, $menu_id, $submenu_id, $search)); ?>" class="pagination-link" title="Próxima página">
                            <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="<?php echo htmlspecialchars(documentosPageLink($total_pages, $menu_id, $submenu_id, $search)); ?>" class="pagination-link" title="Última página">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h2>Nenhum documento importado</h2>
                <p>Comece importando seus primeiros documentos PDF.</p>

                <?php if ($can_import_documents): ?>
                    <a href="<?php echo htmlspecialchars($base_url); ?>/pages/import.php" class="btn btn-primary">
                        <i class="fas fa-cloud-upload-alt"></i>
                        Importar PDF
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
