<div id="editModal" class="menu-modal" aria-hidden="true">
    <div class="menu-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
        <div class="menu-modal-header"><div><span>Categoria</span><h2 id="editModalTitle">Editar categoria</h2></div><button type="button" class="modal-close" data-modal-close="editModal" aria-label="Fechar"><i class="fas fa-times"></i></button></div>
        <form method="POST" class="menu-modal-body">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($manage_menus_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="edit_menu"><input type="hidden" id="edit_menu_id" name="menu_id">
            <div class="form-field"><label for="edit_title">Nome <span>*</span></label><input type="text" id="edit_title" name="title" maxlength="120" required></div>
            <div class="form-field"><label for="edit_menu_type">Tipo <span>*</span></label><select id="edit_menu_type" name="menu_type" required data-url-toggle="edit_url_field"><option value="page">Página com subcategorias</option><option value="link">Link externo</option></select></div>
            <div id="edit_url_field" class="form-field is-hidden"><label for="edit_url">Endereço do link <span>*</span></label><input type="url" id="edit_url" name="url" maxlength="500"></div>
            <div class="modal-icon-grid"><div class="form-field"><label for="edit_icon">Ícone <span>*</span></label><select id="edit_icon" name="icon" required data-icon-preview="edit_icon_preview"><option value="">Selecione um ícone</option><?php foreach ($icons as $icon_class => $icon_label): ?><option value="<?php echo htmlspecialchars($icon_class, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($icon_label, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></div><div class="form-field"><span class="preview-label">Prévia</span><div class="icon-preview-box"><i id="edit_icon_preview" class="fas fa-folder"></i></div></div></div>
            <div class="menu-modal-footer"><button type="button" class="manager-button manager-button-secondary" data-modal-close="editModal">Cancelar</button><button type="submit" class="manager-button manager-button-primary"><i class="fas fa-save"></i> Salvar alterações</button></div>
        </form>
    </div>
</div>

<div id="submenuModal" class="menu-modal" aria-hidden="true">
    <div class="menu-modal-dialog menu-modal-small" role="dialog" aria-modal="true" aria-labelledby="submenuModalTitle">
        <div class="menu-modal-header"><div><span>Subcategoria de <strong id="submenu_menu_name"></strong></span><h2 id="submenuModalTitle">Nova subcategoria</h2></div><button type="button" class="modal-close" data-modal-close="submenuModal" aria-label="Fechar"><i class="fas fa-times"></i></button></div>
        <form method="POST" class="menu-modal-body">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($manage_menus_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="add_submenu"><input type="hidden" id="submenu_menu_id" name="menu_id">
            <div class="form-field"><label for="submenu_title">Nome da subcategoria <span>*</span></label><input type="text" id="submenu_title" name="submenu_title" maxlength="120" placeholder="Ex: Políticas de RH" required></div>
            <div class="menu-modal-footer"><button type="button" class="manager-button manager-button-secondary" data-modal-close="submenuModal">Cancelar</button><button type="submit" class="manager-button manager-button-primary"><i class="fas fa-plus"></i> Adicionar</button></div>
        </form>
    </div>
</div>
