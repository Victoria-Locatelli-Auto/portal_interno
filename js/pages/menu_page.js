document.addEventListener('DOMContentLoaded', function () {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    if (!tabButtons.length) {
        return;
    }

    tabButtons.forEach((button) => {
        button.addEventListener('click', function () {
            const targetId = this.getAttribute('data-tab');
            const targetContent = document.getElementById(targetId);

            if (!targetContent) {
                return;
            }

            tabButtons.forEach((btn) => btn.classList.remove('active'));
            tabContents.forEach((tab) => tab.classList.remove('active'));

            this.classList.add('active');
            targetContent.classList.add('active');
        });
    });
});