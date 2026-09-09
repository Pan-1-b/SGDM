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

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Iniciar sesión - SGDM</title>

    <link rel="stylesheet"
          href="<?= BASE_URL ?>/css/app.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

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

                    <a href="<?= BASE_URL ?>/registro"
                       class="btn btn-primary">
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
                    Iniciar sesión
                </h1>

                <p class="auth-subtitle">
                    Accede a tu cuenta de HERMES
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


                <form id="login-form" action="<?= BASE_URL ?>/login" method="POST">

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
                                placeholder="Ingresa tu contraseña"
                                required
                                autocomplete="current-password"
                            >
                            <button type="button" class="password-toggle" data-password-toggle="#password" aria-label="Mostrar contraseña" aria-pressed="false">
                                <i class="bi bi-eye-slash eye-icon" aria-hidden="true"></i>
                            </button>
                        </div>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-primary btn-block"
                    >
                        Iniciar sesión
                    </button>

                </form>


                <div class="auth-footer">

                    <span>
                        ¿No tienes una cuenta?
                    </span>

                    <a href="<?= BASE_URL ?>/registro">
                        Registrarse
                    </a>

                </div>

            </div>

        </div>

    </main>

</div>

<script src="<?= BASE_URL ?>/js/app.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.querySelector('#login-form');

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

            const data = await response.json();

            await Swal.fire({
                icon: data.success ? 'success' : 'error',
                title: data.success ? 'Operación exitosa' : 'Ocurrió un error',
                text: data.message
            });

            if (data.success && data.redirect) {
                window.location.href = data.redirect;
            }

        } catch (error) {

            console.error(error);

            await Swal.fire({
                icon: 'error',
                title: 'Ocurrió un error',
                text: 'Ocurrió un error al comunicarse con el servidor.'
            });
        }

    });

});


</script>
