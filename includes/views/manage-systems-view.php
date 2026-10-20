<main class="systems-manager-page">
    <header class="systems-manager-header">
        <div class="systems-manager-heading">
            <span class="systems-manager-icon" aria-hidden="true"><i class="fas fa-laptop-code"></i></span>
            <div><span class="systems-manager-kicker">Acessos corporativos</span><h1>Gerenciar sistemas</h1></div>
        </div>
        <a class="systems-view-link" href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>/pages/sistemas.php"><i class="fas fa-arrow-up-right-from-square"></i> Ver sistemas</a>
    </header>

    <?php if (!empty($feedback['message'])): ?>
        <div class="systems-alert is-<?php echo $feedback['type'] === 'success' ? 'success' : 'error'; ?>" role="<?php echo $feedback['type'] === 'success' ? 'status' : 'alert'; ?>">
            <i class="fas fa-<?php echo $feedback['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <span><?php echo htmlspecialchars($feedback['message'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    <?php endif; ?>

    <section class="systems-stats">
        <div class="systems-stat"><strong><?php echo (int)$system_stats['total']; ?></strong><span>Cadastrados</span></div>
        <div class="systems-stat"><strong><?php echo (int)$system_stats['active']; ?></strong><span>Ativos</span></div>
        <div class="systems-stat"><strong><?php echo (int)$system_stats['inactive']; ?></strong><span>Inativos</span></div>
    </section>

    <section class="systems-admin-panel">
        <div class="systems-panel-heading"><h2>Novo sistema</h2><p>Cadastre o endereço e a identidade visual da plataforma.</p></div>
        <form method="POST" enctype="multipart/form-data" class="systems-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($manage_systems_csrf, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="action" value="add_system">

            <div class="systems-form-grid">
                <label class="system-logo-dropzone" for="system_logo" data-logo-dropzone>
                    <input type="file" id="system_logo" name="system_logo" accept="image/jpeg,image/png,image/webp">
                    <span data-logo-placeholder><i class="fas fa-cloud-arrow-up"></i><strong>Selecionar logo</strong><small>JPG, PNG ou WebP · até 2 MB</small></span>
                    <img src="" alt="" data-logo-preview hidden>
                </label>

                <div class="systems-fields">
                    <div class="systems-field"><label for="name">Nome do sistema <span>*</span></label><input type="text" id="name" name="name" maxlength="120" placeholder="Ex.: Sistema de chamados" required></div>
                    <div class="systems-field"><label for="description">Descrição</label><input type="text" id="description" name="description" maxlength="255" placeholder="Ex.: Solicitações e acompanhamento de suporte"></div>
                    <div class="systems-field systems-field-full"><label for="system_url">Link do sistema <span>*</span></label><input type="url" id="system_url" name="system_url" maxlength="2048" placeholder="https://sistema.exemplo.com.br" required></div>
                    <div class="systems-field"><label for="order_position">Ordem de exibição</label><input type="number" id="order_position" name="order_position" min="0" max="9999" value="0"><small>Use 0 para adicionar ao final.</small></div>
                    <label class="systems-toggle"><input type="checkbox" name="is_active" value="1" checked><span aria-hidden="true"></span><div><strong>Sistema ativo</strong><small>Visível para os colaboradores.</small></div></label>
                </div>
            </div>

            <div class="systems-form-actions"><button type="submit" class="systems-button is-primary"><i class="fas fa-plus"></i> Cadastrar sistema</button></div>
        </form>
    </section>

    <section class="systems-admin-panel">
        <div class="systems-panel-heading"><h2>Sistemas cadastrados</h2><p>Controle quais acessos ficam disponíveis aos colaboradores.</p></div>

        <?php if (empty($systems)): ?>
            <div class="systems-admin-empty"><i class="fas fa-display"></i><strong>Nenhum sistema cadastrado</strong><span>Adicione o primeiro sistema usando o formulário acima.</span></div>
        <?php else: ?>
            <div class="systems-admin-list">
                <?php foreach ($systems as $system): ?>
                    <?php $logo_url = manageSystemsLogoUrl($base_url, $system['logo_path'] ?? null); ?>
                    <article class="systems-admin-item">
                        <div class="systems-admin-logo"><?php if ($logo_url): ?><img src="<?php echo htmlspecialchars($logo_url, ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy"><?php else: ?><i class="fas fa-display"></i><?php endif; ?></div>
                        <div class="systems-admin-content">
                            <div class="systems-admin-title"><strong><?php echo htmlspecialchars($system['name'], ENT_QUOTES, 'UTF-8'); ?></strong><span class="systems-status <?php echo !empty($system['is_active']) ? 'is-active' : 'is-inactive'; ?>"><?php echo !empty($system['is_active']) ? 'Ativo' : 'Inativo'; ?></span></div>
                            <?php if (!empty($system['description'])): ?><p><?php echo htmlspecialchars($system['description'], ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
                            <div class="systems-admin-meta"><a href="<?php echo htmlspecialchars($system['system_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer"><i class="fas fa-link"></i> Abrir sistema</a><span><i class="fas fa-arrow-down-1-9"></i> Ordem <?php echo (int)$system['order_position']; ?></span></div>
                        </div>
                        <div class="systems-admin-actions">
                            <form method="POST" enctype="multipart/form-data" class="systems-logo-action">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($manage_systems_csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="action" value="update_logo">
                                <input type="hidden" name="system_id" value="<?php echo (int)$system['id']; ?>">
                                <input type="file" id="system-logo-<?php echo (int)$system['id']; ?>" name="system_logo" accept="image/jpeg,image/png,image/webp" data-system-logo-input required>
                                <label for="system-logo-<?php echo (int)$system['id']; ?>" class="systems-icon-button" title="<?php echo $logo_url ? 'Substituir logo' : 'Adicionar logo'; ?>"><i class="fas fa-image"></i></label>
                            </form>
                            <form method="POST"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($manage_systems_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="toggle_system"><input type="hidden" name="system_id" value="<?php echo (int)$system['id']; ?>"><button type="submit" class="systems-icon-button" title="<?php echo !empty($system['is_active']) ? 'Desativar' : 'Ativar'; ?>"><i class="fas fa-toggle-<?php echo !empty($system['is_active']) ? 'on' : 'off'; ?>"></i></button></form>
                            <form method="POST" data-confirm="Excluir este sistema permanentemente?"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($manage_systems_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="delete_system"><input type="hidden" name="system_id" value="<?php echo (int)$system['id']; ?>"><button type="submit" class="systems-icon-button is-danger" title="Excluir sistema"><i class="fas fa-trash"></i></button></form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
