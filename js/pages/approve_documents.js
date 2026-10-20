document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('.approve-page');
    const modal = document.getElementById('previewModal');
    const title = document.getElementById('previewTitle');
    const body = document.getElementById('previewBody');
    let opener = null;
    let controller = null;

    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm || 'Confirmar esta ação?')) event.preventDefault();
        });
    });

    if (!page || !modal || !title || !body) return;

    const closePreview = () => {
        controller?.abort();
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('has-preview-open');
        opener?.focus();
    };

    const showError = (message) => {
        body.replaceChildren();
        const paragraph = document.createElement('p');
        paragraph.className = 'preview-error';
        paragraph.textContent = message;
        body.appendChild(paragraph);
    };

    const openPreview = async (button) => {
        opener = button;
        controller?.abort();
        controller = new AbortController();
        title.textContent = button.dataset.docName || 'Documento';
        body.innerHTML = '<p class="preview-loading"><i class="fas fa-spinner fa-spin"></i> Carregando documento...</p>';
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('has-preview-open');
        modal.querySelector('[data-preview-close]')?.focus();

        try {
            const url = new URL(page.dataset.previewUrl, window.location.origin);
            url.searchParams.set('id', button.dataset.docId || '');
            const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' }, signal: controller.signal });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Não foi possível carregar o documento.');
            body.textContent = data.content || '[Arquivo sem conteúdo.]';
        } catch (error) {
            if (error.name !== 'AbortError') showError(error.message || 'Erro ao carregar o documento.');
        }
    };

    document.querySelectorAll('.preview-button').forEach((button) => button.addEventListener('click', () => openPreview(button)));
    document.querySelectorAll('[data-preview-close]').forEach((button) => button.addEventListener('click', closePreview));
    modal.addEventListener('mousedown', (event) => { if (event.target === modal) closePreview(); });
    document.addEventListener('keydown', (event) => {
        if (!modal.classList.contains('is-open')) return;
        if (event.key === 'Escape') closePreview();
        if (event.key !== 'Tab') return;
        const focusable = [...modal.querySelectorAll('button:not([disabled])')];
        if (!focusable.length) return;
        const first = focusable[0]; const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    });
});
