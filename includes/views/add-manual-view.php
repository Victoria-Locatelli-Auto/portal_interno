<?php
$feedback = array_merge(['success' => '', 'error' => ''], $feedback ?? []);
$form_data = array_merge(['document_title' => '', 'document_content' => ''], $form_data ?? []);
$base_url = $base_url ?? '';
?>

<div class="manual-page">
    <header class="page-header">
        <h1 class="page-title">
            <i class="fas fa-keyboard"></i>
            Adicionar Documento Manual
        </h1>
        <p class="page-subtitle">Insira conteúdo de texto diretamente ou copie de um arquivo Word/Google Docs.</p>
    </header>

    <section class="manual-panel">
        <?php if (!empty($feedback['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($feedback['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($feedback['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($feedback['error']); ?>
            </div>
        <?php endif; ?>

        <div class="info-box">
            <h2><i class="fas fa-lightbulb"></i> Dica</h2>
            <p>Você pode copiar o conteúdo de um arquivo Word (.docx) ou Google Docs e colar no campo abaixo. O documento será salvo como pendente de aprovação.</p>
        </div>

        <form method="POST" class="manual-form">
            <div class="form-group">
                <label for="document_title">Título do Documento <span>*</span></label>
                <input
                    type="text"
                    id="document_title"
                    name="document_title"
                    placeholder="Ex: Política de Benefícios 2024"
                    maxlength="255"
                    required
                    value="<?php echo htmlspecialchars($form_data['document_title']); ?>"
                >
                <div class="char-count">
                    <span id="title_count">0</span>/255 caracteres
                </div>
            </div>

            <div class="form-group">
                <label for="document_content">Conteúdo <span>*</span></label>
                <textarea
                    id="document_content"
                    name="document_content"
                    placeholder="Cole aqui o conteúdo do seu documento..."
                    required
                ><?php echo htmlspecialchars($form_data['document_content']); ?></textarea>
                <div class="char-count">
                    <span id="content_count">0</span> caracteres
                </div>
            </div>

            <div class="form-actions">
                <a href="<?php echo htmlspecialchars($base_url); ?>/pages/documentos.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Salvar Documento
                </button>
            </div>
        </form>
    </section>
</div>
