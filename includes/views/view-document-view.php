<header class="page-header">
    <h1 class="page-title">
        <i class="fas fa-file-lines"></i>
        Visualização de Documento
    </h1>
</header>

<section class="content-panel">
    <div class="detail-header">
        <div>
            <h2 class="detail-title"><?php echo htmlspecialchars($formatted_title); ?></h2>
            <div class="detail-meta">
                <span class="badge status-<?php echo htmlspecialchars($document['status'] ?? ''); ?>">
                    <?php echo htmlspecialchars($status_text); ?>
                </span>

                <span class="meta-date">
                    <i class="fas fa-calendar"></i>
                    Enviado: <?php echo htmlspecialchars(viewDocumentDate($document['uploaded_at'] ?? null)); ?>
                </span>

                <?php if (!empty($document['processed_at'])): ?>
                    <span class="meta-date">
                        <i class="fas fa-check-circle"></i>
                        Processado: <?php echo htmlspecialchars(viewDocumentDate($document['processed_at'])); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="detail-actions">
            <?php if ($is_admin && ($document['status'] ?? '') === 'concluido'): ?>
                <button
                    type="button"
                    class="btn btn-danger"
                    title="Excluir este documento"
                    data-delete-document
                    data-document-id="<?php echo (int)$doc_id; ?>"
                    data-document-name="<?php echo htmlspecialchars($document['original_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    data-csrf-token="<?php echo htmlspecialchars($delete_document_csrf, ENT_QUOTES, 'UTF-8'); ?>"
                    data-delete-url="<?php echo htmlspecialchars($base_url); ?>/api/delete_document.php"
                    data-redirect-url="<?php echo htmlspecialchars($base_url); ?>/pages/documentos.php"
                >
                    <i class="fas fa-trash"></i>
                    Excluir documento
                </button>
            <?php endif; ?>

            <button type="button" class="btn btn-secondary" title="Voltar" data-go-back>
                <i class="fas fa-arrow-left"></i>
                Voltar
            </button>
        </div>
    </div>

    <?php if (($document['status'] ?? '') === 'pendente'): ?>
        <div class="warning-message">
            <i class="fas fa-info-circle"></i>
            <span>Documento pendente de aprovação.</span>
        </div>
    <?php endif; ?>

    <?php if ($document_state['is_pdf'] && !$document_state['file_available']): ?>
        <div class="warning-message">
            <i class="fas fa-triangle-exclamation"></i>
            <span>O arquivo PDF original não foi encontrado no servidor. Exibindo o conteúdo processado disponível.</span>
        </div>
    <?php endif; ?>

    <?php if ($document_state['should_show_pdf']): ?>
        <section class="detail-content pdf-viewer-container">
            <div class="content-header">
                <h2>
                    <i class="fas fa-file-pdf"></i>
                    Visualização do PDF
                </h2>
            </div>

            <embed
                src="<?php echo htmlspecialchars($base_url); ?>/api/serve_document.php?id=<?php echo (int)$doc_id; ?>"
                type="application/pdf"
                width="100%"
                height="800"
                class="pdf-embed"
            >
        </section>
    <?php endif; ?>

    <?php if ($document_state['should_show_content']): ?>
        <section class="detail-content">
            <div class="content-header">
                <h2>
                    <i class="fas fa-file-alt"></i>
                    Conteúdo do Documento
                </h2>

                <button class="btn btn-info" type="button" data-copy-content title="Copiar para a área de transferência">
                    <i class="fas fa-copy"></i>
                    Copiar Texto
                </button>
            </div>

            <div class="extracted-text" id="extractedContent">
                <?php echo nl2br(htmlspecialchars($document['extracted_text'] ?? '')); ?>
            </div>
        </section>

        <?php if (!empty($document['ai_analysis'])): ?>
            <section class="detail-content">
                <div class="content-header">
                    <h2>
                        <i class="fas fa-brain"></i>
                        Análise da IA
                    </h2>
                </div>

                <div class="ai-analysis">
                    <?php echo nl2br(htmlspecialchars($document['ai_analysis'])); ?>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($document_state['is_processing']): ?>
        <div class="processing-message">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Seu documento está sendo processado. Aguarde alguns instantes.</span>
        </div>
    <?php elseif ($document_state['is_error']): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Houve um erro ao processar este documento. Tente fazer o upload novamente.</span>
        </div>
    <?php endif; ?>
</section>
