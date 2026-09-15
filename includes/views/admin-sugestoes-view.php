<div class="suggestions-admin" data-api-url="<?php echo htmlspecialchars(adminSuggestionsApiUrl($base_url)); ?>">
    <header class="page-header">
        <h1 class="page-title">
            <i class="fas fa-lightbulb"></i>
            Sugestões do Portal
        </h1>
        <p class="page-subtitle">Acompanhe as ideias enviadas pelos colaboradores.</p>
    </header>

    <?php if (!$can_view_suggestions): ?>
        <section class="suggestions-panel">
            <div class="alert alert-error">
                <i class="fas fa-lock"></i>
                Acesso negado. Esta área está disponível apenas para administradores.
            </div>
        </section>
    <?php else: ?>
        <section class="suggestions-panel">
            <div class="suggestions-toolbar">
                <div class="suggestions-kpi">
                    <span>Total</span>
                    <strong id="suggestions-total">0</strong>
                </div>

                <div class="suggestions-search">
                    <label for="suggestions-filter">Buscar</label>
                    <input
                        type="text"
                        id="suggestions-filter"
                        placeholder="Buscar por usuário ou sugestão"
                        autocomplete="off"
                    >
                </div>
            </div>

            <div class="suggestions-status" id="suggestions-status">Carregando sugestões...</div>

            <div class="suggestions-table-wrap">
                <table class="suggestions-table">
                    <thead>
                        <tr>
                            <th>Usuário</th>
                            <th>Sugestão</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody id="suggestions-table-body"></tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
</div>
