<div class="view-document-page">
    <header class="view-document-header">
        <button type="button" class="document-back-link" data-go-back>
            <i class="fas fa-arrow-left" aria-hidden="true"></i>
            Voltar
        </button>

        <div class="view-document-heading">
            <span class="view-document-icon" aria-hidden="true">
                <i class="fas fa-file-lines"></i>
            </span>

            <div>
                <span class="view-document-kicker">Biblioteca do Portal MCM</span>
                <h1>Visualização de documento</h1>
            </div>
        </div>
    </header>

    <section class="content-panel">
        <div class="detail-header">
            <div class="detail-heading">
                <span class="detail-label">Documento</span>
                <h2 class="detail-title"><?php echo htmlspecialchars($formatted_title); ?></h2>

                <div class="detail-meta">
                    <span class="meta-date">
                        <i class="far fa-calendar" aria-hidden="true"></i>
                        Enviado em <?php echo htmlspecialchars(viewDocumentDate($document['uploaded_at'] ?? null)); ?>
                    </span>
                </div>
            </div>

            <?php if ($is_admin && ($document['status'] ?? '') === 'concluido'): ?>
                <div class="detail-actions">
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
                        <i class="far fa-trash-can" aria-hidden="true"></i>
                        Excluir documento
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <?php if (($document['status'] ?? '') === 'pendente'): ?>
            <div class="warning-message" role="status">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
                <span>Documento pendente de aprovação.</span>
            </div>
        <?php endif; ?>

        <?php if ($document_state['is_pdf'] && !$document_state['file_available']): ?>
            <div class="warning-message" role="alert">
                <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                <span>O arquivo PDF original não foi encontrado no servidor. Exibindo o conteúdo processado disponível.</span>
            </div>
        <?php endif; ?>

        <?php if ($document_state['should_show_pdf']): ?>
            <section class="detail-content pdf-viewer-container">
                <div class="content-header">
                    <div>
                        <span class="content-kicker">Arquivo original</span>
                        <h2>
                            <i class="far fa-file-pdf" aria-hidden="true"></i>
                            Visualização do PDF
                        </h2>
                    </div>
                </div>

                <embed
                    src="<?php echo htmlspecialchars($base_url); ?>/api/serve_document.php?id=<?php echo (int)$doc_id; ?>"
                    type="application/pdf"
                    width="100%"
                    height="800"
                    class="pdf-embed"
                    title="<?php echo htmlspecialchars($formatted_title); ?>"
                >
            </section>
        <?php endif; ?>

        <?php if ($document_state['should_show_content']): ?>
            <section class="detail-content">
                <div class="content-header">
                    <div>
                        <span class="content-kicker">Conteúdo processado</span>
                        <h2>
                            <i class="far fa-file-lines" aria-hidden="true"></i>
                            Conteúdo do documento
                        </h2>
                    </div>

                    <button class="btn btn-info" type="button" data-copy-content title="Copiar para a área de transferência">
                        <i class="far fa-copy" aria-hidden="true"></i>
                        <span>Copiar texto</span>
                    </button>
                </div>

                <div class="extracted-text" id="extractedContent">
                    <?php echo nl2br(htmlspecialchars($document['extracted_text'] ?? '')); ?>
                </div>
            </section>

            <?php if (!empty($document['ai_analysis'])): ?>
                <section class="detail-content">
                    <div class="content-header">
                        <div>
                            <span class="content-kicker">Resumo inteligente</span>
                            <h2>
                                <i class="fas fa-brain" aria-hidden="true"></i>
                                Análise da IA
                            </h2>
                        </div>
                    </div>

                    <div class="ai-analysis">
                        <?php echo nl2br(htmlspecialchars($document['ai_analysis'])); ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($document_state['is_processing']): ?>
            <div class="processing-message" role="status">
                <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
                <span>Seu documento está sendo processado. Aguarde alguns instantes.</span>
            </div>
        <?php elseif ($document_state['is_error']): ?>
            <div class="error-message" role="alert">
                <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                <span>Houve um erro ao processar este documento. Tente fazer o upload novamente.</span>
            </div>
        <?php endif; ?>
    </section>
</div>
