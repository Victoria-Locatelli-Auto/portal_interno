<section class="import-page" data-submenus-url="<?php echo htmlspecialchars($base_url); ?>/api/get_submenus.php">
    <div class="import-panel">
        <header class="import-header">
            <h1>Importar Documentos</h1>
            <p>Envie seus arquivos PDF ou insira texto diretamente para processar com inteligência artificial.</p>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="import-tabs" role="tablist">
            <button type="button" class="tab-button active" data-tab-target="pdf-tab">
                <i class="fas fa-file-pdf"></i>
                Importar PDF
            </button>
            <button type="button" class="tab-button" data-tab-target="text-tab">
                <i class="fas fa-file-alt"></i>
                Importar Texto
            </button>
        </div>

        <div id="pdf-tab" class="tab-content active">
            <form class="import-form" method="POST" enctype="multipart/form-data">
                <div class="upload-area" id="uploadArea">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <h2><i class="fas fa-arrow-up"></i> Arraste seus arquivos aqui</h2>
                    <p>ou</p>
                    <label for="pdf_file" class="btn btn-primary">
                        <i class="fas fa-folder-open"></i>
                        Selecionar Arquivo
                    </label>
                    <input type="file" id="pdf_file" name="pdf_file" accept=".pdf" class="file-input">
                    <p class="upload-info"><i class="fas fa-info-circle"></i> Máximo: 50MB | Formato: PDF</p>
                </div>

                <div id="fileInfo" class="file-info is-hidden">
                    <div class="file-preview">
                        <i class="fas fa-file-pdf"></i>
                        <span id="fileName">Arquivo selecionado</span>
                    </div>

                    <?php if (!empty($menus)): ?>
                        <?php
                            $menu_select_id = 'menu_id';
                            $submenu_select_id = 'submenu_id';
                            $submenu_container_id = 'submenu_container';
                        ?>
                        <?php require __DIR__ . '/partials/import-category-fields.php'; ?>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-upload"></i>
                        Enviar Arquivo
                    </button>
                    <button type="button" class="btn btn-secondary btn-block" id="resetFileButton">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                </div>
            </form>
        </div>

        <div id="text-tab" class="tab-content">
            <form class="import-form text-form" method="POST">
                <div class="form-group">
                    <label for="text_title">
                        <i class="fas fa-heading"></i>
                        Título do Documento
                    </label>
                    <input type="text" id="text_title" name="text_title" placeholder="Digite o título do documento" maxlength="255" required>
                    <small class="form-hint">Máximo: 255 caracteres</small>
                </div>

                <div class="form-group">
                    <label for="text_content">
                        <i class="fas fa-align-left"></i>
                        Conteúdo
                    </label>
                    <textarea id="text_content" name="text_content" placeholder="Cole ou digite o conteúdo do documento aqui..." rows="12" required></textarea>
                    <small class="form-hint">Máximo: 1MB | Caracteres: <span id="char-count">0</span></small>
                </div>

                <?php if (!empty($menus)): ?>
                    <?php
                        $menu_select_id = 'text_menu_id';
                        $submenu_select_id = 'text_submenu_id';
                        $submenu_container_id = 'text_submenu_container';
                    ?>
                    <?php require __DIR__ . '/partials/import-category-fields.php'; ?>
                <?php endif; ?>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-paper-plane"></i>
                        Importar Texto
                    </button>
                </div>
            </form>
        </div>

        <div class="info-box">
            <h2><i class="fas fa-info-circle"></i> Como funciona?</h2>
            <ol>
                <li>Envie um arquivo PDF ou insira um texto diretamente</li>
                <li>Nossa inteligência artificial irá ler e extrair o conteúdo</li>
                <li>Você poderá fazer buscas no conteúdo dos seus documentos</li>
                <li>Obtenha respostas inteligentes baseadas nos seus documentos</li>
            </ol>
        </div>
    </div>
</section>
