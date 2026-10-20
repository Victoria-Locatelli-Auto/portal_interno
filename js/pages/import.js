document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('.import-page');
    const uploadArea = document.getElementById('uploadArea');
    const fileInfo = document.getElementById('fileInfo');
    const fileInput = document.getElementById('pdf_file');
    const fileName = document.getElementById('fileName');
    const fileError = document.querySelector('[data-file-error]');
    const resetFileButton = document.getElementById('resetFileButton');
    const textContent = document.getElementById('text_content');
    const charCount = document.getElementById('char-count');
    const tabButtons = [...document.querySelectorAll('.import-tab')];
    const tabPanels = [...document.querySelectorAll('.import-tab-panel')];
    const maxFileSize = Number(page?.dataset.maxFileSize) || 50 * 1024 * 1024;
    const maxFileSizeMb = Number(page?.dataset.maxFileSizeMb) || 50;

    const showFileError = (message) => {
        if (!fileError) {
            return;
        }

        fileError.textContent = message;
        fileError.classList.toggle('is-hidden', message === '');
    };

    const resetFile = () => {
        if (!fileInput || !uploadArea || !fileInfo) {
            return;
        }

        fileInput.value = '';
        uploadArea.classList.remove('is-hidden', 'dragover');
        fileInfo.classList.add('is-hidden');
        showFileError('');
    };

    const handleFileSelect = (files) => {
        const file = files?.[0];

        if (!file || !uploadArea || !fileInfo || !fileName) {
            return;
        }

        const hasPdfExtension = file.name.toLowerCase().endsWith('.pdf');
        const hasPdfType = file.type === '' || file.type === 'application/pdf';

        if (!hasPdfExtension || !hasPdfType) {
            resetFile();
            showFileError('Selecione um arquivo PDF válido.');
            return;
        }

        if (file.size <= 0 || file.size > maxFileSize) {
            resetFile();
            showFileError(`O arquivo deve ter no máximo ${maxFileSizeMb} MB.`);
            return;
        }

        const sizeInMb = (file.size / 1024 / 1024).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });

        fileName.textContent = `${file.name} (${sizeInMb} MB)`;
        uploadArea.classList.add('is-hidden');
        fileInfo.classList.remove('is-hidden');
        showFileError('');
    };

    const activateTab = (activeButton) => {
        tabButtons.forEach((button) => {
            const isActive = button === activeButton;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-selected', String(isActive));
            button.tabIndex = isActive ? 0 : -1;
        });

        tabPanels.forEach((panel) => {
            panel.hidden = panel.id !== activeButton.dataset.tabTarget;
        });
    };

    tabButtons.forEach((button, index) => {
        button.addEventListener('click', () => activateTab(button));
        button.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) {
                return;
            }

            event.preventDefault();
            const direction = event.key === 'ArrowRight' ? 1 : -1;
            const nextIndex = (index + direction + tabButtons.length) % tabButtons.length;
            tabButtons[nextIndex].focus();
            activateTab(tabButtons[nextIndex]);
        });
    });

    if (textContent && charCount) {
        const updateCount = () => {
            charCount.textContent = textContent.value.length.toLocaleString('pt-BR');
        };

        textContent.addEventListener('input', updateCount);
        updateCount();
    }

    fileInput?.addEventListener('change', () => handleFileSelect(fileInput.files));
    resetFileButton?.addEventListener('click', resetFile);

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

            const droppedFiles = event.dataTransfer?.files;
            if (!droppedFiles?.length) {
                return;
            }

            try {
                fileInput.files = droppedFiles;
            } catch (error) {
                showFileError('Use o botão Selecionar arquivo para adicionar este PDF.');
                return;
            }

            handleFileSelect(droppedFiles);
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

        select.innerHTML = '<option value="">Nenhuma subcategoria</option>';

        if (!source.value) {
            container.classList.add('is-hidden');
            return;
        }

        try {
            const url = new URL(page.dataset.submenusUrl, window.location.origin);
            url.searchParams.set('menu_id', source.value);

            const response = await fetch(url.toString(), {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

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
            console.error('Erro ao carregar subcategorias:', error);
        }
    };

    document.querySelectorAll('[data-submenu-source]').forEach((source) => {
        source.addEventListener('change', () => loadSubmenus(source));
    });
});
