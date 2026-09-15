document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('editNoticeModal');

    const closeModal = () => {
        modal?.classList.remove('active');
        modal?.setAttribute('aria-hidden', 'true');
    };

    const openModal = () => {
        modal?.classList.add('active');
        modal?.setAttribute('aria-hidden', 'false');
    };

    document.querySelectorAll('[data-edit-notice]').forEach((button) => {
        button.addEventListener('click', () => {
            let notice = {};

            try {
                notice = JSON.parse(button.dataset.editNotice || '{}');
            } catch (error) {
                console.error('Dados do aviso inválidos.', error);
                return;
            }

            document.getElementById('edit_notice_id').value = notice.id || '';
            document.getElementById('edit_title').value = notice.title || '';
            document.getElementById('edit_description').value = notice.description || '';
            document.getElementById('edit_content').value = notice.content || '';

            openModal();
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    modal?.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
});
