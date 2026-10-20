document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('system_logo');
    const preview = document.querySelector('[data-logo-preview]');
    const placeholder = document.querySelector('[data-logo-placeholder]');
    let previewUrl = '';

    input?.addEventListener('change', () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        const file = input.files?.[0];

        if (!file || !preview || !file.type.startsWith('image/')) {
            if (preview) {
                preview.hidden = true;
                preview.removeAttribute('src');
            }
            if (placeholder) placeholder.hidden = false;
            return;
        }

        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl;
        preview.hidden = false;
        if (placeholder) placeholder.hidden = true;
    });

    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm || 'Confirmar esta ação?')) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-system-logo-input]').forEach((logoInput) => {
        logoInput.addEventListener('change', () => {
            if (!logoInput.files?.[0]) return;

            if (window.confirm('Deseja enviar esta logo para o sistema?')) {
                logoInput.form?.requestSubmit();
            } else {
                logoInput.value = '';
            }
        });
    });
});
