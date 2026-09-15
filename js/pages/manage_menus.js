document.addEventListener('DOMContentLoaded', () => {
    const toggleUrlField = (select) => {
        const field = document.getElementById(select.dataset.urlToggle);
        const input = field?.querySelector('input');

        if (!field || !input) {
            return;
        }

        if (select.value === 'page') {
            field.classList.add('is-hidden');
            input.removeAttribute('required');
        } else {
            field.classList.remove('is-hidden');
            input.setAttribute('required', 'required');
            input.placeholder = 'Ex: https://exemplo.com.br';
        }
    };

    const updateIconPreview = (select) => {
        const preview = document.getElementById(select.dataset.iconPreview);

        if (preview && select.value) {
            preview.className = select.value;
        }
    };

    const openModal = (id) => {
        const modal = document.getElementById(id);
        modal?.classList.add('active');
        modal?.setAttribute('aria-hidden', 'false');
    };

    const closeModal = (id) => {
        const modal = document.getElementById(id);
        modal?.classList.remove('active');
        modal?.setAttribute('aria-hidden', 'true');
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
            let menu = {};

            try {
                menu = JSON.parse(button.dataset.editMenu || '{}');
            } catch (error) {
                console.error('Dados do menu inválidos.', error);
                return;
            }

            document.getElementById('edit_menu_id').value = menu.id || '';
            document.getElementById('edit_title').value = menu.title || '';
            document.getElementById('edit_menu_type').value = menu.menu_type || 'page';
            document.getElementById('edit_url').value = menu.url || '';
            document.getElementById('edit_icon').value = menu.icon || '';

            toggleUrlField(document.getElementById('edit_menu_type'));
            updateIconPreview(document.getElementById('edit_icon'));
            openModal('editModal');
        });
    });

    document.querySelectorAll('[data-add-submenu]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('submenu_menu_id').value = button.dataset.menuId || '';
            document.getElementById('submenu_menu_name').textContent = button.dataset.menuName || '';
            document.getElementById('submenu_title').value = '';
            openModal('submenuModal');
            document.getElementById('submenu_title').focus();
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach((button) => {
        button.addEventListener('click', () => closeModal(button.dataset.modalClose));
    });

    document.querySelectorAll('.modal').forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal(modal.id);
            }
        });
    });

    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal.active').forEach((modal) => closeModal(modal.id));
        }
    });
});
