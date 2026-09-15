<?php $is_text_doc = menuPageIsTextDocument($doc); ?>

<?php if ($is_text_doc): ?>
    <article class="document-card text-document-card">
        <div class="text-document-header">
            <div class="document-icon">
                <i class="fas fa-file-alt text-doc-icon"></i>
            </div>

            <div class="text-doc-main">
                <div class="document-name text-doc-title">
                    <?php echo htmlspecialchars(menuPageFormatDocumentTitle($doc['original_name'] ?? '')); ?>
                </div>
                <div class="document-meta">
                    <i class="far fa-calendar"></i>
                    <?php echo htmlspecialchars(menuPageDocumentDate($doc['uploaded_at'] ?? null)); ?>
                </div>
            </div>
        </div>

        <div class="text-document-content"><?php echo htmlspecialchars($doc['extracted_text'] ?? ''); ?></div>
    </article>
<?php else: ?>
    <article class="document-card">
        <div class="document-icon">
            <i class="<?php echo htmlspecialchars(menuPageFileIcon($doc['original_name'] ?? '')); ?>"></i>
        </div>

        <div class="document-name">
            <?php echo htmlspecialchars(menuPageFormatDocumentTitle($doc['original_name'] ?? '')); ?>
        </div>

        <div class="document-meta">
            <i class="far fa-calendar"></i>
            <?php echo htmlspecialchars(menuPageDocumentDate($doc['uploaded_at'] ?? null)); ?>
        </div>

        <div>
            <span class="document-status status-<?php echo htmlspecialchars($doc['status'] ?? ''); ?>">
                <?php echo htmlspecialchars(menuPageStatusLabel($doc['status'] ?? '')); ?>
            </span>
        </div>

        <a href="<?php echo $base_url; ?>/pages/view_document.php?id=<?php echo (int)$doc['id']; ?>" class="btn-view">
            <i class="fas fa-eye"></i>
            Visualizar
        </a>
    </article>
<?php endif; ?>
