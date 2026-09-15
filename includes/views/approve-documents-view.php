<div
    class="approve-page"
    data-preview-url="<?php echo htmlspecialchars($base_url); ?>/api/preview_document.php"
>
    <section class="approve-panel">
        <header class="approve-header">
            <div>
                <h1><i class="fas fa-file-circle-check"></i> Aprovar Documentos</h1>
                <p>Gerenciar documentos pendentes de aprovação</p>
            </div>

            <?php if (!empty($pending_documents)): ?>
                <span class="pending-count">
                    <i class="fas fa-hourglass-half"></i>
                    <?php echo count($pending_documents); ?> pendente(s)
                </span>
            <?php endif; ?>
        </header>

        <?php if (!empty($message)): ?>
            <div class="approval-message <?php echo htmlspecialchars($message_type); ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <section class="processing-section">
            <h2><i class="fas fa-cogs"></i> Processamento Automático</h2>

            <div class="processing-info">
                <i class="fas fa-spinner"></i>
                <div>
                    <strong>Apenas documentos PDF passam por processamento automático</strong>
                    <p>Documentos de texto são aprovados diretamente. PDFs aprovados extraem texto automaticamente.</p>
                </div>
            </div>

            <form method="POST" action="<?php echo htmlspecialchars($base_url); ?>/process_now.php">
                <button type="submit" class="btn-process">
                    <i class="fas fa-play"></i>
                    Processar PDFs Agora
                </button>
            </form>

            <div class="processing-status">
                <strong>Status:</strong> PDFs aprovados ficarão em "Processando" até a extração de texto ser finalizada. Textos são aprovados diretamente.
            </div>
        </section>

        <?php if (!empty($pending_documents)): ?>
            <div class="documents-table-wrap">
                <table class="documents-table">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Documento</th>
                            <th>Usuário</th>
                            <th>Status</th>
                            <th>Pré-visualização</th>
                            <th>Data de Envio</th>
                            <?php if (approveDocumentsCanManage($user_level)): ?>
                                <th>Ações</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_documents as $document): ?>
                            <?php
                                $document_title = approveDocumentsFormatTitle((string)($document['original_name'] ?? ''));
                                $is_pdf = approveDocumentsIsPdf($document);
                            ?>
                            <tr>
                                <td>
                                    <span class="document-type <?php echo $is_pdf ? 'pdf' : 'text'; ?>">
                                        <i class="fas fa-<?php echo $is_pdf ? 'file-pdf' : 'file-lines'; ?>"></i>
                                        <?php echo $is_pdf ? 'PDF' : 'Texto'; ?>
                                    </span>
                                </td>

                                <td>
                                    <strong><?php echo htmlspecialchars($document_title); ?></strong>
                                </td>

                                <td>
                                    <div class="user-info-cell">
                                        <strong><?php echo htmlspecialchars($document['username'] ?? '-'); ?></strong>
                                        <span><?php echo htmlspecialchars($document['email'] ?? '-'); ?></span>
                                    </div>
                                </td>

                                <td>
                                    <span class="status-pending">
                                        <i class="fas fa-hourglass-half"></i>
                                        Pendente
                                    </span>
                                </td>

                                <td>
                                    <button
                                        type="button"
                                        class="preview-button"
                                        data-doc-id="<?php echo (int)$document['id']; ?>"
                                        data-doc-name="<?php echo htmlspecialchars($document_title); ?>"
                                    >
                                        <i class="fas fa-eye"></i>
                                        Visualizar
                                    </button>
                                </td>

                                <td><?php echo htmlspecialchars(approveDocumentsFormatDate($document['uploaded_at'] ?? null)); ?></td>

                                <?php if (approveDocumentsCanManage($user_level)): ?>
                                    <td>
                                        <div class="action-buttons">
                                            <form method="POST">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="document_id" value="<?php echo (int)$document['id']; ?>">
                                                <button type="submit" class="btn-approve" title="Aprovar documento">
                                                    <i class="fas fa-check"></i>
                                                    Aprovar
                                                </button>
                                            </form>

                                            <form method="POST">
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="document_id" value="<?php echo (int)$document['id']; ?>">
                                                <button type="submit" class="btn-reject" title="Rejeitar documento">
                                                    <i class="fas fa-times"></i>
                                                    Rejeitar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <h2>Nenhum documento pendente</h2>
                <p>Todos os documentos foram processados.</p>
            </div>
        <?php endif; ?>
    </section>
</div>

<div id="previewModal" class="preview-modal" aria-hidden="true">
    <div class="preview-modal-content" role="dialog" aria-modal="true" aria-labelledby="previewTitle">
        <div class="preview-modal-header">
            <h2 id="previewTitle">Pré-visualização do Documento</h2>
            <button type="button" class="preview-modal-close" data-preview-close aria-label="Fechar pré-visualização">
                &times;
            </button>
        </div>

        <div id="previewBody" class="preview-modal-body">
            <p class="preview-loading">
                <i class="fas fa-spinner fa-spin"></i>
                Carregando...
            </p>
        </div>

        <div class="preview-modal-footer">
            <button type="button" class="btn-modal-close" data-preview-close>Fechar</button>
        </div>
    </div>
</div>
