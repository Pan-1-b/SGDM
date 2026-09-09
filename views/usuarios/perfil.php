<?php

require_once __DIR__ . '/../layouts/header.php';

?>

<main>
    <section class="profile-page">
        <div class="section-header">
            <div>
                <span class="eyebrow">Configuración de usuario</span>
                <h1>Mi perfil</h1>
                <p>Actualiza tus datos personales y tu contraseña.</p>
            </div>
        </div>

        <div class="profile-layout">
            <aside class="profile-summary">
                <div class="profile-avatar">
                    <?= strtoupper(substr($usuario['nombre'], 0, 1)) ?>
                </div>
                <h2><?= htmlspecialchars($usuario['nombre']) ?></h2>
                <p><?= htmlspecialchars($usuario['email']) ?></p>
                <span class="profile-role">
                    <?= htmlspecialchars(ucfirst($usuario['rol'])) ?>
                </span>
                <small>El rol es administrado por el sistema y no puede modificarse desde el perfil.</small>
            </aside>

            <div class="card profile-card">
                <div class="card-body">
                    <form action="<?= BASE_URL ?>/perfil" method="POST" class="profile-form js-json-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nombre">Nombre completo</label>
                                <input
                                    type="text"
                                    id="nombre"
                                    name="nombre"
                                    value="<?= htmlspecialchars($usuario['nombre']) ?>"
                                    maxlength="100"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="email">Correo electrónico</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?= htmlspecialchars($usuario['email']) ?>"
                                    maxlength="150"
                                    required
                                >
                            </div>
                        </div>

                        <div class="profile-divider">
                            <h2>Cambiar contraseña</h2>
                            <p>Déjala vacía si deseas conservar la contraseña actual.</p>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="password_actual">Contraseña actual</label>
                                <div class="password-field">
                                    <input type="password" id="password_actual" name="password_actual" autocomplete="current-password">
                                    <button type="button" class="password-toggle" data-password-toggle="#password_actual" aria-label="Mostrar contraseña" aria-pressed="false">
                                        <i class="bi bi-eye-slash eye-icon" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password_nueva">Nueva contraseña</label>
                                <div class="password-field">
                                    <input type="password" id="password_nueva" name="password_nueva" minlength="8" autocomplete="new-password">
                                    <button type="button" class="password-toggle" data-password-toggle="#password_nueva" aria-label="Mostrar contraseña" aria-pressed="false">
                                        <i class="bi bi-eye-slash eye-icon" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmacion">Confirmar nueva contraseña</label>
                            <div class="password-field">
                                <input type="password" id="password_confirmacion" name="password_confirmacion" minlength="8" autocomplete="new-password">
                                <button type="button" class="password-toggle" data-password-toggle="#password_confirmacion" aria-label="Mostrar contraseña" aria-pressed="false">
                                    <i class="bi bi-eye-slash eye-icon" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Guardar cambios</button>
                            <a href="<?= BASE_URL ?>/dashboard" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
