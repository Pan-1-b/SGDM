<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$autenticado = isset($_SESSION['usuario_id']);

$nombre = $_SESSION['usuario_nombre'] ?? '';
$apellido = $_SESSION['usuario_apellido'] ?? '';
$rol = $_SESSION['usuario_rol'] ?? '';
$solicitudesPendientes = 0;
$invitacionesEquipoPendientes = 0;
$solicitudesEquipoPendientes = 0;

if ($autenticado && $rol === 'organizador') {
    require_once __DIR__ . '/../../models/Torneos.php';
    global $pdo;
    $solicitudesPendientes = (new Torneos($pdo))->contarSolicitudesPendientes($_SESSION['usuario_id']);
}

if ($autenticado && $rol === 'jugador') {
    require_once __DIR__ . '/../../models/Equipos.php';
    global $pdo;
    $invitacionesEquipoPendientes =
        (new Equipos($pdo))->contarInvitacionesPendientes($_SESSION['usuario_id']);
    $solicitudesEquipoPendientes =
        (new Equipos($pdo))->contarSolicitudesPendientesCreador($_SESSION['usuario_id']);
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title><?= $titulo ?? 'HERMES - Gestión Deportiva' ?></title>

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

            <nav class="navbar" aria-label="Navegación principal">

                <div class="navbar-container">

                    <!-- Logo -->

                    <a
                        href="<?= BASE_URL ?>"
                        class="logo"
                    >
                        HERMES
                    </a>

                    <input
                        type="checkbox"
                        id="navbar-toggle"
                        class="navbar-toggle"
                        aria-label="Abrir menú"
                    >

                    <label
                        for="navbar-toggle"
                        class="navbar-toggle-label"
                    >
                        <span></span>
                        <span></span>
                        <span></span>
                        <strong>Menú</strong>
                    </label>


                    <div class="navbar-links">

                        <!-- Inicio -->

                        <a href="<?= BASE_URL ?>/">
                            Inicio
                        </a>


                        <!-- Torneos:
                             disponible para todos -->

                        <a href="<?= BASE_URL ?>/torneos">
                            Torneos
                        </a>


                        <?php if (!$autenticado): ?>

                            <!--
                            ========================================
                            USUARIO NO AUTENTICADO
                            ========================================
                            -->

                            <a href="<?= BASE_URL ?>/login">
                                Iniciar sesión
                            </a>

                            <a
                                href="<?= BASE_URL ?>/registro"
                                class="btn btn-primary"
                            >
                                Crear cuenta
                            </a>


                        <?php else: ?>

                            <!--
                            ========================================
                            USUARIO AUTENTICADO
                            ========================================
                            -->

                            <a href="<?= BASE_URL ?>/dashboard">
                                Dashboard
                            </a>

                            <a href="<?= BASE_URL ?>/perfil">
                                Mi perfil
                            </a>


                            <?php if ($rol === 'jugador'): ?>

                                <!-- Opciones del participante -->

                                <a href="<?= BASE_URL ?>/equipos">
                                    <span class="navbar-link-with-badge">
                                        Mis equipos
                                        <?php if (($invitacionesEquipoPendientes + $solicitudesEquipoPendientes) > 0): ?>
                                            <span class="navbar-badge"><?= $invitacionesEquipoPendientes + $solicitudesEquipoPendientes ?></span>
                                        <?php endif; ?>
                                    </span>
                                </a>

                                <a href="<?= BASE_URL ?>/usuarios/participantes">
                                    Otros jugadores
                                </a>

                                <a href="<?= BASE_URL ?>/mis-solicitudes">
                                    Mis solicitudes
                                </a>

                            <?php endif; ?>


                            <?php if ($rol === 'organizador'): ?>

                                <!-- Opciones del organizador -->

                                <a href="<?= BASE_URL ?>/mis-torneos">
                                    Mis torneos
                                </a>

                                <a href="<?= BASE_URL ?>/torneos/crear">
                                    Crear torneo
                                </a>

                                <a href="<?= BASE_URL ?>/solicitudes">
                                    <span class="navbar-link-with-badge">
                                        Solicitudes
                                        <?php if ($solicitudesPendientes > 0): ?>
                                            <span class="navbar-badge"><?= $solicitudesPendientes ?></span>
                                        <?php endif; ?>
                                    </span>
                                </a>

                            <?php endif; ?>


                            <?php if ($rol === 'administrador'): ?>

                                <!-- Opciones del administrador -->

                                <a href="<?= BASE_URL ?>/administracion">
                                    Administración
                                </a>

                            <?php endif; ?>


                            <!-- Usuario -->

                            <span class="navbar-user">

                                <?= htmlspecialchars($nombre) ?>
                                <?= htmlspecialchars($apellido) ?>

                            </span>


                            <!-- Cerrar sesión -->

                           <a href="<?= BASE_URL ?>/logout" id="logout-link">
                                Cerrar sesión
                           </a>

                        <?php endif; ?>

                    </div>

                </div>

            </nav>

        </header>
