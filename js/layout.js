function toggleMenu() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
}

function toggleSubmenu(id, button) {
    const submenu = document.getElementById(id);
    const sidebar = document.getElementById('sidebar');

    if (!submenu) {
        return;
    }

    if (sidebar && !sidebar.classList.contains('open')) {
        sidebar.classList.add('open');
    }

    const isOpen = submenu.classList.toggle('open');

    if (button) {
        button.classList.toggle('active', isOpen);
        button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }
}
