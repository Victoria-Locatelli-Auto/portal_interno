<section class="smart-search-page">
    <div class="search-container">
        <header class="search-header">
            <h1>
                <i class="fas fa-brain"></i>
                Busca Inteligente em Documentos
            </h1>
            <p class="subtitle">Faça perguntas sobre seus documentos e receba respostas baseadas em IA</p>
        </header>

        <form method="POST" class="search-form">
            <div class="search-input-group">
                <i class="fas fa-search search-icon"></i>
                <input
                    type="text"
                    name="search_query"
                    class="search-input"
                    placeholder="Faça uma pergunta sobre seus documentos..."
                    value="<?php echo htmlspecialchars($search_query); ?>"
                    autocomplete="off"
                >
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i>
                    Buscar
                </button>
            </div>
        </form>

        <?php if ($search_query !== ''): ?>
            <div class="search-results-container">
                <section class="ai-response">
                    <div class="ai-header">
                        <i class="fas fa-robot"></i>
                        <h2>Resposta da IA</h2>
                        <span class="confidence-badge">
                            Confiança: <?php echo searchPageConfidence($ai_answer); ?>%
                        </span>
                    </div>

                    <p class="ai-text">
                        <?php echo nl2br(htmlspecialchars(searchPageCleanResponse($ai_answer))); ?>
                    </p>

                    <?php if (!empty($ai_answer['category'])): ?>
                        <div class="ai-metadata">
                            <small>
                                <i class="fas fa-tag"></i>
                                Categoria: <strong><?php echo htmlspecialchars($ai_answer['category']); ?></strong>
                            </small>
                        </div>
                    <?php endif; ?>
                </section>

                <?php if (!empty($results)): ?>
                    <section class="results-list">
                        <h3>
                            <i class="fas fa-file-alt"></i>
                            Documentos Relacionados (<?php echo count($results); ?>)
                        </h3>

                        <?php foreach ($results as $doc): ?>
                            <?php require __DIR__ . '/partials/search-result-card.php'; ?>
                        <?php endforeach; ?>
                    </section>
                <?php else: ?>
                    <div class="no-documents-found">
                        <p>
                            <i class="fas fa-inbox"></i>
                            Nenhum documento encontrado para esta pergunta.
                        </p>
                    </div>
                <?php endif; ?>

                <section class="ai-rating-section">
                    <p>
                        <i class="fas fa-thumbs-up"></i>
                        <strong>A resposta foi útil?</strong>
                    </p>
                    <div class="rating-buttons">
                        <button type="button" class="rating-btn" data-rating="5" title="Muito útil">
                            <i class="fas fa-star"></i>
                            Muito útil
                        </button>
                        <button type="button" class="rating-btn" data-rating="4" title="Útil">
                            <i class="fas fa-star-half-alt"></i>
                            Útil
                        </button>
                        <button type="button" class="rating-btn" data-rating="3" title="Aceitável">
                            <i class="fas fa-square"></i>
                            Aceitável
                        </button>
                        <button type="button" class="rating-btn" data-rating="2" title="Pouco útil">
                            <i class="fas fa-thumbs-down"></i>
                            Pouco útil
                        </button>
                        <button type="button" class="rating-btn" data-rating="1" title="Não foi útil">
                            <i class="fas fa-times"></i>
                            Inútil
                        </button>
                    </div>
                </section>
            </div>
        <?php else: ?>
            <section class="search-tips">
                <h3>
                    <i class="fas fa-lightbulb"></i>
                    Exemplos de Perguntas
                </h3>
                <ul>
                    <li><strong>Qual é o valor do vale transporte?</strong></li>
                    <li><strong>Como solicitar férias?</strong></li>
                    <li><strong>Quais são os benefícios oferecidos?</strong></li>
                    <li><strong>Como funciona o processo de aprovação?</strong></li>
                    <li><strong>Quando é o pagamento de salário?</strong></li>
                </ul>
            </section>
        <?php endif; ?>
    </div>
</section>
