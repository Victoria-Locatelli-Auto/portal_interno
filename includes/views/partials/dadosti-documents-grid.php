<?php if (!empty($docs)): ?>
    <div class="documents-grid">
        <?php foreach ($docs as $doc): ?>
            <article class="document-card">
                <div class="document-card-icon">
                    <i class="fas fa-file-pdf"></i>
                </div>

                <h2 class="document-card-title">
                    <?php echo htmlspecialchars($doc['original_name'] ?? 'Documento'); ?>
                </h2>

                <div class="document-card-info">
                    Enviado em: <?php echo htmlspecialchars(dadosTiDateLabel($doc['uploaded_at'] ?? null)); ?>
                </div>

                <div class="document-card-actions">
                    <a href="<?php echo htmlspecialchars(dadosTiDocumentUrl($base_url, (int)$doc['id'])); ?>" class="btn btn-primary">
                        <i class="fas fa-eye"></i>
                        Visualizar
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="fas fa-inbox"></i>
        </div>
        <p>Nenhum documento disponível nesta categoria.</p>
    </div>
<?php endif; ?>
