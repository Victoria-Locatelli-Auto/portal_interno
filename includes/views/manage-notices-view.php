<section class="notices-manager-page">
    <div class="notices-manager-panel">
        <h1 class="page-title">
            <i class="fas fa-bell"></i>
            Gerenciar Avisos
        </h1>

        <?php if ($success_msg): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_msg); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <section class="notice-section">
            <h2 class="section-title">
                <i class="fas fa-plus"></i>
                Criar Novo Aviso
            </h2>

            <form method="POST">
                <div class="form-group">
                    <label for="title">Título do Aviso *</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        placeholder="Ex: Manutenção do Sistema"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="description">Descrição (Resumo) *</label>
                    <textarea
                        id="description"
                        name="description"
                        placeholder="Breve descrição que aparecerá na página inicial"
                        required
                    ></textarea>
                </div>

                <div class="form-group">
                    <label for="content">Conteúdo Completo (Opcional)</label>
                    <textarea
                        id="content"
                        name="content"
                        placeholder="Detalhes adicionais do aviso"
                    ></textarea>
                </div>

                <input type="hidden" name="action" value="add_notice">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Publicar Aviso
                </button>
            </form>
        </section>

        <section class="notice-section">
            <h2 class="section-title">
                <i class="fas fa-list"></i>
                Avisos Publicados
            </h2>

            <?php if (empty($notices_list)): ?>
                <p class="empty-text">
                    <i class="fas fa-inbox"></i>
                    Nenhum aviso publicado ainda. Crie um novo aviso acima.
                </p>
            <?php else: ?>
                <div class="notices-table-wrap">
                    <table class="notices-table">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Descrição</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notices_list as $notice): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars(manageNoticesExcerpt($notice['title'] ?? '', 45)); ?></strong>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars(manageNoticesExcerpt($notice['description'] ?? '', 65)); ?></small>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars(manageNoticesDate($notice['created_at'] ?? null)); ?></small>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $notice['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $notice['is_active'] ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <form method="POST">
                                                <input type="hidden" name="action" value="toggle_notice">
                                                <input type="hidden" name="notice_id" value="<?php echo (int)$notice['id']; ?>">
                                                <button type="submit" class="btn btn-secondary" title="Ativar/Desativar">
                                                    <i class="fas fa-toggle-<?php echo $notice['is_active'] ? 'on' : 'off'; ?>"></i>
                                                </button>
                                            </form>

                                            <button
                                                type="button"
                                                class="btn btn-primary"
                                                title="Editar"
                                                data-edit-notice="<?php echo htmlspecialchars(json_encode($notice, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)); ?>"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <form method="POST" data-confirm="Tem certeza que deseja remover este aviso?">
                                                <input type="hidden" name="action" value="delete_notice">
                                                <input type="hidden" name="notice_id" value="<?php echo (int)$notice['id']; ?>">
                                                <button type="submit" class="btn btn-danger" title="Deletar">
                                                    <i class="fas fa-trash"></i>
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
</section>

<?php require __DIR__ . '/partials/manage-notices-modal.php'; ?>
