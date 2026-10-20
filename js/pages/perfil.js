document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-profile-photo-form]');

    if (!form) {
        return;
    }

    const input = form.querySelector('[data-profile-photo-input]');
    const preview = form.querySelector('[data-profile-photo-preview]');
    const fallback = form.querySelector('[data-profile-photo-fallback]');
    const fileName = form.querySelector('[data-profile-photo-name]');
    const removeButton = form.querySelector('[data-profile-photo-remove]');
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    const maxSize = 2 * 1024 * 1024;
    let previewUrl = null;

    if (input && preview && fileName) {
        input.addEventListener('change', () => {
            const file = input.files && input.files[0];

            if (!file) {
                return;
            }

            if (!allowedTypes.includes(file.type)) {
                input.value = '';
                fileName.textContent = 'Selecione uma imagem JPG, PNG ou WebP.';
                fileName.classList.add('is-error');
                return;
            }

            if (file.size > maxSize) {
                input.value = '';
                fileName.textContent = 'A imagem deve ter no máximo 2 MB.';
                fileName.classList.add('is-error');
                return;
            }

            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }

            previewUrl = URL.createObjectURL(file);
            let image = preview.querySelector('[data-profile-photo-image]');

            if (!image) {
                image = document.createElement('img');
                image.dataset.profilePhotoImage = '';
                image.alt = 'Pré-visualização da nova foto de perfil';
                preview.prepend(image);
            }

            image.src = previewUrl;
            fallback?.classList.add('is-hidden');
            fileName.textContent = file.name;
            fileName.classList.remove('is-error');
        });
    }

    removeButton?.addEventListener('click', (event) => {
        if (!window.confirm('Deseja remover sua foto de perfil?')) {
            event.preventDefault();
        }
    });

    window.addEventListener('beforeunload', () => {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }
    });
});
