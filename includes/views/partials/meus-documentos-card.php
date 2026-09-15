<article class="document-card">
    <div class="document-icon">
        <i class="<?php echo htmlspecialchars(meusDocumentosFileIcon($doc['filename'] ?? '')); ?>"></i>
    </div>

    <div class="document-title" title="<?php echo htmlspecialchars($doc['original_name'] ?? ''); ?>">
        <?php echo htmlspecialchars(meusDocumentosTitle($doc['original_name'] ?? '')); ?>
    </div>

    <span class="document-status status-<?php echo htmlspecialchars($doc['status'] ?? ''); ?>">
        <?php echo htmlspecialchars(meusDocumentosStatusLabel($doc['status'] ?? '')); ?>
    </span>

    <div class="document-date">
        <i class="fas fa-calendar"></i>
        <?php echo htmlspecialchars(meusDocumentosDate($doc['uploaded_at'] ?? null)); ?>
    </div>

    <div class="document-actions">
        <a href="<?php echo htmlspecialchars($base_url); ?>/api/serve_document.php?id=<?php echo (int)$doc['id']; ?>" class="btn-view" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-eye"></i>
            Visualizar
        </a>
    </div>
</article>
