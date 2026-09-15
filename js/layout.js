function toggleMenu() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
}

function toggleSubmenu(id, button) {
    const submenu = document.getElementById(id);
    if (submenu) {
        submenu.classList.toggle('open');
    }

    if (button) {
        button.classList.toggle('active');
    }
}