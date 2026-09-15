document.addEventListener('DOMContentLoaded', () => {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    if (tabButtons.length === 0 || tabContents.length === 0) {
        return;
    }

    tabButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const targetId = button.dataset.tabTarget;

            tabButtons.forEach((item) => {
                item.classList.remove('active');
                item.setAttribute('aria-selected', 'false');
            });

            tabContents.forEach((content) => {
                content.classList.remove('active');
            });

            button.classList.add('active');
            button.setAttribute('aria-selected', 'true');
            document.getElementById(targetId)?.classList.add('active');
        });
    });
});
