<div class="category-box">
    <h2>
        <i class="fas fa-tag"></i>
        Classificar Documento (Opcional)
    </h2>

    <p>
        <strong>O que é?</strong> Selecione em qual <strong>Categoria (Menu)</strong> este documento deve ser armazenado.
        Se a categoria tiver <strong>subcategorias</strong>, você poderá especificar também.
    </p>

    <div class="form-group">
        <label for="<?php echo htmlspecialchars($menu_select_id); ?>">
            <i class="fas fa-folder"></i>
            Categoria Principal (Menu)
        </label>
        <select id="<?php echo htmlspecialchars($menu_select_id); ?>" name="<?php echo htmlspecialchars($menu_select_id); ?>" data-submenu-source data-submenu-target="<?php echo htmlspecialchars($submenu_container_id); ?>">
            <option value="">-- Nenhuma categoria --</option>
            <?php foreach ($menus as $menu): ?>
                <option value="<?php echo (int)$menu['id']; ?>">
                    <?php echo htmlspecialchars($menu['title']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group is-hidden" id="<?php echo htmlspecialchars($submenu_container_id); ?>">
        <label for="<?php echo htmlspecialchars($submenu_select_id); ?>">
            <i class="fas fa-folder-open"></i>
            Subcategoria (Submenu)
        </label>
        <select id="<?php echo htmlspecialchars($submenu_select_id); ?>" name="<?php echo htmlspecialchars($submenu_select_id); ?>">
            <option value="">-- Nenhuma subcategoria --</option>
        </select>
    </div>
</div>
