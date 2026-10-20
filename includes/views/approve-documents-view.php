<main class="approve-page" data-preview-url="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>/api/preview_document.php">
    <header class="approve-page-header">
        <div class="approve-heading">
            <span class="approve-heading-icon" aria-hidden="true"><i class="fas fa-file-circle-check"></i></span>
            <div><span class="approve-kicker">Biblioteca do Portal MCM</span><h1>Aprovar documentos</h1></div>
        </div>
        <?php if ($document_stats['total'] > 0): ?><span class="approve-pending-count"><i class="far fa-clock"></i><?php echo (int)$document_stats['total']; ?> pendentes</span><?php endif; ?>
    </header>

    <?php if ($message !== ''): ?>
        <div class="approve-alert is-<?php echo $message_type === 'success' ? 'success' : 'error'; ?>" role="<?php echo $message_type === 'success' ? 'status' : 'alert'; ?>">
            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <span><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    <?php endif; ?>

    <section class="approve-stats" aria-label="Resumo dos documentos pendentes">
        <div class="approve-stat"><strong><?php echo (int)$document_stats['total']; ?></strong><span>Total pendente</span></div>
        <div class="approve-stat"><strong><?php echo (int)$document_stats['pdf']; ?></strong><span>Arquivos PDF</span></div>
        <div class="approve-stat"><strong><?php echo (int)$document_stats['text']; ?></strong><span>Documentos de texto</span></div>
    </section>

    <?php if (approveDocumentsCanManage($user_level)): ?>
        <section class="approve-processing-panel">
            <div class="processing-copy"><span class="processing-icon"><i class="fas fa-gears"></i></span><div><h2>Processamento de PDFs</h2><p>Depois da aprovação, os arquivos PDF aguardam a extração automática do texto. Documentos de texto não precisam desta etapa.</p></div></div>
            <form method="POST" action="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>/process_now.php" data-confirm="Iniciar agora o processamento dos PDFs aprovados?">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($approve_documents_csrf, ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="approve-button is-primary"><i class="fas fa-play"></i> Processar PDFs agora</button>
            </form>
        </section>
    <?php else: ?>
        <div class="approve-alert is-info"><i class="fas fa-eye"></i><span>Seu perfil possui acesso para consulta. Aprovações, rejeições e processamento são realizados por administradores.</span></div>
    <?php endif; ?>

    <section class="approve-panel">
        <div class="approve-panel-heading"><div><h2>Fila de aprovação</h2><p>Documentos enviados e ainda não analisados.</p></div></div>

        <?php if (!empty($pending_documents)): ?>
            <div class="approve-table-wrap">
                <table class="approve-table">
                    <thead><tr><th>Documento</th><th>Enviado por</th><th>Data de envio</th><th>Status</th><th class="preview-column">Prévia</th><?php if (approveDocumentsCanManage($user_level)): ?><th class="actions-column">Ações</th><?php endif; ?></tr></thead>
                    <tbody>
                    <?php foreach ($pending_documents as $document): ?>
                        <?php $document_title = approveDocumentsFormatTitle((string)($document['original_name'] ?? '')); $is_pdf = approveDocumentsIsPdf($document); ?>
                        <tr>
                            <td><div class="document-identity"><span class="document-icon is-<?php echo $is_pdf ? 'pdf' : 'text'; ?>"><i class="far fa-file-<?php echo $is_pdf ? 'pdf' : 'lines'; ?>"></i></span><div><strong><?php echo htmlspecialchars($document_title, ENT_QUOTES, 'UTF-8'); ?></strong><small><?php echo $is_pdf ? 'Arquivo PDF' : 'Documento de texto'; ?></small></div></div></td>
                            <td><div class="sender-info"><strong><?php echo htmlspecialchars($document['username'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong><span><?php echo htmlspecialchars($document['email'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></span></div></td>
                            <td><span class="upload-date"><i class="far fa-calendar"></i><?php echo htmlspecialchars(approveDocumentsFormatDate($document['uploaded_at'] ?? null), ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <td><span class="pending-status"><i class="fas fa-circle"></i>Pendente</span></td>
                            <td><button type="button" class="preview-button" data-doc-id="<?php echo (int)$document['id']; ?>" data-doc-name="<?php echo htmlspecialchars($document_title, ENT_QUOTES, 'UTF-8'); ?>"><i class="far fa-eye"></i> Visualizar</button></td>
                            <?php if (approveDocumentsCanManage($user_level)): ?><td><div class="document-actions"><form method="POST" data-confirm="Aprovar este documento?"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($approve_documents_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="approve"><input type="hidden" name="document_id" value="<?php echo (int)$document['id']; ?>"><button type="submit" class="action-button is-approve" title="Aprovar documento" aria-label="Aprovar documento"><i class="fas fa-check"></i></button></form><form method="POST" data-confirm="Rejeitar este documento? Essa ação o removerá da fila."><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($approve_documents_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="reject"><input type="hidden" name="document_id" value="<?php echo (int)$document['id']; ?>"><button type="submit" class="action-button is-reject" title="Rejeitar documento" aria-label="Rejeitar documento"><i class="fas fa-times"></i></button></form></div></td><?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="approve-empty"><span><i class="fas fa-check"></i></span><h2>Nenhum documento pendente</h2><p>A fila de aprovação está em dia.</p></div>
        <?php endif; ?>
    </section>
</main>

<div id="previewModal" class="preview-modal" aria-hidden="true">
    <div class="preview-dialog" role="dialog" aria-modal="true" aria-labelledby="previewTitle">
        <header class="preview-header"><div><span>Pré-visualização</span><h2 id="previewTitle">Documento</h2></div><button type="button" class="preview-close" data-preview-close aria-label="Fechar"><i class="fas fa-times"></i></button></header>
        <div id="previewBody" class="preview-body"><p class="preview-loading"><i class="fas fa-spinner fa-spin"></i> Carregando documento...</p></div>
        <footer class="preview-footer"><button type="button" class="approve-button is-secondary" data-preview-close>Fechar</button></footer>
    </div>
</div>
