document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('.approve-page');
    const modal = document.getElementById('previewModal');
    const title = document.getElementById('previewTitle');
    const body = document.getElementById('previewBody');

    if (!page || !modal || !title || !body) {
        return;
    }

    const loadingMarkup = '<p class="preview-loading"><i class="fas fa-spinner fa-spin"></i> Carregando...</p>';

    const closePreview = () => {
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
    };

    const showPreviewError = (message) => {
        body.innerHTML = '';

        const paragraph = document.createElement('p');
        paragraph.className = 'preview-error';
        paragraph.textContent = message;
        body.appendChild(paragraph);
    };

    const openPreview = async (docId, docName) => {
        title.textContent = `Pré-visualização: ${docName}`;
        body.innerHTML = loadingMarkup;
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');

        try {
            const url = new URL(page.dataset.previewUrl, window.location.origin);
            url.searchParams.set('id', docId);

            const response = await fetch(url.toString(), {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                },
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || `HTTP ${response.status}`);
            }

            body.textContent = data.content || '[Arquivo sem conteúdo]';
        } catch (error) {
            showPreviewError(`Erro ao carregar documento: ${error.message}`);
        }
    };

    document.querySelectorAll('.preview-button').forEach((button) => {
        button.addEventListener('click', () => {
            openPreview(button.dataset.docId, button.dataset.docName || 'Documento');
        });
    });

    document.querySelectorAll('[data-preview-close]').forEach((button) => {
        button.addEventListener('click', closePreview);
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closePreview();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('active')) {
            closePreview();
        }
    });
});
