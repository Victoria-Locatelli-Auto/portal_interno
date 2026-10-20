<main class="systems-page">
    <header class="systems-header">
        <div class="systems-heading">
            <span class="systems-heading-icon" aria-hidden="true"><i class="fas fa-table-cells-large"></i></span>
            <div>
                <span class="systems-kicker">Acesso corporativo</span>
                <h1>Sistemas da empresa</h1>
                <p>Acesse as plataformas utilizadas no dia a dia de trabalho.</p>
            </div>
        </div>
    </header>

    <?php if (!empty($systems)): ?>
        <section class="systems-toolbar" aria-label="Buscar sistemas">
            <label for="systems_search"><i class="fas fa-magnifying-glass" aria-hidden="true"></i><span class="sr-only">Buscar sistema</span></label>
            <input type="search" id="systems_search" placeholder="Buscar por nome ou descrição..." autocomplete="off" data-systems-search>
        </section>
    <?php endif; ?>

    <section class="systems-panel" aria-live="polite">
        <?php if (empty($systems)): ?>
            <div class="systems-empty">
                <i class="fas fa-display" aria-hidden="true"></i>
                <strong>Nenhum sistema disponível</strong>
                <span>Os sistemas ativos aparecerão aqui.</span>
            </div>
        <?php else: ?>
            <div class="systems-grid" data-systems-grid>
                <?php foreach ($systems as $system): ?>
                    <?php $logo_url = systemsLogoUrl($base_url, $system['logo_path'] ?? null); ?>
                    <article class="system-card" data-system-card data-search-text="<?php echo htmlspecialchars(mb_strtolower(($system['name'] ?? '') . ' ' . ($system['description'] ?? ''), 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="system-logo">
                            <?php if ($logo_url): ?>
                                <img src="<?php echo htmlspecialchars($logo_url, ENT_QUOTES, 'UTF-8'); ?>" alt="Logo do sistema <?php echo htmlspecialchars($system['name'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                            <?php else: ?>
                                <i class="fas fa-display" aria-hidden="true"></i>
                            <?php endif; ?>
                        </div>
                        <div class="system-content">
                            <h2><?php echo htmlspecialchars($system['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                            <?php if (!empty($system['description'])): ?>
                                <p><?php echo htmlspecialchars($system['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php endif; ?>
                        </div>
                        <a class="system-access" href="<?php echo htmlspecialchars($system['system_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                            <span>Acessar sistema</span><i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="systems-empty" data-systems-no-results hidden>
                <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                <strong>Nenhum sistema encontrado</strong>
                <span>Tente buscar usando outro termo.</span>
            </div>
        <?php endif; ?>
    </section>
</main>
