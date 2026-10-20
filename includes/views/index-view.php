<section class="home-dashboard">
    <div class="home-hero">
        <div class="home-greeting">
            <h1>Bem-vindo <?php echo htmlspecialchars(indexFormatCollaboratorName($first_name)); ?> ao Portal MCM</h1>
            <p>Informa&ccedil;&otilde;es, solicita&ccedil;&otilde;es, documentos e ferramentas do dia a dia, em um s&oacute; lugar.</p>
        </div>
    </div>

    <div class="home-search">
        <p class="search-prompt">O que voc&ecirc; precisa hoje?</p>
        <form method="GET">
            <i class="fas fa-search"></i>
            <input
                type="text"
                name="q"
                placeholder="Busque por sistemas, documentos ou pessoas..."
                value="<?php echo htmlspecialchars($search_query); ?>"
            >
            <kbd>Ctrl K</kbd>
        </form>
    </div>

    <?php if (empty($search_query) && !empty($banners)): ?>
        <section class="home-banner-carousel" data-banner-carousel aria-label="Destaques do Portal MCM">
            <div class="home-banner-viewport">
                <?php foreach ($banners as $banner_index => $banner): ?>
                    <?php $banner_image = indexBannerImageUrl($base_url, $banner['image_path'] ?? null); ?>
                    <?php if ($banner_image): ?>
                        <article class="home-banner-slide <?php echo $banner_index === 0 ? 'is-active' : ''; ?>" data-banner-slide aria-hidden="<?php echo $banner_index === 0 ? 'false' : 'true'; ?>">
                            <?php if (!empty($banner['target_url'])): ?>
                                <a href="<?php echo htmlspecialchars($banner['target_url'], ENT_QUOTES, 'UTF-8'); ?>" class="home-banner-link" <?php echo indexBannerIsExternal($banner['target_url']) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                            <?php else: ?>
                                <div class="home-banner-link">
                            <?php endif; ?>
                                <img src="<?php echo htmlspecialchars($banner_image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($banner['alt_text'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $banner_index === 0 ? 'fetchpriority="high"' : 'loading="lazy"'; ?>>
                                <?php if (!empty($banner['title'])): ?><span class="home-banner-caption"><?php echo htmlspecialchars($banner['title'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                            <?php if (!empty($banner['target_url'])): ?>
                                </a>
                            <?php else: ?>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php if (count($banners) > 1): ?>
                <button type="button" class="home-banner-control is-previous" data-banner-previous aria-label="Banner anterior"><i class="fas fa-chevron-left"></i></button>
                <button type="button" class="home-banner-control is-next" data-banner-next aria-label="Próximo banner"><i class="fas fa-chevron-right"></i></button>
                <div class="home-banner-dots" role="tablist" aria-label="Selecionar banner"><?php foreach ($banners as $banner_index => $banner): ?><button type="button" class="home-banner-dot <?php echo $banner_index === 0 ? 'is-active' : ''; ?>" data-banner-dot="<?php echo (int)$banner_index; ?>" aria-label="Exibir banner <?php echo (int)$banner_index + 1; ?>" aria-selected="<?php echo $banner_index === 0 ? 'true' : 'false'; ?>"></button><?php endforeach; ?></div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php if (empty($search_query)): ?>
        <section class="quick-section">
            <div class="section-heading">
                <div>
                    <span class="section-kicker">Acessos frequentes</span>
                </div>
            </div>

            <div class="quick-grid">
                <?php $card_position = 0; ?>

                <?php foreach ($custom_menus as $menu): ?>
                    <?php $tone = indexQuickAccessTone($card_position++); ?>
                    <a
                        href="<?php echo htmlspecialchars(indexMenuHref($menu, $base_url)); ?>"
                        class="quick-card quick-card-<?php echo htmlspecialchars($tone); ?>"
                        <?php echo indexMenuTarget($menu); ?>
                    >
                        <div class="quick-icon">
                            <i class="<?php echo htmlspecialchars(indexMenuIcon($menu)); ?>"></i>
                        </div>
                        <div class="quick-copy">
                            <strong><?php echo htmlspecialchars($menu['title']); ?></strong>
                        </div>
                    </a>
                <?php endforeach; ?>

                <?php
                $fixed_cards = [
                    [
                        'href' => $base_url . '/pages/institucional.php',
                        'icon' => 'fas fa-building',
                        'title' => 'Institucional',
                    ],
                    [
                        'href' => $base_url . '/pages/contatos.php',
                        'icon' => 'fas fa-address-book',
                        'title' => 'Contatos',
                    ],
                    [
                        'href' => $base_url . '/pages/treinamentos.php',
                        'icon' => 'fas fa-graduation-cap',
                        'title' => 'Treinamentos',
                    ],
                ];
                ?>

                <?php foreach ($fixed_cards as $card): ?>
                    <?php $tone = indexQuickAccessTone($card_position++); ?>
                    <a href="<?php echo htmlspecialchars($card['href']); ?>" class="quick-card quick-card-<?php echo htmlspecialchars($tone); ?>">
                        <div class="quick-icon">
                            <i class="<?php echo htmlspecialchars($card['icon']); ?>"></i>
                        </div>
                        <div class="quick-copy">
                            <strong><?php echo htmlspecialchars($card['title']); ?></strong>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($search_query)): ?>
        <section class="search-results">
            <h2>
                <i class="fas fa-search"></i>
                Resultados para "<?php echo htmlspecialchars($search_query); ?>" (<?php echo (int) $total_results; ?>)
            </h2>

            <?php if (!empty($search_results)): ?>
                <div class="documents-list">
                    <?php foreach ($search_results as $result): ?>
                        <?php $doc = $result['document']; ?>
                        <div class="search-result-card">
                            <div class="result-card-header">
                                <div class="result-file-icon">
                                    <?php if (indexIsPdfDocument($doc)): ?>
                                        <i class="fas fa-file-pdf"></i>
                                    <?php else: ?>
                                        <i class="fas fa-file-alt"></i>
                                    <?php endif; ?>
                                </div>

                                <div class="result-title-section">
                                    <h3 class="result-title">
                                        <?php echo htmlspecialchars($doc['original_name']); ?>
                                    </h3>
                                    <div class="result-meta">
                                        <span>
                                            <i class="far fa-calendar"></i>
                                            <?php echo date('d/m/Y', strtotime($doc['uploaded_at'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($result['preview'])): ?>
                                <div class="result-preview">
                                    <i class="fas fa-quote-left"></i>
                                    <em><?php echo htmlspecialchars(substr($result['preview'], 0, 150)); ?></em>
                                </div>
                            <?php endif; ?>

                            <div class="result-card-footer">
                                <a href="<?php echo htmlspecialchars($base_url); ?>/pages/view_document.php?id=<?php echo (int) $doc['id']; ?>" class="btn-view">
                                    <i class="fas fa-eye"></i> Visualizar Documento
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_results > $items_per_page): ?>
                    <div class="pagination">
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        ?>

                        <?php if ($page > 1): ?>
                            <a href="<?php echo htmlspecialchars(indexPageLink($search_query, $page - 1)); ?>" class="pagination-btn">
                                <i class="fas fa-chevron-left"></i> Anterior
                            </a>
                        <?php endif; ?>

                        <?php if ($start_page > 1): ?>
                            <a href="<?php echo htmlspecialchars(indexPageLink($search_query, 1)); ?>" class="pagination-num">1</a>
                            <?php if ($start_page > 2): ?>
                                <span class="pagination-dots">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="pagination-num pagination-current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="<?php echo htmlspecialchars(indexPageLink($search_query, $i)); ?>" class="pagination-num"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <span class="pagination-dots">...</span>
                            <?php endif; ?>
                            <a href="<?php echo htmlspecialchars(indexPageLink($search_query, $total_pages)); ?>" class="pagination-num"><?php echo $total_pages; ?></a>
                        <?php endif; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="<?php echo htmlspecialchars(indexPageLink($search_query, $page + 1)); ?>" class="pagination-btn">
                                Pr&oacute;ximo <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>

                        <div class="pagination-info">
                            P&aacute;gina <?php echo (int) $page; ?> de <?php echo (int) $total_pages; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="no-results">Nenhum documento encontrado para sua busca.</p>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <section class="notices-panel">
        <div class="section-heading">
            <div>
                <h2>Avisos</h2>
                <p>Comunicados recentes do Portal MCM</p>
            </div>
        </div>

        <div class="notices-box">
            <?php if (!empty($notices)): ?>
                <?php foreach ($notices as $notice): ?>
                    <article class="notice-item">
                        <div class="notice-header">
                            <h3><?php echo htmlspecialchars($notice['title']); ?></h3>
                            <span class="notice-date">
                                <i class="fas fa-calendar-alt"></i>
                                <?php echo date('d/m/Y H:i', strtotime($notice['created_at'])); ?>
                            </span>
                        </div>
                        <p class="notice-description"><?php echo indexFormatNoticeDescription($notice['description'] ?? ''); ?></p>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <article class="notice-item">
                    <p class="notice-description">Nenhum aviso dispon&iacute;vel no momento.</p>
                </article>
            <?php endif; ?>
        </div>
    </section>
</section>
