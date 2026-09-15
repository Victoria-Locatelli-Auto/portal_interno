function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');

    if (sidebar) {
        sidebar.classList.toggle('open');
    }
}

function toggleSubmenu(id, button) {
    const submenu = document.getElementById(id);

    if (!submenu) {
        return;
    }

    submenu.classList.toggle('open');
    button.classList.toggle('active');
}

document.addEventListener('DOMContentLoaded', () => {
    const sidebarButton = document.querySelector('[data-toggle-sidebar]');
    const submenuButtons = document.querySelectorAll('[data-toggle-submenu]');

    if (sidebarButton) {
        sidebarButton.addEventListener('click', toggleSidebar);
    }

    submenuButtons.forEach((button) => {
        button.addEventListener('click', () => {
            toggleSubmenu(button.dataset.toggleSubmenu, button);
        });
    });
});
