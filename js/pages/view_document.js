document.addEventListener('DOMContentLoaded', () => {
    const copyButton = document.querySelector('[data-copy-content]');
    const backButton = document.querySelector('[data-go-back]');
    const deleteButton = document.querySelector('[data-delete-document]');

    const showCopyFeedback = () => {
        if (!copyButton) {
            return;
        }

        const originalContent = copyButton.innerHTML;
        copyButton.classList.add('is-copied');
        copyButton.innerHTML = '<i class="fas fa-check" aria-hidden="true"></i><span>Texto copiado</span>';

        window.setTimeout(() => {
            copyButton.classList.remove('is-copied');
            copyButton.innerHTML = originalContent;
        }, 2000);
    };

    const copyWithFallback = (text) => {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    };

    const copyToClipboard = async () => {
        const text = document.getElementById('extractedContent')?.innerText;

        if (!text || !copyButton) {
            return;
        }

        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
            } else {
                copyWithFallback(text);
            }

            showCopyFeedback();
        } catch (error) {
            alert('Não foi possível copiar o conteúdo. Tente novamente.');
        }
    };

    const deleteCurrentDocument = async () => {
        if (!deleteButton) {
            return;
        }

        const documentId = deleteButton.dataset.documentId;
        const documentName = deleteButton.dataset.documentName;
        const csrfToken = deleteButton.dataset.csrfToken;
        const deleteUrl = deleteButton.dataset.deleteUrl;
        const redirectUrl = deleteButton.dataset.redirectUrl;
        const message = `Confirma a exclusão permanente do documento "${documentName}", ID ${documentId}?\n\nSomente este documento, seus vínculos e seu arquivo físico serão removidos. Esta ação não pode ser desfeita.`;

        if (!window.confirm(message)) {
            return;
        }

        deleteButton.disabled = true;
        const formData = new FormData();
        formData.append('document_id', String(documentId));
        formData.append('csrf_token', csrfToken);

        try {
            const response = await fetch(deleteUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Não foi possível excluir o documento.');
            }

            alert(data.message);
            window.location.href = redirectUrl;
        } catch (error) {
            alert('Erro ao excluir documento: ' + error.message);
            deleteButton.disabled = false;
        }
    };

    copyButton?.addEventListener('click', copyToClipboard);
    backButton?.addEventListener('click', () => history.back());
    deleteButton?.addEventListener('click', deleteCurrentDocument);

    if (document.querySelector('.processing-message')) {
        window.setTimeout(() => {
            location.reload();
        }, 5000);
    }
});
