document.addEventListener('DOMContentLoaded', function () {
    const resetButtons = document.querySelectorAll('[data-confirm-reset="true"]');

    resetButtons.forEach((button) => {
        button.addEventListener('click', function (event) {
            const userName = this.getAttribute('data-user-name') || 'este usuário';

            const confirmed = confirm(
                `Deseja realmente resetar a senha de ${userName} para Mcm@2026?`
            );

            if (!confirmed) {
                event.preventDefault();
            }
        });
    });

    const closeModalButton = document.querySelector('[data-close-reset-modal]');

    if (closeModalButton) {
        closeModalButton.addEventListener('click', function () {
            const modal = document.getElementById('resetPasswordModal');

            if (modal) {
                modal.style.display = 'none';
            }
        });
    }
});