<section class="notices-page">
    <div class="notices-panel">
        <header class="notices-header">
            <h1><i class="fas fa-bell"></i> Avisos e Notícias</h1>
            <p>Acompanhe os comunicados e atualizações do portal.</p>
        </header>

        <?php if (!empty($notices)): ?>
            <div class="notices-list">
                <?php foreach ($notices as $notice): ?>
                    <article class="notice-card">
                        <div class="notice-card-header">
                            <h2><?php echo htmlspecialchars($notice['title'] ?? 'Aviso'); ?></h2>
                            <time datetime="<?php echo htmlspecialchars(noticeDateTimeAttribute($notice['created_at'] ?? null)); ?>">
                                <?php echo htmlspecialchars(noticeDateLabel($notice['created_at'] ?? null)); ?>
                            </time>
                        </div>

                        <?php if (!empty($notice['description'])): ?>
                            <p class="notice-description">
                                <?php echo htmlspecialchars($notice['description']); ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($notice['content'])): ?>
                            <details class="notice-details">
                                <summary>Leia mais</summary>
                                <div class="notice-content">
                                    <?php echo nl2br(htmlspecialchars($notice['content'])); ?>
                                </div>
                            </details>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-bell-slash"></i>
                <h2>Nenhum aviso publicado</h2>
                <p>Volte mais tarde para verificar novidades.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
