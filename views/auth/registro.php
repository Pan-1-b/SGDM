<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;

unset($_SESSION['error']);
unset($_SESSION['success']);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Crear cuenta - SGDM</title>

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/css/app.css"
    >
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

<div class="page">

    <header>

        <nav class="navbar">

            <div class="navbar-container">

                <a href="<?= BASE_URL ?>/" class="logo">
                    HERMES
                </a>

                <div class="navbar-links">

                    <a href="<?= BASE_URL ?>/">
                        Inicio
                    </a>

                    <a href="<?= BASE_URL ?>/torneos">
                        Torneos
                    </a>

                    <a href="<?= BASE_URL ?>/login">
                        Iniciar sesión
                    </a>

                    <a
                        href="<?= BASE_URL ?>/registro"
                        class="btn btn-primary"
                    >
                        Crear cuenta
                    </a>

                </div>

            </div>

        </nav>

    </header>


    <main>

        <div class="auth-container">

            <div class="auth-card">

                <h1>
                    Crear cuenta
                </h1>

                <p class="auth-subtitle">
                    Regístrate en HERMES
                </p>


                <?php if ($error): ?>

                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>

                <?php endif; ?>


                <?php if ($success): ?>

                    <div class="alert alert-success">
                        <?= htmlspecialchars($success) ?>
                    </div>

                <?php endif; ?>


              <form
                    id="registro-form"
                    action="<?= BASE_URL ?>/registro"
                    method="POST"
                >

                    <div class="form-group">

                        <label for="nombre">
                            Nombre completo
                        </label>

                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            placeholder="Ingresa tu nombre"
                            required
                            maxlength="100"
                            autocomplete="name"
                        >

                    </div>


                    <div class="form-group">

                        <label for="email">
                            Correo electrónico
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="correo@ejemplo.com"
                            required
                            maxlength="150"
                            autocomplete="email"
                        >

                    </div>


                    <div class="form-group">

                        <label for="password">
                            Contraseña
                        </label>

                        <div class="password-field">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Ingresa una contraseña"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >
                            <button type="button" class="password-toggle" data-password-toggle="#password" aria-label="Mostrar contraseña" aria-pressed="false">
                                <i class="bi bi-eye-slash eye-icon" aria-hidden="true"></i>
                            </button>
                        </div>

                    </div>


                    <div class="form-group">

                        <label for="password_confirmacion">
                            Confirmar contraseña
                        </label>

                        <div class="password-field">
                            <input
                                type="password"
                                id="password_confirmacion"
                                name="password_confirmacion"
                                placeholder="Repite tu contraseña"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >
                            <button type="button" class="password-toggle" data-password-toggle="#password_confirmacion" aria-label="Mostrar contraseña" aria-pressed="false">
                                <i class="bi bi-eye-slash eye-icon" aria-hidden="true"></i>
                            </button>
                        </div>
                        <small>La contraseña debe tener al menos 8 caracteres.</small>

                    </div>


                    <div class="form-group">

                        <label for="rol">
                            Tipo de usuario
                        </label>

                        <select
                            id="rol"
                            name="rol"
                            required
                        >

                            <option value="">
                                Selecciona una opción
                            </option>

                            <option value="jugador">
                                Jugador
                            </option>

                            <option value="organizador">
                                Organizador
                            </option>

                        </select>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-primary btn-block"
                    >
                        Crear cuenta
                    </button>

                </form>


                <div class="auth-footer">

                    <span>
                        ¿Ya tienes una cuenta?
                    </span>

                    <a href="<?= BASE_URL ?>/login">
                        Iniciar sesión
                    </a>

                </div>

            </div>

        </div>

    </main>

</div>

<script src="<?= BASE_URL ?>/js/app.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.querySelector('#registro-form');

    if (!form) {
        return;
    }

    form.addEventListener('submit', async function (e) {

        e.preventDefault();

        const formData = new FormData(form);

        try {

            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });

            const texto = await response.text();

            console.log('Respuesta del servidor:', texto);

            const data = JSON.parse(texto);

            await Swal.fire({
                icon: data.success ? 'success' : 'error',
                title: data.success ? 'Operación exitosa' : 'Ocurrió un error',
                text: data.message
            });

            if (data.success && data.redirect) {
                window.location.href = data.redirect;
            }

        } catch (error) {

            console.error('Error:', error);

            await Swal.fire({
                icon: 'error',
                title: 'Ocurrió un error',
                text: 'Ocurrió un error al comunicarse con el servidor.'
            });

        }

    });

});
</script>



</body>


</html>