<div class="notices-manager-page">
    <header class="notices-page-header">
        <div class="notices-page-heading">
            <span class="notices-page-icon" aria-hidden="true">
                <i class="far fa-bell"></i>
            </span>

            <div>
                <span class="notices-page-kicker">Comunicação interna</span>
                <h1>Gerenciar avisos</h1>
            </div>
        </div>
    </header>

    <?php if ($success_msg): ?>
        <div class="notice-alert is-success" role="status">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <?php echo htmlspecialchars($success_msg); ?>
        </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="notice-alert is-error" role="alert">
            <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
            <?php echo htmlspecialchars($error_msg); ?>
        </div>
    <?php endif; ?>

    <section class="notice-editor-panel" aria-labelledby="new-notice-title">
        <div class="notice-section-heading">
            <div>
                <span class="notice-section-kicker">Novo comunicado</span>
                <h2 id="new-notice-title">Criar aviso</h2>
            </div>
        </div>

        <form method="POST" class="notice-editor-form">
            <input type="hidden" name="action" value="add_notice">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($manage_notices_csrf); ?>">

            <div class="notice-field">
                <label for="title">Título <span aria-hidden="true">*</span></label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="<?php echo htmlspecialchars($notice_form['title']); ?>"
                    placeholder="Ex.: Manutenção programada"
                    maxlength="255"
                    required
                >
            </div>

            <div class="notice-field">
                <label for="description">Descrição <span aria-hidden="true">*</span></label>
                <textarea
                    id="description"
                    name="description"
                    placeholder="Resumo exibido na página inicial"
                    maxlength="1000"
                    rows="3"
                    required
                ><?php echo htmlspecialchars($notice_form['description']); ?></textarea>
            </div>

            <div class="notice-field">
                <label for="content">Conteúdo completo</label>
                <textarea
                    id="content"
                    name="content"
                    placeholder="Informações adicionais do aviso"
                    maxlength="20000"
                    rows="5"
                ><?php echo htmlspecialchars($notice_form['content']); ?></textarea>
            </div>

            <div class="notice-form-actions">
                <button type="submit" class="notice-button is-primary">
                    <i class="far fa-paper-plane" aria-hidden="true"></i>
                    Publicar aviso
                </button>
            </div>
        </form>
    </section>

    <section class="notices-list-panel" aria-labelledby="published-notices-title">
        <div class="notice-section-heading has-stats">
            <div>
                <span class="notice-section-kicker">Histórico</span>
                <h2 id="published-notices-title">Avisos publicados</h2>
            </div>

            <div class="notice-stats" aria-label="Resumo dos avisos">
                <span><strong><?php echo (int)$notice_stats['total']; ?></strong> Total</span>
                <span class="is-active"><strong><?php echo (int)$notice_stats['active']; ?></strong> Ativos</span>
                <span><strong><?php echo (int)$notice_stats['inactive']; ?></strong> Inativos</span>
            </div>
        </div>

        <?php if (empty($notices_list)): ?>
            <div class="notices-empty-state">
                <span><i class="far fa-bell-slash" aria-hidden="true"></i></span>
                <h3>Nenhum aviso publicado</h3>
            </div>
        <?php else: ?>
            <div class="notices-table-wrap">
                <table class="notices-table">
                    <thead>
                        <tr>
                            <th scope="col">Aviso</th>
                            <th scope="col">Descrição</th>
                            <th scope="col">Publicação</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="visually-hidden">Ações</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notices_list as $notice): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars(manageNoticesExcerpt($notice['title'] ?? '', 55)); ?></strong></td>
                                <td><?php echo htmlspecialchars(manageNoticesExcerpt($notice['description'] ?? '', 85)); ?></td>
                                <td><?php echo htmlspecialchars(manageNoticesDate($notice['created_at'] ?? null)); ?></td>
                                <td>
                                    <span class="notice-status <?php echo $notice['is_active'] ? 'is-active' : 'is-inactive'; ?>">
                                        <?php echo $notice['is_active'] ? 'Ativo' : 'Inativo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="notice-row-actions">
                                        <form method="POST">
                                            <input type="hidden" name="action" value="toggle_notice">
                                            <input type="hidden" name="notice_id" value="<?php echo (int)$notice['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($manage_notices_csrf); ?>">
                                            <button type="submit" class="notice-icon-button" title="<?php echo $notice['is_active'] ? 'Desativar' : 'Ativar'; ?> aviso">
                                                <i class="fas fa-toggle-<?php echo $notice['is_active'] ? 'on' : 'off'; ?>" aria-hidden="true"></i>
                                                <span class="visually-hidden"><?php echo $notice['is_active'] ? 'Desativar' : 'Ativar'; ?> aviso</span>
                                            </button>
                                        </form>

                                        <button
                                            type="button"
                                            class="notice-icon-button"
                                            title="Editar aviso"
                                            data-edit-notice="<?php echo htmlspecialchars(json_encode($notice, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
                                        >
                                            <i class="far fa-pen-to-square" aria-hidden="true"></i>
                                            <span class="visually-hidden">Editar aviso</span>
                                        </button>

                                        <form method="POST" data-confirm="Tem certeza que deseja remover este aviso?">
                                            <input type="hidden" name="action" value="delete_notice">
                                            <input type="hidden" name="notice_id" value="<?php echo (int)$notice['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($manage_notices_csrf); ?>">
                                            <button type="submit" class="notice-icon-button is-danger" title="Excluir aviso">
                                                <i class="far fa-trash-can" aria-hidden="true"></i>
                                                <span class="visually-hidden">Excluir aviso</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require __DIR__ . '/partials/manage-notices-modal.php'; ?>
