<fieldset class="import-category-fields">
    <legend>Classificação <span>opcional</span></legend>

    <div class="import-category-grid">
        <div class="import-field">
            <label for="<?php echo htmlspecialchars($menu_select_id); ?>">Categoria</label>
            <select
                id="<?php echo htmlspecialchars($menu_select_id); ?>"
                name="<?php echo htmlspecialchars($menu_select_id); ?>"
                data-submenu-source
                data-submenu-target="<?php echo htmlspecialchars($submenu_container_id); ?>"
            >
                <option value="">Nenhuma categoria</option>
                <?php foreach ($menus as $menu): ?>
                    <option value="<?php echo (int)$menu['id']; ?>">
                        <?php echo htmlspecialchars($menu['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="import-field is-hidden" id="<?php echo htmlspecialchars($submenu_container_id); ?>">
            <label for="<?php echo htmlspecialchars($submenu_select_id); ?>">Subcategoria</label>
            <select id="<?php echo htmlspecialchars($submenu_select_id); ?>" name="<?php echo htmlspecialchars($submenu_select_id); ?>">
                <option value="">Nenhuma subcategoria</option>
            </select>
        </div>
    </div>
</fieldset>
