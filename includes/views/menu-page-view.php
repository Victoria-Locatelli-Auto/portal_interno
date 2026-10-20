<div class="menu-page">
    <header class="menu-page-header">
        <a class="menu-page-back" href="<?php echo htmlspecialchars($base_url); ?>/index.php">
            <i class="fas fa-arrow-left" aria-hidden="true"></i>
            Voltar ao início
        </a>

        <div class="menu-page-heading">
            <span class="menu-page-icon" aria-hidden="true">
                <?php if (!empty($menu['icon'])): ?>
                    <i class="<?php echo htmlspecialchars($menu['icon']); ?>"></i>
                <?php else: ?>
                    <i class="fas fa-folder-open"></i>
                <?php endif; ?>
            </span>

            <div>
                <span class="menu-page-kicker">Biblioteca do Portal MCM</span>
                <h1><?php echo htmlspecialchars($menu['title']); ?></h1>
            </div>
        </div>
    </header>

    <section class="content-panel" aria-label="Documentos de <?php echo htmlspecialchars($menu['title']); ?>">
        <?php if (!empty($submenus)): ?>
            <?php if (empty($active_documents)): ?>
                <div class="empty-state">
                    <span class="empty-state-icon"><i class="far fa-folder-open" aria-hidden="true"></i></span>
                    <h3>Nenhum documento encontrado</h3>
                    <p>Esta categoria ainda não possui documentos disponíveis.</p>
                </div>
            <?php else: ?>
                <div class="documents-grid">
                    <?php foreach ($active_documents as $doc): ?>
                        <?php require __DIR__ . '/partials/menu-page-document-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="submenu-heading">
                <div>
                    <h2>Documentos</h2>
                </div>
            </div>

            <?php if (empty($default_documents)): ?>
                <div class="empty-state">
                    <span class="empty-state-icon"><i class="far fa-folder-open" aria-hidden="true"></i></span>
                    <h3>Nenhum documento encontrado</h3>
                    <p>Esta categoria ainda não possui documentos disponíveis.</p>
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
</div>
