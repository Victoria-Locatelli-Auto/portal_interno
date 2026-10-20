document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-confirm]').forEach((button) => {
        button.addEventListener('click', (event) => {
            if (!window.confirm(button.dataset.confirm || 'Confirmar esta ação?')) event.preventDefault();
        });
    });

    document.querySelectorAll('.admin-action-menu').forEach((menu) => {
        menu.addEventListener('toggle', () => {
            if (!menu.open) return;
            document.querySelectorAll('.admin-action-menu[open]').forEach((other) => {
                if (other !== menu) other.removeAttribute('open');
            });
        });
    });

    document.addEventListener('click', (event) => {
        if (event.target.closest('.admin-action-menu')) return;
        document.querySelectorAll('.admin-action-menu[open]').forEach((menu) => menu.removeAttribute('open'));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') document.querySelectorAll('.admin-action-menu[open]').forEach((menu) => menu.removeAttribute('open'));
    });
});
