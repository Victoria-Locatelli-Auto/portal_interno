<div class="contacts-page">
    <header class="contacts-page-header">
        <div class="contacts-page-heading">
            <span class="contacts-page-icon" aria-hidden="true">
                <i class="far fa-address-book"></i>
            </span>

            <div>
                <span class="contacts-page-kicker">Diretório corporativo</span>
                <h1>Contatos</h1>
            </div>
        </div>
    </header>

    <section class="filters-panel" aria-labelledby="contacts-filter-title">
        <div class="panel-heading">
            <div>
                <h2 id="contacts-filter-title">Buscar colaboradores</h2>
            </div>
        </div>

        <form method="GET" class="contacts-filters">
            <div class="field field-search">
                <label for="search">Nome</label>
                <div class="input-control">
                    <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Digite o nome do colaborador"
                    >
                </div>
            </div>

            <div class="field">
                <label for="branch">Filial</label>
                <select name="branch" id="branch">
                    <option value="">Todas as filiais</option>
                    <?php foreach ($branches as $branch): ?>
                        <option value="<?php echo htmlspecialchars($branch['branch']); ?>" <?php echo $branch_filter === $branch['branch'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(formatCorporateText($branch['branch'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="job_title">Cargo</label>
                <select name="job_title" id="job_title">
                    <option value="">Todos os cargos</option>
                    <?php foreach ($jobs as $job): ?>
                        <option value="<?php echo htmlspecialchars($job['job_title']); ?>" <?php echo $job_filter === $job['job_title'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(formatCorporateText($job['job_title'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter" aria-hidden="true"></i>
                    Filtrar
                </button>
                <a href="<?php echo htmlspecialchars($base_url); ?>/pages/contatos.php" class="btn btn-secondary">
                    Limpar
                </a>
            </div>
        </form>
    </section>

    <section class="directory-panel" aria-labelledby="contacts-list-title">
        <div class="directory-heading">
            <div>
                <h2 id="contacts-list-title">Colaboradores</h2>
            </div>
        </div>

        <?php if (!empty($contacts)): ?>
            <div class="contacts-grid">
                <?php foreach ($contacts as $contact): ?>
                    <?php
                    $contact_name = fullName($contact);
                    $contact_photo_url = contactProfilePhotoUrl($base_url, $contact['profile_photo'] ?? null);
                    ?>
                    <article class="contact-card">
                        <div class="contact-header">
                            <span class="contact-avatar">
                                <?php if ($contact_photo_url): ?>
                                    <img
                                        src="<?php echo htmlspecialchars($contact_photo_url); ?>"
                                        alt="Foto de <?php echo htmlspecialchars($contact_name); ?>"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                <?php else: ?>
                                    <i class="far fa-user" aria-hidden="true"></i>
                                <?php endif; ?>
                            </span>
                            <div>
                                <h3><?php echo htmlspecialchars($contact_name); ?></h3>
                                <span><?php echo htmlspecialchars(formatCorporateText($contact['funcao'] ?? 'Cargo não informado')); ?></span>
                            </div>
                        </div>

                        <div class="contact-body">
                            <div class="contact-line">
                                <i class="fas fa-building" aria-hidden="true"></i>
                                <div>
                                    <span class="contact-label">Filial</span>
                                    <span><?php echo htmlspecialchars(formatCorporateText($contact['filiais'] ?? '-')); ?></span>
                                </div>
                            </div>

                            <div class="contact-line">
                                <i class="fab fa-whatsapp" aria-hidden="true"></i>
                                <div>
                                    <span class="contact-label">WhatsApp corporativo</span>
                                    <span><?php echo htmlspecialchars(formatCorporateWhatsApp($contact['wpp_corporativo'] ?? null)); ?></span>
                                </div>
                            </div>

                            <div class="contact-line">
                                <i class="far fa-envelope" aria-hidden="true"></i>
                                <div>
                                    <span class="contact-label">E-mail</span>
                                    <?php if (!empty($contact['email'])): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>">
                                            <?php echo htmlspecialchars($contact['email']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span>-</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <nav class="pagination" aria-label="Paginação de contatos">
                    <?php if ($current_page > 1): ?>
                        <a class="page-link" href="<?php echo htmlspecialchars(buildPageLink(1)); ?>" aria-label="Primeira página">
                            <i class="fas fa-angles-left" aria-hidden="true"></i>
                        </a>
                        <a class="page-link" href="<?php echo htmlspecialchars(buildPageLink($current_page - 1)); ?>" aria-label="Página anterior">
                            <i class="fas fa-angle-left" aria-hidden="true"></i>
                        </a>
                    <?php endif; ?>

                    <?php
                    $start = max(1, $current_page - 2);
                    $end = min($total_pages, $current_page + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <a
                            class="page-link <?php echo $i === $current_page ? 'active' : ''; ?>"
                            href="<?php echo htmlspecialchars(buildPageLink($i)); ?>"
                            <?php echo $i === $current_page ? 'aria-current="page"' : ''; ?>
                        >
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a class="page-link" href="<?php echo htmlspecialchars(buildPageLink($current_page + 1)); ?>" aria-label="Próxima página">
                            <i class="fas fa-angle-right" aria-hidden="true"></i>
                        </a>
                        <a class="page-link" href="<?php echo htmlspecialchars(buildPageLink($total_pages)); ?>" aria-label="Última página">
                            <i class="fas fa-angles-right" aria-hidden="true"></i>
                        </a>
                    <?php endif; ?>

                    <span class="pagination-info">
                        Página <?php echo (int)$current_page; ?> de <?php echo (int)$total_pages; ?>
                    </span>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <span class="empty-state-icon"><i class="fas fa-user-group" aria-hidden="true"></i></span>
                <h3>Nenhum contato encontrado</h3>
                <p>Revise os filtros informados e tente novamente.</p>
            </div>
        <?php endif; ?>
    </section>
</div>
