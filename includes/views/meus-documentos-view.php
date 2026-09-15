<section class="my-documents-page">
    <nav class="breadcrumb">
        <a href="<?php echo htmlspecialchars($base_url); ?>/index.php">
            <i class="fas fa-home"></i>
            Home
        </a>
        <span>/ Meus Documentos</span>
    </nav>

    <header class="documents-header">
        <h1>
            <i class="fas fa-file-alt"></i>
            Meus Documentos
        </h1>
        <p>Documentos que você enviou para o portal</p>

        <div class="documents-stats">
            <div class="stat-item">
                <i class="fas fa-files"></i>
                <div>
                    <div class="stat-value"><?php echo (int)$stats['total']; ?></div>
                    <div>Total de Documentos</div>
                </div>
            </div>

            <div class="stat-item">
                <i class="fas fa-check-circle"></i>
                <div>
                    <div class="stat-value"><?php echo (int)$stats['approved']; ?></div>
                    <div>Aprovados</div>
                </div>
            </div>

            <div class="stat-item">
                <i class="fas fa-times-circle"></i>
                <div>
                    <div class="stat-value"><?php echo (int)$stats['rejected']; ?></div>
                    <div>Rejeitados</div>
                </div>
            </div>
        </div>
    </header>

    <section class="search-box">
        <form method="POST" class="search-form">
            <input
                type="text"
                name="search_query"
                placeholder="Buscar nos meus documentos..."
                value="<?php echo htmlspecialchars($search_query); ?>"
            >
            <button type="submit" class="btn-search">
                <i class="fas fa-search"></i>
                Buscar
            </button>

            <?php if ($search_query !== ''): ?>
                <a href="<?php echo htmlspecialchars($base_url); ?>/pages/meus_documentos.php" class="btn-clear">
                    <i class="fas fa-times"></i>
                    Limpar
                </a>
            <?php endif; ?>
        </form>
    </section>

    <?php if ($search_query !== ''): ?>
        <div class="search-results-count">
            <i class="fas fa-info-circle"></i>
            Encontrados <strong><?php echo count($results); ?></strong> documento(s) contendo "<?php echo htmlspecialchars($search_query); ?>"
        </div>
    <?php endif; ?>

    <?php if (!empty($results)): ?>
        <section class="documents-grid">
            <?php foreach ($results as $doc): ?>
                <?php require __DIR__ . '/partials/meus-documentos-card.php'; ?>
            <?php endforeach; ?>
        </section>
    <?php else: ?>
        <section class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h2>Nenhum Documento Encontrado</h2>
            <p>
                <?php if ($search_query !== ''): ?>
                    Nenhum documento contém "<?php echo htmlspecialchars($search_query); ?>"
                <?php else: ?>
                    Você ainda não enviou nenhum documento
                <?php endif; ?>
            </p>
        </section>
    <?php endif; ?>
</section>
