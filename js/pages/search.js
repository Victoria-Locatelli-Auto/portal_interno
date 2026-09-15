document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.rating-btn').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.rating-btn').forEach((item) => item.classList.remove('is-selected'));
            button.classList.add('is-selected');
            alert(`Obrigado pela avaliação: ${button.dataset.rating}`);
        });
    });
});
