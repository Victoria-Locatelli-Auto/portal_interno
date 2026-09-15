<header class="page-header">
    <h1 class="page-title">
        <?php if (!empty($menu['icon'])): ?>
            <i class="<?php echo htmlspecialchars($menu['icon']); ?>"></i>
        <?php else: ?>
            <i class="fas fa-folder-open"></i>
        <?php endif; ?>
        <?php echo htmlspecialchars($menu['title']); ?>
    </h1>

    <div class="page-subtitle">
        Documentos e conteúdos vinculados a este menu
    </div>
</header>

<section class="content-panel">
    <?php if (!empty($submenus)): ?>
        <div class="submenu-tabs">
            <?php foreach ($submenus as $index => $submenu): ?>
                <button
                    type="button"
                    class="tab-button <?php echo $index === 0 ? 'active' : ''; ?>"
                    data-tab="submenu-<?php echo (int)$submenu['id']; ?>"
                >
                    <span>
                        <i class="fas fa-folder"></i>
                        <?php echo htmlspecialchars($submenu['title']); ?>
                    </span>
                    <span class="tab-badge"><?php echo (int)$submenu['doc_count']; ?></span>
                </button>
            <?php endforeach; ?>
        </div>

        <?php foreach ($submenus as $index => $submenu): ?>
            <?php
                $submenu_id = (int)$submenu['id'];
                $documents = $documents_by_submenu[$submenu_id] ?? [];
            ?>

            <div id="submenu-<?php echo $submenu_id; ?>" class="tab-content <?php echo $index === 0 ? 'active' : ''; ?>">
                <?php if (empty($documents)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Nenhum documento nesta subcategoria</p>
                    </div>
                <?php else: ?>
                    <div class="documents-grid">
                        <?php foreach ($documents as $doc): ?>
                            <?php require __DIR__ . '/partials/menu-page-document-card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <?php if (empty($default_documents)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Nenhum documento nesta categoria</p>
            </div>
        <?php else: ?>
            <div class="documents-grid">
                <?php foreach ($default_documents as $doc): ?>
                    <?php require __DIR__ . '/partials/menu-page-document-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
