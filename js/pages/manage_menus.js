document.addEventListener('DOMContentLoaded', () => {
    let lastFocusedElement = null;

    const toggleUrlField = (select) => {
        const field = document.getElementById(select.dataset.urlToggle || '');
        const input = field?.querySelector('input');
        if (!field || !input) return;
        const isExternal = select.value === 'link';
        field.classList.toggle('is-hidden', !isExternal);
        input.required = isExternal;
        if (!isExternal) input.value = '';
    };

    const updateIconPreview = (select) => {
        const preview = document.getElementById(select.dataset.iconPreview || '');
        if (preview) preview.className = select.value || 'fas fa-folder';
    };

    const openModal = (id) => {
        const modal = document.getElementById(id);
        if (!modal) return;
        lastFocusedElement = document.activeElement;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('has-open-modal');
        window.setTimeout(() => modal.querySelector('input:not([type="hidden"]), select, button')?.focus(), 0);
    };

    const closeModal = (modal) => {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        if (!document.querySelector('.menu-modal.is-open')) document.body.classList.remove('has-open-modal');
        lastFocusedElement?.focus();
    };

    document.querySelectorAll('[data-url-toggle]').forEach((select) => {
        select.addEventListener('change', () => toggleUrlField(select));
        toggleUrlField(select);
    });

    document.querySelectorAll('[data-icon-preview]').forEach((select) => {
        select.addEventListener('change', () => updateIconPreview(select));
        updateIconPreview(select);
    });

    document.querySelectorAll('[data-edit-menu]').forEach((button) => {
        button.addEventListener('click', () => {
            try {
                const menu = JSON.parse(button.dataset.editMenu || '{}');
                document.getElementById('edit_menu_id').value = menu.id || '';
                document.getElementById('edit_title').value = menu.title || '';
                document.getElementById('edit_menu_type').value = menu.menu_type || 'page';
                document.getElementById('edit_url').value = menu.url || '';
                document.getElementById('edit_icon').value = menu.icon || '';
                toggleUrlField(document.getElementById('edit_menu_type'));
                if (menu.menu_type === 'link') document.getElementById('edit_url').value = menu.url || '';
                updateIconPreview(document.getElementById('edit_icon'));
                openModal('editModal');
            } catch (error) {
                console.error('Não foi possível abrir os dados da categoria.', error);
            }
        });
    });

    document.querySelectorAll('[data-add-submenu]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('submenu_menu_id').value = button.dataset.menuId || '';
            document.getElementById('submenu_menu_name').textContent = button.dataset.menuName || '';
            document.getElementById('submenu_title').value = '';
            openModal('submenuModal');
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach((button) => button.addEventListener('click', () => closeModal(document.getElementById(button.dataset.modalClose))));
    document.querySelectorAll('.menu-modal').forEach((modal) => modal.addEventListener('mousedown', (event) => { if (event.target === modal) closeModal(modal); }));
    document.querySelectorAll('[data-confirm]').forEach((form) => form.addEventListener('submit', (event) => { if (!window.confirm(form.dataset.confirm)) event.preventDefault(); }));

    document.addEventListener('keydown', (event) => {
        const modal = document.querySelector('.menu-modal.is-open');
        if (!modal) return;
        if (event.key === 'Escape') closeModal(modal);
        if (event.key !== 'Tab') return;
        const focusable = [...modal.querySelectorAll('button:not([disabled]), input:not([type="hidden"]):not([disabled]), select:not([disabled])')].filter((element) => element.offsetParent !== null);
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    });
});
