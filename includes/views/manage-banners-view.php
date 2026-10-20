<main class="banner-manager-page">
    <header class="banner-page-header">
        <div class="banner-heading"><span class="banner-heading-icon"><i class="far fa-images"></i></span><div><span class="banner-kicker">Comunicação visual</span><h1>Gerenciar banners</h1></div></div>
        <a class="banner-home-link" href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>/index.php"><i class="fas fa-arrow-up-right-from-square"></i> Ver página inicial</a>
    </header>

    <?php if (!empty($feedback['message'])): ?><div class="banner-alert is-<?php echo $feedback['type'] === 'success' ? 'success' : 'error'; ?>" role="<?php echo $feedback['type'] === 'success' ? 'status' : 'alert'; ?>"><i class="fas fa-<?php echo $feedback['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i><span><?php echo htmlspecialchars($feedback['message'], ENT_QUOTES, 'UTF-8'); ?></span></div><?php endif; ?>

    <section class="banner-stats">
        <div class="banner-stat"><strong><?php echo (int)$banner_stats['total']; ?></strong><span>Cadastrados</span></div>
        <div class="banner-stat"><strong><?php echo (int)$banner_stats['active']; ?></strong><span>Ativos</span></div>
        <div class="banner-stat"><strong><?php echo (int)$banner_stats['scheduled']; ?></strong><span>Agendados</span></div>
        <div class="banner-stat"><strong><?php echo (int)$banner_stats['expired']; ?></strong><span>Encerrados</span></div>
    </section>

    <section class="banner-panel">
        <div class="banner-panel-heading"><div><h2>Novo banner</h2><p>Para melhor resultado, use uma imagem horizontal com proporção aproximada de 4:1.</p></div></div>
        <form method="POST" enctype="multipart/form-data" class="banner-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($manage_banners_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="add_banner">
            <div class="banner-upload-grid">
                <div class="banner-image-field">
                    <label for="banner_image" class="banner-dropzone" data-banner-dropzone>
                        <input type="file" id="banner_image" name="banner_image" accept="image/jpeg,image/png,image/webp" required>
                        <span class="banner-upload-placeholder" data-upload-placeholder><i class="fas fa-cloud-arrow-up"></i><strong>Selecionar imagem</strong><small>JPG, PNG ou WebP · até 5 MB</small></span>
                        <img src="" alt="" data-banner-preview hidden>
                    </label>
                </div>
                <div class="banner-fields">
                    <div class="banner-field"><label for="alt_text">Descrição da imagem <span>*</span></label><input type="text" id="alt_text" name="alt_text" maxlength="255" placeholder="Ex.: Campanha de segurança no trânsito" required><small>Esse texto torna o banner acessível.</small></div>
                    <div class="banner-field"><label for="title">Título sobre a imagem</label><input type="text" id="title" name="title" maxlength="160" placeholder="Ex.: Semana da Segurança"></div>
                    <div class="banner-field"><label for="target_url">Link ao clicar</label><input type="url" id="target_url" name="target_url" maxlength="500" placeholder="https://exemplo.com.br"></div>
                </div>
            </div>
            <div class="banner-schedule-grid">
                <div class="banner-field"><label for="starts_at">Início da exibição</label><input type="datetime-local" id="starts_at" name="starts_at"></div>
                <div class="banner-field"><label for="ends_at">Fim da exibição</label><input type="datetime-local" id="ends_at" name="ends_at"></div>
                <label class="banner-toggle"><input type="checkbox" name="is_active" value="1" checked><span aria-hidden="true"></span><div><strong>Banner ativo</strong><small>Pode ser desativado depois.</small></div></label>
            </div>
            <div class="banner-form-actions"><button type="submit" class="banner-button is-primary"><i class="fas fa-plus"></i> Cadastrar banner</button></div>
        </form>
    </section>

    <section class="banner-panel">
        <div class="banner-panel-heading"><div><h2>Banners cadastrados</h2><p>A home mostra somente banners ativos e dentro do período configurado.</p></div></div>
        <?php if (empty($banners)): ?><div class="banner-empty"><i class="far fa-image"></i><strong>Nenhum banner cadastrado</strong><span>Adicione a primeira imagem usando o formulário acima.</span></div><?php else: ?>
            <div class="banner-list">
                <?php foreach ($banners as $banner): ?>
                    <?php $image_url = manageBannersImageUrl($base_url, $banner['image_path'] ?? null); $now = time(); $is_scheduled = !empty($banner['starts_at']) && strtotime($banner['starts_at']) > $now; $is_expired = !empty($banner['ends_at']) && strtotime($banner['ends_at']) < $now; ?>
                    <article class="banner-item">
                        <div class="banner-item-image"><?php if ($image_url): ?><img src="<?php echo htmlspecialchars($image_url, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($banner['alt_text'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy"><?php else: ?><i class="fas fa-image"></i><?php endif; ?></div>
                        <div class="banner-item-content"><div class="banner-item-title"><strong><?php echo htmlspecialchars($banner['title'] ?: $banner['alt_text'], ENT_QUOTES, 'UTF-8'); ?></strong><span class="banner-status <?php echo empty($banner['is_active']) ? 'is-inactive' : ($is_expired ? 'is-expired' : ($is_scheduled ? 'is-scheduled' : 'is-visible')); ?>"><?php echo empty($banner['is_active']) ? 'Inativo' : ($is_expired ? 'Encerrado' : ($is_scheduled ? 'Agendado' : 'Visível')); ?></span></div><div class="banner-meta"><span><i class="far fa-calendar"></i> <?php echo htmlspecialchars(manageBannersFormatDate($banner['starts_at'] ?? null), ENT_QUOTES, 'UTF-8'); ?> até <?php echo htmlspecialchars(manageBannersFormatDate($banner['ends_at'] ?? null), ENT_QUOTES, 'UTF-8'); ?></span><?php if (!empty($banner['target_url'])): ?><a href="<?php echo htmlspecialchars($banner['target_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer"><i class="fas fa-link"></i> Abrir link</a><?php endif; ?><span><i class="far fa-user"></i> <?php echo htmlspecialchars($banner['created_by_name'] ?? 'Administrador', ENT_QUOTES, 'UTF-8'); ?></span></div></div>
                        <div class="banner-item-actions"><form method="POST"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($manage_banners_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="toggle_banner"><input type="hidden" name="banner_id" value="<?php echo (int)$banner['id']; ?>"><button type="submit" class="banner-icon-button" title="<?php echo !empty($banner['is_active']) ? 'Desativar' : 'Ativar'; ?>"><i class="fas fa-toggle-<?php echo !empty($banner['is_active']) ? 'on' : 'off'; ?>"></i></button></form><form method="POST" data-confirm="Excluir este banner permanentemente?"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($manage_banners_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="delete_banner"><input type="hidden" name="banner_id" value="<?php echo (int)$banner['id']; ?>"><button type="submit" class="banner-icon-button is-danger" title="Excluir banner"><i class="fas fa-trash"></i></button></form></div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
