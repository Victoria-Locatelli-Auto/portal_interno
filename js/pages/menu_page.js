document.addEventListener('DOMContentLoaded', function () {
    const tabList = document.querySelector('.submenu-tabs');

    if (!tabList) {
        return;
    }

    const tabButtons = Array.from(tabList.querySelectorAll('[role="tab"]'));
    const tabContents = Array.from(document.querySelectorAll('[role="tabpanel"]'));

    function activateTab(button, moveFocus) {
        const targetId = button.getAttribute('data-tab');
        const targetContent = document.getElementById(targetId);

        if (!targetContent) {
            return;
        }

        tabButtons.forEach((tab) => {
            const isActive = tab === button;
            tab.classList.toggle('active', isActive);
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            tab.tabIndex = isActive ? 0 : -1;
        });

        tabContents.forEach((panel) => {
            const isActive = panel === targetContent;
            panel.classList.toggle('active', isActive);
            panel.hidden = !isActive;
        });

        const submenuId = targetId.replace('submenu-', '');
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('submenu_id', submenuId);
        window.history.replaceState({}, '', currentUrl);

        document.querySelectorAll('[data-sidebar-submenu]').forEach((item) => {
            item.classList.toggle('is-active', item.dataset.sidebarSubmenu === submenuId);
        });

        if (moveFocus) {
            button.focus();
        }
    }

    tabButtons.forEach((button) => {
        button.addEventListener('click', function () {
            activateTab(this, false);
        });

        button.addEventListener('keydown', function (event) {
            const currentIndex = tabButtons.indexOf(this);
            let nextIndex = null;

            if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
                nextIndex = (currentIndex + 1) % tabButtons.length;
            } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
                nextIndex = (currentIndex - 1 + tabButtons.length) % tabButtons.length;
            } else if (event.key === 'Home') {
                nextIndex = 0;
            } else if (event.key === 'End') {
                nextIndex = tabButtons.length - 1;
            }

            if (nextIndex !== null) {
                event.preventDefault();
                activateTab(tabButtons[nextIndex], true);
            }
        });
    });
});
