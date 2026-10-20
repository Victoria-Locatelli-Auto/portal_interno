<div id="editNoticeModal" class="notice-modal" aria-hidden="true">
    <div class="notice-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="edit-notice-title">
        <div class="notice-modal-header">
            <div>
                <span class="notice-section-kicker">Atualização</span>
                <h2 id="edit-notice-title">Editar aviso</h2>
            </div>

            <button type="button" class="notice-modal-close" data-modal-close aria-label="Fechar edição">
                <i class="fas fa-xmark" aria-hidden="true"></i>
            </button>
        </div>

        <form method="POST" class="notice-modal-form">
            <input type="hidden" name="action" value="edit_notice">
            <input type="hidden" id="edit_notice_id" name="notice_id">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($manage_notices_csrf); ?>">

            <div class="notice-field">
                <label for="edit_title">Título <span aria-hidden="true">*</span></label>
                <input type="text" id="edit_title" name="title" maxlength="255" required>
            </div>

            <div class="notice-field">
                <label for="edit_description">Descrição <span aria-hidden="true">*</span></label>
                <textarea id="edit_description" name="description" maxlength="1000" rows="3" required></textarea>
            </div>

            <div class="notice-field">
                <label for="edit_content">Conteúdo completo</label>
                <textarea id="edit_content" name="content" maxlength="20000" rows="6"></textarea>
            </div>

            <div class="notice-modal-actions">
                <button type="button" class="notice-button is-secondary" data-modal-close>
                    Cancelar
                </button>
                <button type="submit" class="notice-button is-primary">
                    <i class="far fa-floppy-disk" aria-hidden="true"></i>
                    Salvar alterações
                </button>
            </div>
        </form>
    </div>
</div>
