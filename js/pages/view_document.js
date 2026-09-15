document.addEventListener('DOMContentLoaded', () => {
    const copyButton = document.querySelector('[data-copy-content]');
    const backButton = document.querySelector('[data-go-back]');
    const deleteButton = document.querySelector('[data-delete-document]');

    const copyToClipboard = () => {
        const text = document.getElementById('extractedContent')?.innerText;

        if (!text) {
            return;
        }

        navigator.clipboard.writeText(text).then(() => {
            alert('Conteúdo copiado para a área de transferência.');
        }).catch(() => {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('Conteúdo copiado para a área de transferência.');
        });
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

    if (document.querySelector('.status-processando, .status-processing')) {
        setTimeout(() => {
            location.reload();
        }, 5000);
    }
});
