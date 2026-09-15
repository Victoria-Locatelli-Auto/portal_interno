<div id="editNoticeModal" class="modal" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true">
        <div class="modal-header">
            <i class="fas fa-edit"></i>
            Editar Aviso
        </div>

        <form method="POST" class="modal-body">
            <div class="form-group">
                <label for="edit_title">Título do Aviso *</label>
                <input type="text" id="edit_title" name="title" required>
            </div>

            <div class="form-group">
                <label for="edit_description">Descrição *</label>
                <textarea id="edit_description" name="description" required></textarea>
            </div>

            <div class="form-group">
                <label for="edit_content">Conteúdo Completo</label>
                <textarea id="edit_content" name="content"></textarea>
            </div>

            <input type="hidden" name="action" value="edit_notice">
            <input type="hidden" id="edit_notice_id" name="notice_id">

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="editNoticeModal">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>
