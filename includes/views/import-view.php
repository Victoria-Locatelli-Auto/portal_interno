<div
    class="import-page"
    data-submenus-url="<?php echo htmlspecialchars($base_url); ?>/api/get_submenus.php"
    data-max-file-size="<?php echo (int)$import_max_file_size; ?>"
    data-max-file-size-mb="<?php echo (int)$import_max_file_size_mb; ?>"
>
    <header class="import-page-header">
        <div class="import-page-heading">
            <span class="import-page-icon" aria-hidden="true">
                <i class="fas fa-arrow-up-from-bracket"></i>
            </span>

            <div>
                <span class="import-page-kicker">Biblioteca do Portal MCM</span>
                <h1>Importar documentos</h1>
            </div>
        </div>
    </header>

    <?php if ($error): ?>
        <div class="import-alert is-error" role="alert">
            <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="import-alert is-success" role="status">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <section class="import-workspace" aria-labelledby="import-method-title">
        <div class="import-workspace-heading">
            <span class="import-section-kicker">Novo conteúdo</span>
            <h2 id="import-method-title">Escolha como deseja importar</h2>
        </div>

        <div class="import-tabs" role="tablist" aria-label="Tipo de importação">
            <button
                type="button"
                id="pdf-tab-button"
                class="import-tab is-active"
                role="tab"
                aria-selected="true"
                aria-controls="pdf-tab"
                data-tab-target="pdf-tab"
            >
                <i class="far fa-file-pdf" aria-hidden="true"></i>
                Arquivo PDF
            </button>

            <button
                type="button"
                id="text-tab-button"
                class="import-tab"
                role="tab"
                aria-selected="false"
                aria-controls="text-tab"
                data-tab-target="text-tab"
            >
                <i class="far fa-file-lines" aria-hidden="true"></i>
                Conteúdo em texto
            </button>
        </div>

        <div id="pdf-tab" class="import-tab-panel" role="tabpanel" aria-labelledby="pdf-tab-button">
            <form class="import-form" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($import_csrf); ?>">

                <div class="upload-area" id="uploadArea">
                    <span class="upload-icon" aria-hidden="true">
                        <i class="fas fa-cloud-arrow-up"></i>
                    </span>
                    <h3>Arraste o arquivo PDF para esta área</h3>
                    <span class="upload-divider">ou</span>
                    <label for="pdf_file" class="import-button is-primary">
                        <i class="far fa-folder-open" aria-hidden="true"></i>
                        Selecionar arquivo
                    </label>
                    <input type="file" id="pdf_file" name="pdf_file" accept="application/pdf,.pdf" class="file-input">
                    <small>Somente PDF, com tamanho máximo de <?php echo (int)$import_max_file_size_mb; ?> MB.</small>
                    <small class="upload-error is-hidden" data-file-error role="alert"></small>
                </div>

                <div id="fileInfo" class="selected-file is-hidden" aria-live="polite">
                    <div class="selected-file-heading">
                        <span class="selected-file-icon" aria-hidden="true">
                            <i class="far fa-file-pdf"></i>
                        </span>
                        <div>
                            <span>Arquivo selecionado</span>
                            <strong id="fileName">Nenhum arquivo selecionado</strong>
                        </div>
                    </div>

                    <?php if (!empty($menus)): ?>
                        <?php
                        $menu_select_id = 'menu_id';
                        $submenu_select_id = 'submenu_id';
                        $submenu_container_id = 'submenu_container';
                        ?>
                        <?php require __DIR__ . '/partials/import-category-fields.php'; ?>
                    <?php endif; ?>

                    <div class="import-form-actions">
                        <button type="button" class="import-button is-secondary" id="resetFileButton">
                            <i class="fas fa-xmark" aria-hidden="true"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="import-button is-primary">
                            <i class="fas fa-arrow-up-from-bracket" aria-hidden="true"></i>
                            Enviar PDF
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div id="text-tab" class="import-tab-panel" role="tabpanel" aria-labelledby="text-tab-button" hidden>
            <form class="import-form text-form" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($import_csrf); ?>">

                <div class="import-field">
                    <label for="text_title">Título do documento <span aria-hidden="true">*</span></label>
                    <input type="text" id="text_title" name="text_title" placeholder="Digite o título do documento" maxlength="255" required>
                    <small>Máximo de 255 caracteres.</small>
                </div>

                <div class="import-field">
                    <label for="text_content">Conteúdo <span aria-hidden="true">*</span></label>
                    <textarea id="text_content" name="text_content" placeholder="Cole ou digite o conteúdo do documento" rows="12" required></textarea>
                    <small>Caracteres: <span id="char-count">0</span></small>
                </div>

                <?php if (!empty($menus)): ?>
                    <?php
                    $menu_select_id = 'text_menu_id';
                    $submenu_select_id = 'text_submenu_id';
                    $submenu_container_id = 'text_submenu_container';
                    ?>
                    <?php require __DIR__ . '/partials/import-category-fields.php'; ?>
                <?php endif; ?>

                <div class="import-form-actions">
                    <button type="submit" class="import-button is-primary">
                        <i class="far fa-paper-plane" aria-hidden="true"></i>
                        Importar texto
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>
