document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('.documents-page');
    const menuSelect = document.getElementById('filter_menu');
    const submenuField = document.getElementById('submenuField');
    const submenuSelect = document.getElementById('filter_submenu');

    const loadSubmenus = async () => {
        if (!page || !menuSelect || !submenuField || !submenuSelect) {
            return;
        }

        const menuId = menuSelect.value;
        const selectedSubmenu = submenuSelect.dataset.selected || '';
        submenuSelect.innerHTML = '<option value="">-- Todas as Subcategorias --</option>';

        if (!menuId) {
            submenuField.classList.add('is-hidden');
            return;
        }

        try {
            const url = new URL(page.dataset.submenusUrl, window.location.origin);
            url.searchParams.set('menu_id', menuId);

            const response = await fetch(url.toString(), {
                headers: {
                    Accept: 'application/json',
                },
            });
            const data = await response.json();
            const submenus = Array.isArray(data.submenus) ? data.submenus : [];

            if (submenus.length === 0) {
                submenuField.classList.add('is-hidden');
                return;
            }

            submenus.forEach((submenu) => {
                const option = document.createElement('option');
                option.value = submenu.id;
                option.textContent = submenu.title;

                if (String(submenu.id) === selectedSubmenu) {
                    option.selected = true;
                }

                submenuSelect.appendChild(option);
            });

            submenuField.classList.remove('is-hidden');
        } catch (error) {
            submenuField.classList.add('is-hidden');
            console.error('Erro ao carregar submenus:', error);
        }
    };

    if (menuSelect) {
        menuSelect.addEventListener('change', () => {
            submenuSelect.dataset.selected = '';
            loadSubmenus();
        });
    }

    if (document.querySelector('.status-processando')) {
        setTimeout(() => {
            window.location.reload();
        }, 5000);
    }
});
