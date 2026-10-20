<main class="admin-page">
    <header class="admin-page-header">
        <div class="admin-heading">
            <span class="admin-heading-icon" aria-hidden="true"><i class="fas fa-users-cog"></i></span>
            <div><span class="admin-kicker">Administração do portal</span><h1>Gerenciar usuários</h1></div>
        </div>
        <a class="admin-monitor-link" href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>/pages/system_monitor.php"><i class="fas fa-heartbeat"></i> Monitor do sistema</a>
    </header>

    <?php if (!empty($feedback['success'])): ?><div class="admin-alert is-success" role="status"><i class="fas fa-check-circle"></i><span><?php echo htmlspecialchars($feedback['success'], ENT_QUOTES, 'UTF-8'); ?></span></div><?php endif; ?>
    <?php if (!empty($feedback['error'])): ?><div class="admin-alert is-error" role="alert"><i class="fas fa-exclamation-circle"></i><span><?php echo htmlspecialchars($feedback['error'], ENT_QUOTES, 'UTF-8'); ?></span></div><?php endif; ?>

    <section class="admin-stats" aria-label="Resumo dos usuários">
        <div class="admin-stat"><strong><?php echo (int)$admin_stats['total']; ?></strong><span>Usuários cadastrados</span></div>
        <div class="admin-stat"><strong><?php echo (int)$admin_stats['active']; ?></strong><span>Usuários ativos</span></div>
        <div class="admin-stat"><strong><?php echo (int)$admin_stats['pending']; ?></strong><span>E-mails pendentes</span></div>
        <div class="admin-stat"><strong><?php echo (int)$admin_stats['importers']; ?></strong><span>Podem importar</span></div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel-heading"><div><h2>Localizar colaborador</h2></div><span>Admin: <?php echo htmlspecialchars($logged_username, ENT_QUOTES, 'UTF-8'); ?></span></div>
        <form method="GET" class="admin-filters">
            <div class="admin-field is-search"><label for="search">Busca</label><div class="admin-search-control"><i class="fas fa-search"></i><input id="search" type="search" name="search" placeholder="Nome, usuário, e-mail ou perfil" value="<?php echo htmlspecialchars($filters['search'], ENT_QUOTES, 'UTF-8'); ?>"></div></div>
            <div class="admin-field"><label for="order">Ordenar por</label><select id="order" name="order"><option value="created_at" <?php echo $filters['order'] === 'created_at' ? 'selected' : ''; ?>>Data de cadastro</option><option value="username" <?php echo $filters['order'] === 'username' ? 'selected' : ''; ?>>Usuário</option><option value="email" <?php echo $filters['order'] === 'email' ? 'selected' : ''; ?>>E-mail</option><option value="access_level" <?php echo $filters['order'] === 'access_level' ? 'selected' : ''; ?>>Perfil</option><option value="is_active" <?php echo $filters['order'] === 'is_active' ? 'selected' : ''; ?>>Status</option></select></div>
            <div class="admin-field"><label for="dir">Direção</label><select id="dir" name="dir"><option value="desc" <?php echo $filters['dir'] === 'DESC' ? 'selected' : ''; ?>>Descendente</option><option value="asc" <?php echo $filters['dir'] === 'ASC' ? 'selected' : ''; ?>>Ascendente</option></select></div>
            <div class="admin-field is-small"><label for="per_page">Por página</label><select id="per_page" name="per_page"><option value="10" <?php echo $filters['per_page'] === 10 ? 'selected' : ''; ?>>10</option><option value="20" <?php echo $filters['per_page'] === 20 ? 'selected' : ''; ?>>20</option><option value="50" <?php echo $filters['per_page'] === 50 ? 'selected' : ''; ?>>50</option></select></div>
            <div class="admin-filter-actions"><button class="admin-button is-primary" type="submit"><i class="fas fa-filter"></i> Filtrar</button><a class="admin-button is-secondary" href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>/admin.php">Limpar</a></div>
        </form>
    </section>

    <section class="admin-panel users-panel">
        <div class="admin-panel-heading"><div><h2>Colaboradores</h2><p>Exibindo <?php echo count($users); ?> de <?php echo (int)$total_users; ?> resultados</p></div><span>Página <?php echo (int)$page; ?> de <?php echo (int)$total_pages; ?></span></div>
        <div class="admin-table-wrap">
            <table class="admin-users-table">
                <thead><tr>
                    <th><a href="?<?php echo htmlspecialchars(adminBuildQuery($filters, ['order' => 'username', 'dir' => adminSortDirection('username', $filters), 'page' => 1]), ENT_QUOTES, 'UTF-8'); ?>">Colaborador <?php if ($filters['order'] === 'username'): ?><i class="fas fa-arrow-<?php echo $filters['dir'] === 'ASC' ? 'up' : 'down'; ?>"></i><?php endif; ?></a></th>
                    <th><a href="?<?php echo htmlspecialchars(adminBuildQuery($filters, ['order' => 'access_level', 'dir' => adminSortDirection('access_level', $filters), 'page' => 1]), ENT_QUOTES, 'UTF-8'); ?>">Perfil</a></th><th>Permissões</th><th>Status</th><th class="admin-actions-heading">Ações</th>
                </tr></thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <?php $display_name = adminUserName($user); $photo_url = adminProfilePhotoUrl($base_url, $user['profile_photo'] ?? null); ?>
                    <tr>
                        <td><div class="admin-person"><span class="admin-avatar"><?php if ($photo_url): ?><img src="<?php echo htmlspecialchars($photo_url, ENT_QUOTES, 'UTF-8'); ?>" alt="Foto de <?php echo htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?>"><?php else: ?><i class="far fa-user"></i><?php endif; ?></span><div><strong><?php echo htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?></strong><a href="mailto:<?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></a><small>@<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?> · cadastro em <?php echo date('d/m/Y', strtotime((string)$user['created_at'])); ?></small></div></div></td>
                        <td><span class="admin-role role-<?php echo htmlspecialchars(strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$user['access_level'])), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($user['access_level'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                        <td><div class="admin-permissions"><span class="permission-tag <?php echo $user['email_verified'] ? 'is-ok' : 'is-warning'; ?>"><i class="fas fa-envelope"></i><?php echo $user['email_verified'] ? 'E-mail verificado' : 'E-mail pendente'; ?></span><span class="permission-tag <?php echo $user['can_import'] ? 'is-ok' : ''; ?>"><i class="fas fa-file-import"></i><?php echo $user['can_import'] ? 'Importação liberada' : 'Sem importação'; ?></span></div></td>
                        <td><span class="admin-status <?php echo $user['is_active'] ? 'is-active' : 'is-inactive'; ?>"><i class="fas fa-circle"></i><?php echo $user['is_active'] ? 'Ativo' : 'Inativo'; ?></span></td>
                        <td><div class="admin-row-actions">
                            <a class="admin-icon-button" href="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>/pages/editar_funcionario.php?id=<?php echo (int)$user['id']; ?>" title="Editar colaborador" aria-label="Editar colaborador"><i class="fas fa-pen"></i></a>
                            <details class="admin-action-menu"><summary title="Alterar perfil"><i class="fas fa-user-shield"></i></summary><div class="admin-action-popover"><strong>Alterar perfil</strong><?php foreach (['admin'=>'Admin','editor'=>'Editor','viewer'=>'Viewer','gestor'=>'Gestor','analista'=>'Analista','coordenador'=>'Coordenador','marketing'=>'Marketing','rh'=>'RH'] as $action_role => $role_label): ?><form method="POST"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="set_<?php echo $action_role; ?>"><input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>"><button type="submit"><?php echo $role_label; ?></button></form><?php endforeach; ?></div></details>
                            <details class="admin-action-menu"><summary title="Mais ações"><i class="fas fa-ellipsis-v"></i></summary><div class="admin-action-popover is-right"><strong>Permissões e acesso</strong><form method="POST"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="<?php echo $user['can_import'] ? 'revoke_import' : 'approve_import'; ?>"><input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>"><button type="submit"><i class="fas fa-file-import"></i><?php echo $user['can_import'] ? 'Revogar importação' : 'Liberar importação'; ?></button></form><form method="POST"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="<?php echo $user['is_active'] ? 'inativar_usuario' : 'ativar_usuario'; ?>"><input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>"><button type="submit" data-confirm="<?php echo $user['is_active'] ? 'Inativar este colaborador?' : 'Ativar este colaborador?'; ?>"><i class="fas fa-power-off"></i><?php echo $user['is_active'] ? 'Inativar usuário' : 'Ativar usuário'; ?></button></form><form method="POST"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($admin_csrf, ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="action" value="resetar_senha"><input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>"><button type="submit" data-confirm="Redefinir a senha de <?php echo htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?> para Mcm@2026?"><i class="fas fa-key"></i>Redefinir senha</button></form></div></details>
                        </div></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?><tr><td colspan="5"><div class="admin-empty"><i class="fas fa-user-slash"></i><strong>Nenhum colaborador encontrado</strong></div></td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php $start_page = max(1, $page - 2); $end_page = min($total_pages, $page + 2); ?>
        <?php if ($total_pages > 1): ?><nav class="admin-pagination" aria-label="Paginação"><a class="page-link <?php echo $page <= 1 ? 'is-disabled' : ''; ?>" href="?<?php echo htmlspecialchars(adminBuildQuery($filters, ['page' => max(1, $page - 1)]), ENT_QUOTES, 'UTF-8'); ?>" aria-label="Página anterior"><i class="fas fa-chevron-left"></i></a><?php for ($i = $start_page; $i <= $end_page; $i++): ?><a class="page-link <?php echo $i === $page ? 'is-current' : ''; ?>" href="?<?php echo htmlspecialchars(adminBuildQuery($filters, ['page' => $i]), ENT_QUOTES, 'UTF-8'); ?>"><?php echo $i; ?></a><?php endfor; ?><a class="page-link <?php echo $page >= $total_pages ? 'is-disabled' : ''; ?>" href="?<?php echo htmlspecialchars(adminBuildQuery($filters, ['page' => min($total_pages, $page + 1)]), ENT_QUOTES, 'UTF-8'); ?>" aria-label="Próxima página"><i class="fas fa-chevron-right"></i></a></nav><?php endif; ?>
    </section>
</main>
