document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('editNoticeModal');
    const modalDialog = modal?.querySelector('[role="dialog"]');
    let triggerButton = null;

    const closeModal = () => {
        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('has-open-modal');
        triggerButton?.focus();
        triggerButton = null;
    };

    const openModal = (button) => {
        if (!modal) {
            return;
        }

        triggerButton = button;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('has-open-modal');
        document.getElementById('edit_title')?.focus();
    };

    document.querySelectorAll('[data-edit-notice]').forEach((button) => {
        button.addEventListener('click', () => {
            let notice;

            try {
                notice = JSON.parse(button.dataset.editNotice || '{}');
            } catch (error) {
                console.error('Dados do aviso inválidos.', error);
                return;
            }

            const noticeId = document.getElementById('edit_notice_id');
            const title = document.getElementById('edit_title');
            const description = document.getElementById('edit_description');
            const content = document.getElementById('edit_content');

            if (!noticeId || !title || !description || !content) {
                return;
            }

            noticeId.value = notice.id || '';
            title.value = notice.title || '';
            description.value = notice.description || '';
            content.value = notice.content || '';
            openModal(button);
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

    modalDialog?.addEventListener('keydown', (event) => {
        if (event.key !== 'Tab') {
            return;
        }

        const focusable = [...modalDialog.querySelectorAll(
            'button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), a[href]'
        )];

        if (focusable.length === 0) {
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal?.classList.contains('is-open')) {
            closeModal();
        }
    });
});
