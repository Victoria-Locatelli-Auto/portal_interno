<div class="profile-page">
    <header class="profile-page-header">
        <div class="profile-page-heading">
            <span class="profile-page-icon" aria-hidden="true">
                <i class="far fa-user"></i>
            </span>

            <div>
                <span class="profile-page-kicker">Conta do colaborador</span>
                <h1>Meu perfil</h1>
            </div>
        </div>
    </header>

    <?php if ($profile_photo_feedback): ?>
        <div
            class="profile-alert is-<?php echo $profile_photo_feedback['type'] === 'success' ? 'success' : 'error'; ?>"
            role="status"
        >
            <i
                class="fas <?php echo $profile_photo_feedback['type'] === 'success' ? 'fa-check-circle' : 'fa-circle-exclamation'; ?>"
                aria-hidden="true"
            ></i>
            <?php echo htmlspecialchars($profile_photo_feedback['message']); ?>
        </div>
    <?php endif; ?>

    <div class="profile-layout">
        <section class="profile-summary" aria-labelledby="profile-name">
            <form method="POST" enctype="multipart/form-data" class="profile-photo-form" data-profile-photo-form>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($profile_photo_csrf); ?>">

                <div class="profile-avatar" data-profile-photo-preview>
                    <?php if ($profile_photo_url): ?>
                        <img
                            src="<?php echo htmlspecialchars($profile_photo_url); ?>"
                            alt="Foto de <?php echo htmlspecialchars($full_name); ?>"
                            data-profile-photo-image
                        >
                    <?php endif; ?>

                    <i
                        class="far fa-user <?php echo $profile_photo_url ? 'is-hidden' : ''; ?>"
                        aria-hidden="true"
                        data-profile-photo-fallback
                    ></i>
                </div>

                <input
                    type="file"
                    id="profile_photo"
                    name="profile_photo"
                    class="profile-photo-input"
                    accept="image/jpeg,image/png,image/webp"
                    data-profile-photo-input
                >

                <div class="profile-photo-actions">
                    <label for="profile_photo" class="profile-photo-select">
                        <i class="far fa-image" aria-hidden="true"></i>
                        Escolher foto
                    </label>

                    <button type="submit" name="profile_photo_action" value="upload" class="profile-photo-save">
                        <i class="fas fa-check" aria-hidden="true"></i>
                        Salvar
                    </button>

                    <?php if ($profile_photo_url): ?>
                        <button
                            type="submit"
                            name="profile_photo_action"
                            value="remove"
                            class="profile-photo-remove"
                            data-profile-photo-remove
                        >
                            <i class="far fa-trash-can" aria-hidden="true"></i>
                            Remover
                        </button>
                    <?php endif; ?>
                </div>

                <span class="profile-photo-help" data-profile-photo-name>JPG, PNG ou WebP, até 2 MB.</span>
            </form>

            <h2 id="profile-name"><?php echo htmlspecialchars($full_name); ?></h2>
            <p><?php echo htmlspecialchars($funcao_descricao); ?></p>

            <div class="profile-statuses" aria-label="Status da conta">
                <span class="profile-status <?php echo $user['is_active'] ? 'is-success' : 'is-danger'; ?>">
                    <i class="fas <?php echo $user['is_active'] ? 'fa-check' : 'fa-xmark'; ?>" aria-hidden="true"></i>
                    <?php echo $user['is_active'] ? 'Conta ativa' : 'Conta inativa'; ?>
                </span>

                <span class="profile-status <?php echo $user['email_verified'] ? 'is-success' : 'is-warning'; ?>">
                    <i class="fas <?php echo $user['email_verified'] ? 'fa-check' : 'fa-clock'; ?>" aria-hidden="true"></i>
                    <?php echo $user['email_verified'] ? 'E-mail verificado' : 'E-mail pendente'; ?>
                </span>
            </div>
        </section>

        <section class="profile-details" aria-labelledby="profile-details-title">
            <div class="profile-section-heading">
                <div>
                    <span class="profile-section-kicker">Dados cadastrados</span>
                    <h2 id="profile-details-title">Informações pessoais</h2>
                </div>

                <a
                    href="<?php echo htmlspecialchars($base_url); ?>/pages/editar_funcionario.php?id=<?php echo (int)$user['id']; ?>"
                    class="profile-edit-button"
                >
                    <i class="far fa-pen-to-square" aria-hidden="true"></i>
                    Editar perfil
                </a>
            </div>

            <dl class="profile-information-grid">
                <div class="profile-information-item">
                    <dt><i class="far fa-user" aria-hidden="true"></i> Nome</dt>
                    <dd><?php echo htmlspecialchars($full_name); ?></dd>
                </div>

                <div class="profile-information-item">
                    <dt><i class="far fa-envelope" aria-hidden="true"></i> E-mail</dt>
                    <dd><?php echo htmlspecialchars($user['email'] ?? '-'); ?></dd>
                </div>

                <div class="profile-information-item">
                    <dt><i class="fas fa-briefcase" aria-hidden="true"></i> Função</dt>
                    <dd><?php echo htmlspecialchars($funcao_descricao); ?></dd>
                </div>

                <div class="profile-information-item">
                    <dt><i class="fas fa-building" aria-hidden="true"></i> Filial</dt>
                    <dd><?php echo htmlspecialchars($filial_nome); ?></dd>
                </div>

                <div class="profile-information-item">
                    <dt><i class="fab fa-whatsapp" aria-hidden="true"></i> WhatsApp corporativo</dt>
                    <dd><?php echo htmlspecialchars($whatsapp); ?></dd>
                </div>

                <div class="profile-information-item">
                    <dt><i class="far fa-calendar" aria-hidden="true"></i> Cadastro</dt>
                    <dd><?php echo htmlspecialchars($registered_at); ?></dd>
                </div>
            </dl>
        </section>
    </div>
</div>
