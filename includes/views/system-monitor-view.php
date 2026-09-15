<section class="system-monitor-page">
    <header class="monitor-header">
        <h1>Monitor de Banco e Disponibilidade</h1>
        <p>Acesso restrito a administradores. Exibe status atual e últimos eventos de indisponibilidade.</p>
    </header>

    <div class="top-actions">
        <a class="btn btn-secondary" href="<?php echo htmlspecialchars($base_url); ?>/admin.php">
            <i class="fas fa-arrow-left"></i>
            Voltar ao Admin
        </a>
        <a class="btn btn-primary" href="<?php echo htmlspecialchars($base_url); ?>/pages/system_monitor.php">
            <i class="fas fa-rotate-right"></i>
            Atualizar
        </a>
    </div>

    <section class="monitor-grid">
        <article class="monitor-card">
            <h2>PortalMCM (default)</h2>
            <span class="status <?php echo $default_status['ok'] ? 'ok' : 'bad'; ?>">
                <?php echo $default_status['ok'] ? 'Operacional' : 'Com falha'; ?>
            </span>
            <p class="message"><?php echo htmlspecialchars($default_status['message'], ENT_QUOTES, 'UTF-8'); ?></p>
        </article>

        <article class="monitor-card">
            <h2>BaseMCM (auth compartilhada)</h2>
            <span class="status <?php echo $auth_status['ok'] ? 'ok' : 'bad'; ?>">
                <?php echo $auth_status['ok'] ? 'Operacional' : 'Com falha'; ?>
            </span>
            <p class="message"><?php echo htmlspecialchars($auth_status['message'], ENT_QUOTES, 'UTF-8'); ?></p>
        </article>
    </section>

    <section class="logs-panel">
        <h2>Últimos erros PortalMCM</h2>
        <?php if ($portal_errors): ?>
            <pre><?php echo htmlspecialchars(implode("\n", $portal_errors), ENT_QUOTES, 'UTF-8'); ?></pre>
        <?php else: ?>
            <p class="empty">Nenhum erro recente com tag [PortalMCM][DB].</p>
        <?php endif; ?>
    </section>

    <section class="logs-panel">
        <h2>Últimos erros BaseMCM</h2>
        <?php if ($base_errors): ?>
            <pre><?php echo htmlspecialchars(implode("\n", $base_errors), ENT_QUOTES, 'UTF-8'); ?></pre>
        <?php else: ?>
            <p class="empty">Nenhum erro recente com tag [BaseMCM][AUTH].</p>
        <?php endif; ?>
    </section>
</section>
