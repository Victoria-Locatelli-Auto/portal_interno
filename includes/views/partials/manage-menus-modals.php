<div id="editModal" class="modal" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true">
        <div class="modal-header">
            <i class="fas fa-edit"></i>
            Editar Categoria
        </div>

        <form method="POST" class="modal-body">
            <div class="form-group">
                <label for="edit_title">Nome *</label>
                <input type="text" id="edit_title" name="title" required>
            </div>

            <div class="form-group">
                <label for="edit_menu_type">Tipo *</label>
                <select id="edit_menu_type" name="menu_type" required data-url-toggle="edit_url_field">
                    <option value="page">Com Subcategorias</option>
                    <option value="link">Link Externo</option>
                </select>
            </div>

            <div id="edit_url_field" class="form-group is-hidden">
                <label for="edit_url">URL / Link Externo</label>
                <input type="text" id="edit_url" name="url">
                <small class="form-hint">Obrigatório para links externos</small>
            </div>

            <div class="form-group">
                <label for="edit_icon">Ícone *</label>
                <select id="edit_icon" name="icon" required data-icon-preview="edit_icon_preview">
                    <option value="">-- Selecione um ícone --</option>
                    <?php foreach ($icons as $icon_class => $icon_label): ?>
                        <option value="<?php echo htmlspecialchars($icon_class); ?>">
                            <?php echo htmlspecialchars($icon_label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label><i class="fas fa-eye"></i> Prévia do Ícone</label>
                <div class="icon-preview-box">
                    <i id="edit_icon_preview" class="fas fa-folder"></i>
                </div>
            </div>

            <input type="hidden" name="action" value="edit_menu">
            <input type="hidden" id="edit_menu_id" name="menu_id">

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="editModal">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<div id="submenuModal" class="modal" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true">
        <div class="modal-header">
            <i class="fas fa-plus"></i>
            Adicionar Subcategoria em <span id="submenu_menu_name"></span>
        </div>

        <form method="POST" class="modal-body">
            <div class="form-group">
                <label for="submenu_title">Nome da Subcategoria *</label>
                <input type="text" id="submenu_title" name="submenu_title" placeholder="Ex: 2024, Aprovados, Técnicos" required>
            </div>

            <input type="hidden" name="action" value="add_submenu">
            <input type="hidden" id="submenu_menu_id" name="menu_id">

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="submenuModal">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus"></i>
                    Adicionar
                </button>
            </div>
        </form>
    </div>
</div>
