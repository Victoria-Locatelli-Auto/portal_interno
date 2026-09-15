<div class="container">
    <h1 class="page-title">Meu Perfil</h1>

    <div class="profile-grid">
        <section class="card profile-summary">
            <div class="card-header">Perfil</div>
            <div class="card-body">
                <div class="avatar">
                    <i class="fas fa-user"></i>
                </div>

                <div class="name"><?php echo htmlspecialchars($full_name); ?></div>
                <div class="role"><?php echo htmlspecialchars($funcao_descricao); ?></div>

                <div class="badges">
                    <span class="badge <?php echo $user['is_active'] ? 'green' : 'red'; ?>">
                        <?php echo $user['is_active'] ? 'Ativo' : 'Inativo'; ?>
                    </span>

                    <span class="badge <?php echo $user['email_verified'] ? 'green' : 'yellow'; ?>">
                        <?php echo $user['email_verified'] ? 'E-mail verificado' : 'E-mail pendente'; ?>
                    </span>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-header">Informações</div>
            <div class="card-body">
                <div class="info-row">
                    <div class="label">Nome</div>
                    <div><?php echo htmlspecialchars($full_name); ?></div>
                </div>

                <div class="info-row">
                    <div class="label">E-mail</div>
                    <div><?php echo htmlspecialchars($user['email']); ?></div>
                </div>

                <div class="info-row">
                    <div class="label">Função</div>
                    <div><?php echo htmlspecialchars($funcao_descricao); ?></div>
                </div>

                <div class="info-row">
                    <div class="label">Filial</div>
                    <div><?php echo htmlspecialchars($user['nome_filial']); ?></div>
                </div>

                <div class="info-row">
                    <div class="label">WhatsApp</div>
                    <div><?php echo htmlspecialchars($user['wpp_corporativo']); ?></div>
                </div>

                <div class="info-row">
                    <div class="label">Cadastro</div>
                    <div><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></div>
                </div>

                <div class="actions">
                    <a href="<?php echo htmlspecialchars($base_url); ?>/pages/editar_funcionario.php?id=<?php echo (int)$user['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar Funcionário
                    </a>
                </div>
            </div>
        </section>
    </div>
</div>
