<article class="result-item">
    <div class="result-icon">
        <i class="<?php echo htmlspecialchars(searchPageDocumentIcon($doc)); ?>"></i>
    </div>

    <div class="result-content">
        <h4><?php echo htmlspecialchars($doc['original_name'] ?? 'Documento'); ?></h4>
        <p class="result-excerpt">
            <?php echo htmlspecialchars(searchPageExcerpt($doc, $search_query)); ?>
        </p>
        <small class="result-date">
            <i class="far fa-calendar"></i>
            <?php echo htmlspecialchars(searchPageDate($doc['uploaded_at'] ?? null)); ?>
        </small>
    </div>

    <a href="<?php echo htmlspecialchars($base_url); ?>/pages/view_document.php?id=<?php echo (int)$doc['id']; ?>" class="btn btn-sm btn-primary">
        <i class="fas fa-eye"></i>
        Ver
    </a>
</article>
