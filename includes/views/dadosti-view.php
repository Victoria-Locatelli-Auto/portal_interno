<section class="dados-ti-page">
    <div class="dados-ti-panel">
        <header class="dados-ti-header">
            <h1>
                <i class="<?php echo htmlspecialchars($menu['icon'] ?: 'fas fa-folder-open'); ?>"></i>
                <?php echo htmlspecialchars($menu['title']); ?>
            </h1>
        </header>

        <?php if (!empty($submenus)): ?>
            <div class="tabs-container">
                <div class="tabs-header" role="tablist" aria-label="Categorias de documentos">
                    <?php foreach ($submenus as $index => $submenu): ?>
                        <button
                            type="button"
                            class="tab-button <?php echo $index === 0 ? 'active' : ''; ?>"
                            data-tab-target="submenu-<?php echo (int)$submenu['id']; ?>"
                            role="tab"
                            aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                        >
                            <i class="fas fa-folder"></i>
                            <?php echo htmlspecialchars($submenu['title']); ?>

                            <?php if ((int)$submenu['doc_count'] > 0): ?>
                                <span class="tab-count"><?php echo (int)$submenu['doc_count']; ?></span>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($submenus as $index => $submenu): ?>
                    <?php $docs = $documents_by_submenu[(int)$submenu['id']] ?? []; ?>
                    <div
                        id="submenu-<?php echo (int)$submenu['id']; ?>"
                        class="tab-content <?php echo $index === 0 ? 'active' : ''; ?>"
                        role="tabpanel"
                    >
                        <?php require __DIR__ . '/partials/dadosti-documents-grid.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <?php $docs = $default_documents; ?>
            <?php require __DIR__ . '/partials/dadosti-documents-grid.php'; ?>
        <?php endif; ?>
    </div>
</section>
