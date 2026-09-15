document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('.import-page');
    const uploadArea = document.getElementById('uploadArea');
    const fileInfo = document.getElementById('fileInfo');
    const fileInput = document.getElementById('pdf_file');
    const fileName = document.getElementById('fileName');
    const resetFileButton = document.getElementById('resetFileButton');
    const textContent = document.getElementById('text_content');
    const charCount = document.getElementById('char-count');

    const resetFile = () => {
        if (!fileInput || !uploadArea || !fileInfo) {
            return;
        }

        fileInput.value = '';
        uploadArea.classList.remove('is-hidden');
        fileInfo.classList.add('is-hidden');
    };

    const handleFileSelect = (files) => {
        const file = files?.[0];

        if (!file || !uploadArea || !fileInfo || !fileName) {
            return;
        }

        if (file.type !== 'application/pdf') {
            alert('Por favor, selecione um arquivo PDF válido');
            resetFile();
            return;
        }

        if (file.size > 50 * 1024 * 1024) {
            alert('Arquivo muito grande. Máximo: 50MB');
            resetFile();
            return;
        }

        fileName.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB)`;
        uploadArea.classList.add('is-hidden');
        fileInfo.classList.remove('is-hidden');
    };

    document.querySelectorAll('.tab-button').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.tab-button').forEach((item) => item.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach((content) => content.classList.remove('active'));

            button.classList.add('active');
            document.getElementById(button.dataset.tabTarget)?.classList.add('active');
        });
    });

    if (textContent && charCount) {
        const updateCount = () => {
            charCount.textContent = textContent.value.length.toLocaleString('pt-BR');
        };

        textContent.addEventListener('input', updateCount);
        updateCount();
    }

    if (fileInput) {
        fileInput.addEventListener('change', () => handleFileSelect(fileInput.files));
    }

    if (resetFileButton) {
        resetFileButton.addEventListener('click', resetFile);
    }

    if (uploadArea && fileInput) {
        uploadArea.addEventListener('dragover', (event) => {
            event.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (event) => {
            event.preventDefault();
            uploadArea.classList.remove('dragover');

            if (event.dataTransfer.files.length > 0) {
                fileInput.files = event.dataTransfer.files;
                handleFileSelect(event.dataTransfer.files);
            }
        });
    }

    const loadSubmenus = async (source) => {
        if (!page) {
            return;
        }

        const container = document.getElementById(source.dataset.submenuTarget);
        const select = container?.querySelector('select');

        if (!container || !select) {
            return;
        }

        select.innerHTML = '<option value="">-- Nenhuma subcategoria --</option>';

        if (!source.value) {
            container.classList.add('is-hidden');
            return;
        }

        try {
            const url = new URL(page.dataset.submenusUrl, window.location.origin);
            url.searchParams.set('menu_id', source.value);

            const response = await fetch(url.toString(), {
                headers: {
                    Accept: 'application/json',
                },
            });
            const data = await response.json();
            const submenus = Array.isArray(data.submenus) ? data.submenus : [];

            if (submenus.length === 0) {
                container.classList.add('is-hidden');
                return;
            }

            submenus.forEach((submenu) => {
                const option = document.createElement('option');
                option.value = submenu.id;
                option.textContent = submenu.title;
                select.appendChild(option);
            });

            container.classList.remove('is-hidden');
        } catch (error) {
            container.classList.add('is-hidden');
            console.error('Erro ao carregar submenus:', error);
        }
    };

    document.querySelectorAll('[data-submenu-source]').forEach((source) => {
        source.addEventListener('change', () => loadSubmenus(source));
    });
});
