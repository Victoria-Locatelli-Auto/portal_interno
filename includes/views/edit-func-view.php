<section class="edit-employee-page">
    <div class="page-header">
        <h1 class="page-title">Editar Funcionário</h1>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>

    <div class="edit-employee-grid">
        <aside class="card employee-card">
            <div class="employee-card__header">Perfil</div>
            <div class="employee-card__body employee-profile">
                <div class="employee-profile__avatar">
                    <i class="fas fa-user"></i>
                </div>

                <div class="employee-profile__name"><?php echo htmlspecialchars($full_name); ?></div>
                <div class="employee-profile__role"><?php echo htmlspecialchars($cargo); ?></div>
                

                <div class="employee-profile__badges">
                    <span class="badge <?php echo $user['is_active'] ? 'badge--success' : 'badge--danger'; ?>">
                        <?php echo $user['is_active'] ? 'Ativo' : 'Inativo'; ?>
                    </span>

                    <span class="badge <?php echo $user['email_verified'] ? 'badge--success' : 'badge--warning'; ?>">
                        <?php echo $user['email_verified'] ? 'E-mail verificado' : 'E-mail pendente'; ?>
                    </span>
                </div>
            </div>
        </aside>

        <section class="card employee-card">
            <div class="employee-card__header">
                <?php echo $is_admin ? 'Editar Funcionário' : 'Editar Meus Dados'; ?>
            </div>
            <div class="employee-card__body">
                <form method="POST">
                    <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">

                    <div class="employee-form">
                        <div class="employee-form__group">
                            <label for="first_name">Nome</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>

                        <div class="employee-form__group">
                            <label for="last_name">Sobrenome</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>

                        <div class="employee-form__group employee-form__group--full">
                            <label for="email">E-mail</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?php echo htmlspecialchars($user['email']); ?>"
                                <?php echo $is_admin ? '' : 'readonly'; ?>
                                required
                            >
                        </div>

                        <div class="employee-form__group">
                            <label for="wpp_corporativo">WhatsApp Corporativo</label>
                            <input type="text" id="wpp_corporativo" name="wpp_corporativo" value="<?php echo htmlspecialchars($user['wpp_corporativo']); ?>" required>
                        </div>

                        <div class="employee-form__group">
                            <label for="id_filial">Filial</label>
                            <select id="id_filial" name="id_filial" required>
                                <option value="0">-- Selecione uma filial --</option>
                                <?php foreach ($filiais as $filial): ?>
                                    <option value="<?php echo (int)$filial['id']; ?>" <?php echo ((int)$user['id_filial'] === (int)$filial['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(editFuncionarioFormatFilial($filial['codigo_filial']) . ' - ' . $filial['nome_filial']); ?>
                                    </option>
                                <?php endforeach; ?>
                                
                            </select>
                        </div>

                

                        <div class="employee-form__group">
                            <label for="id_funcao">Função</label>
                            <select id="id_funcao" name="id_funcao" required>
                                <option value="0">-- Selecione --</option>
                                <?php foreach ($funcoes as $funcao): ?>
                                    <option value="<?php echo (int)$funcao['id']; ?>" <?php echo ((int)$user['id_funcao'] === (int)$funcao['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(editFuncionarioFormatCargo($funcao['descricao'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>


                        <div class="employee-form__group">
                            <label for="access_level">Nível de acesso</label>
                            <select id="access_level" name="access_level" <?php echo $is_admin ? '' : 'disabled'; ?>>
                                <?php foreach ($niveis as $nivel): ?>
                                    <option value="<?php echo htmlspecialchars($nivel); ?>" <?php echo $user['access_level'] === $nivel ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($nivel); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <?php if (!$is_admin): ?>
                                <small class="employee-form__hint">Somente administradores podem alterar.</small>
                            <?php endif; ?>
                        </div>

                        <?php if ($is_admin): ?>
                            <div class="employee-form__group employee-form__group--full">
                                <span class="employee-form__label">Status</span>

                                <div class="employee-form__checkboxes">
                                    <label class="employee-form__checkbox">
                                        <input type="checkbox" name="is_active" value="1" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                                        Funcionário ativo
                                    </label>

                                    <label class="employee-form__checkbox">
                                        <input type="checkbox" name="email_verified" value="1" <?php echo $user['email_verified'] ? 'checked' : ''; ?>>
                                        E-mail verificado
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    </div>

                    <div class="employee-form__actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Salvar Alterações
                        </button>

                        <a href="<?php echo $base_url; ?>/pages/perfil.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Voltar
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </div>
</section>
