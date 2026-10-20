<div class="edit-employee-page">
    <header class="edit-employee-header">
        <div class="edit-employee-heading">
            <span class="edit-employee-heading-icon" aria-hidden="true">
                <i class="far fa-pen-to-square"></i>
            </span>

            <div>
                <span class="edit-employee-kicker">Gestão de colaboradores</span>
                <h1><?php echo $is_admin ? 'Editar colaborador' : 'Editar meus dados'; ?></h1>
            </div>
        </div>
    </header>

    <?php if (!empty($profile_completion_required)): ?>
        <div class="employee-alert is-error" role="alert">
            <i class="fas fa-circle-info" aria-hidden="true"></i>
            Complete os campos obrigatórios para continuar acessando o Portal MCM.
        </div>
    <?php endif; ?>

    <?php if ($success_msg): ?>
        <div class="employee-alert is-success" role="status">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <?php echo htmlspecialchars($success_msg); ?>
        </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
        <div class="employee-alert is-error" role="alert">
            <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
            <?php echo htmlspecialchars($error_msg); ?>
        </div>
    <?php endif; ?>

    <div class="edit-employee-layout">
        <aside class="employee-summary" aria-labelledby="employee-name">
            <div class="employee-avatar">
                <?php if ($employee_photo_url): ?>
                    <img
                        src="<?php echo htmlspecialchars($employee_photo_url); ?>"
                        alt="Foto de <?php echo htmlspecialchars($full_name); ?>"
                    >
                <?php else: ?>
                    <i class="far fa-user" aria-hidden="true"></i>
                <?php endif; ?>
            </div>

            <h2 id="employee-name"><?php echo htmlspecialchars($full_name); ?></h2>
            <p><?php echo htmlspecialchars($cargo); ?></p>

            <div class="employee-summary-info">
                <span><i class="fas fa-building" aria-hidden="true"></i><?php echo htmlspecialchars($filial_nome); ?></span>
                <span><i class="fab fa-whatsapp" aria-hidden="true"></i><?php echo htmlspecialchars($whatsapp !== '' ? $whatsapp : 'Não informado'); ?></span>
            </div>

            <div class="employee-statuses" aria-label="Status do colaborador">
                <span class="employee-status <?php echo $user['is_active'] ? 'is-success' : 'is-danger'; ?>">
                    <i class="fas <?php echo $user['is_active'] ? 'fa-check' : 'fa-xmark'; ?>" aria-hidden="true"></i>
                    <?php echo $user['is_active'] ? 'Conta ativa' : 'Conta inativa'; ?>
                </span>

                <span class="employee-status <?php echo $user['email_verified'] ? 'is-success' : 'is-warning'; ?>">
                    <i class="fas <?php echo $user['email_verified'] ? 'fa-check' : 'fa-clock'; ?>" aria-hidden="true"></i>
                    <?php echo $user['email_verified'] ? 'E-mail verificado' : 'E-mail pendente'; ?>
                </span>
            </div>
        </aside>

        <section class="employee-form-panel" aria-labelledby="employee-form-title">
            <div class="employee-form-heading">
                <span class="employee-form-kicker">Dados cadastrais</span>
                <h2 id="employee-form-title">Informações do colaborador</h2>
            </div>

            <form method="POST" data-employee-form>
                <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($edit_funcionario_csrf); ?>">

                <div class="employee-form-grid">
                    <div class="employee-field">
                        <label for="first_name">Nome <span aria-hidden="true">*</span></label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>

                    <div class="employee-field">
                        <label for="last_name">Sobrenome <span aria-hidden="true">*</span></label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>

                    <div class="employee-field employee-field--full">
                        <label for="email">E-mail <span aria-hidden="true">*</span></label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php echo htmlspecialchars($user['email']); ?>"
                            <?php echo $is_admin ? '' : 'readonly'; ?>
                            required
                        >
                        <?php if (!$is_admin): ?>
                            <small>O e-mail somente pode ser alterado por um administrador.</small>
                        <?php endif; ?>
                    </div>

                    <div class="employee-field">
                        <label for="wpp_corporativo">WhatsApp corporativo</label>
                        <input
                            type="tel"
                            id="wpp_corporativo"
                            name="wpp_corporativo"
                            value="<?php echo htmlspecialchars($whatsapp); ?>"
                            placeholder="(63) 9 9999-6666"
                            inputmode="numeric"
                            autocomplete="tel"
                            data-whatsapp-input
                        >
                    </div>

                    <div class="employee-field">
                        <label for="id_filial">Filial <span aria-hidden="true">*</span></label>
                        <select id="id_filial" name="id_filial" required>
                            <option value="0">Selecione uma filial</option>
                            <?php foreach ($filiais as $filial): ?>
                                <option value="<?php echo (int)$filial['id']; ?>" <?php echo ((int)$user['id_filial'] === (int)$filial['id']) ? 'selected' : ''; ?>>
                                    <?php
                                    echo htmlspecialchars(
                                        editFuncionarioNormalizeFilialCodigo($filial['codigo_filial'])
                                        . ' - '
                                        . editFuncionarioFormatFilial($filial['nome_filial'])
                                    );
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="employee-field">
                        <label for="id_funcao">Função <span aria-hidden="true">*</span></label>
                        <select id="id_funcao" name="id_funcao" required>
                            <option value="0">Selecione uma função</option>
                            <?php foreach ($funcoes as $funcao): ?>
                                <option value="<?php echo (int)$funcao['id']; ?>" <?php echo ((int)$user['id_funcao'] === (int)$funcao['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(editFuncionarioFormatCargo($funcao['descricao'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="employee-field">
                        <label for="access_level">Nível de acesso <span aria-hidden="true">*</span></label>
                        <select id="access_level" name="access_level" <?php echo $is_admin ? 'required' : 'disabled'; ?>>
                            <?php foreach ($niveis as $nivel): ?>
                                <option value="<?php echo htmlspecialchars($nivel); ?>" <?php echo $user['access_level'] === $nivel ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nivel); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!$is_admin): ?>
                            <small>Somente administradores podem alterar o nível de acesso.</small>
                        <?php endif; ?>
                    </div>

                    <?php if ($is_admin): ?>
                        <fieldset class="employee-status-options employee-field--full">
                            <legend>Status da conta</legend>

                            <label class="employee-check-option">
                                <input type="checkbox" name="is_active" value="1" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                                <span>
                                    <strong>Funcionário ativo</strong>
                                    <small>Permite que o colaborador acesse o portal.</small>
                                </span>
                            </label>

                            <label class="employee-check-option">
                                <input type="checkbox" name="email_verified" value="1" <?php echo $user['email_verified'] ? 'checked' : ''; ?>>
                                <span>
                                    <strong>E-mail verificado</strong>
                                    <small>Confirma que o endereço corporativo foi validado.</small>
                                </span>
                            </label>
                        </fieldset>
                    <?php endif; ?>
                </div>

                <div class="employee-form-actions">
                    <?php if (empty($profile_completion_required)): ?>
                    <a href="<?php echo htmlspecialchars($return_url); ?>" class="employee-button is-secondary">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i>
                        Voltar
                    </a>
                    <?php endif; ?>

                    <button type="submit" class="employee-button is-primary">
                        <i class="far fa-floppy-disk" aria-hidden="true"></i>
                        Salvar alterações
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
