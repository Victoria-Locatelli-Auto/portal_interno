<div class="content-wrap">
    <h1 class="hero-title">Contatos</h1>
    <div class="hero-subtitle">“A comunicação é o melhor instrumento a serviço do sucesso da empresa.”</div>

    <form method="GET" class="filters-box">
        <div class="filters-grid">
            <div class="field">
                <label for="search">Pesquisar por nome</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Digite o nome do funcionário"
                >
            </div>

            <div class="field">
                <label for="branch">Filial</label>
                <select name="branch" id="branch">
                    <option value="">Todas</option>
                    <?php foreach ($branches as $branch): ?>
                        <option value="<?php echo htmlspecialchars($branch['branch']); ?>" <?php echo $branch_filter === $branch['branch'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($branch['branch']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="job_title">Cargo</label>
                <select name="job_title" id="job_title">
                    <option value="">Todos</option>
                    <?php foreach ($jobs as $job): ?>
                        <option value="<?php echo htmlspecialchars($job['job_title']); ?>" <?php echo $job_filter === $job['job_title'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($job['job_title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="contatos.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpar
                </a>
            </div>
        </div>
    </form>

    <?php if (!empty($contacts)): ?>
        <div class="contacts-grid">
            <?php foreach ($contacts as $contact): ?>
                <div class="contact-card">
                    <div class="contact-header">
                        <i class="fas fa-shield-heart"></i>
                        <span><?php echo htmlspecialchars(fullName($contact)); ?></span>
                    </div>

                    <div class="contact-body">
                        <div class="contact-line">
                            <span class="contact-label">Filial:</span>
                            <?php echo htmlspecialchars($contact['filiais'] ?? '-'); ?>
                        </div>

                        <div class="contact-line">
                            <span class="contact-label">Cargo:</span>
                            <?php echo htmlspecialchars($contact['funcao'] ?? '-'); ?>
                        </div>

                        <div class="contact-line">
                            <span class="contact-label">WhatsApp:</span>
                            <?php echo htmlspecialchars($contact['wpp_corporativo'] ?? '-'); ?>
                        </div>

                        <div class="contact-line">
                            <span class="contact-label">E-mail:</span>
                            <?php echo htmlspecialchars($contact['email'] ?? '-'); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a class="page-link" href="<?php echo htmlspecialchars(buildPageLink(1)); ?>">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a class="page-link" href="<?php echo htmlspecialchars(buildPageLink($current_page - 1)); ?>">
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php endif; ?>

                <?php
                $start = max(1, $current_page - 2);
                $end = min($total_pages, $current_page + 2);
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <a class="page-link <?php echo $i === $current_page ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(buildPageLink($i)); ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a class="page-link" href="<?php echo htmlspecialchars(buildPageLink($current_page + 1)); ?>">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a class="page-link" href="<?php echo htmlspecialchars(buildPageLink($total_pages)); ?>">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php endif; ?>

                <div class="results-info">
                    Página <strong><?php echo $current_page; ?></strong> de <strong><?php echo $total_pages; ?></strong>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-box">
            Nenhum contato encontrado com os filtros informados.
        </div>
    <?php endif; ?>
</div>
