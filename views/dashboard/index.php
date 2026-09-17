<?php

require_once __DIR__ . '/../layouts/header.php';

$rol = $usuario['rol'] ?? '';
$nombre = $usuario['nombre'] ?? '';
?>

<div class="dashboard-container">

    <!-- Encabezado del dashboard -->
    <div class="dashboard-header">

        <div>
            <h1>Dashboard</h1>

            <p>
                Bienvenido, <?= htmlspecialchars($nombre) ?>.
                Aquí tienes un resumen de tu actividad en HERMES.
            </p>
        </div>

        <div class="dashboard-user">

            <div class="user-avatar">
                <?= strtoupper(substr($nombre, 0, 1)) ?>
            </div>

            <div class="user-info">
                <strong>
                    <?= htmlspecialchars($nombre) ?>
                </strong>

                <span>
                    <?= htmlspecialchars(ucfirst($rol)) ?>
                </span>
            </div>

        </div>

    </div>


    <!-- Estadísticas -->
    <section class="dashboard-stats">

        <div class="stat-card">

            <div class="stat-icon">
                <i class="bi bi-trophy" aria-hidden="true"></i>
            </div>

            <div>
                <span class="stat-label">
                    Torneos
                </span>

                <strong class="stat-value">
                    <?= (int)$estadisticas['torneos'] ?>
                </strong>
            </div>

        </div>
 
 
        <?php if ($rol !== 'organizador'): ?>
        <div class="stat-card">

            <div class="stat-icon">
                <i class="bi bi-people" aria-hidden="true"></i>
            </div>

            <div>
                <span class="stat-label">
                    Equipos
                </span>

                <strong class="stat-value">
                    <?= (int)$estadisticas['equipos'] ?>
                </strong>
            </div>

        </div>
        <?php endif; ?>


        <div class="stat-card">

            <div class="stat-icon">
                <i class="bi bi-clipboard-check" aria-hidden="true"></i>
            </div>

            <div>
                <span class="stat-label">
                    Inscripciones
                </span>

                <strong class="stat-value">
                    <?= (int)$estadisticas['inscripciones_pendientes'] ?>
                </strong>
            </div>

        </div>

    </section>


    <!-- Opciones principales -->
    <section class="dashboard-section">

        <div class="section-title">

            <h2>
                Acceso rápido
            </h2>

            <p>
                Accede a las principales funciones del sistema.
            </p>

        </div>


        <div class="dashboard-cards">

            <!-- Torneos: disponible para todos -->
            <a href="<?= BASE_URL ?>/torneos" class="dashboard-card">

                <div class="dashboard-card-icon">
                    <i class="bi bi-trophy" aria-hidden="true"></i>
                </div>

                <div class="dashboard-card-content">

                    <h3>
                        Torneos
                    </h3>

                    <p>
                        Consulta los torneos disponibles,
                        sus fechas y opciones de inscripción.
                    </p>

                </div>

                <span class="dashboard-card-arrow">
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </span>

            </a>


            <?php if ($rol === 'jugador'): ?>

                <a href="<?= BASE_URL ?>/equipos" class="dashboard-card">

                    <div class="dashboard-card-icon">
                        <i class="bi bi-people" aria-hidden="true"></i>
                    </div>

                    <div class="dashboard-card-content">

                        <h3>
                            Mis equipos
                        </h3>

                        <p>
                            Crea y administra tus equipos
                            deportivos.
                        </p>

                    </div>

                    <span class="dashboard-card-arrow">
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </span>

                </a>


                <a href="<?= BASE_URL ?>/mis-solicitudes" class="dashboard-card">

                    <div class="dashboard-card-icon">
                        <i class="bi bi-clipboard-check" aria-hidden="true"></i>
                    </div>

                    <div class="dashboard-card-content">

                        <h3>
                            Mis inscripciones
                        </h3>

                        <p>
                            Consulta el estado de tus
                            inscripciones en torneos.
                        </p>

                    </div>

                    <span class="dashboard-card-arrow">
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </span>

                </a>


                <a href="<?= BASE_URL ?>/perfil" class="dashboard-card">
                    <div class="dashboard-card-icon">
                        <i class="bi bi-person" aria-hidden="true"></i>
                    </div>

                    <div class="dashboard-card-content">

                        <h3>
                            Mi perfil
                        </h3>

                        <p>
                            Consulta y administra tu
                            información personal.
                        </p>

                    </div>

                    <span class="dashboard-card-arrow">
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </span>

                </a>


            <?php elseif ($rol === 'organizador'): ?>

                <a href="<?= BASE_URL ?>/torneos/crear" class="dashboard-card">

                    <div class="dashboard-card-icon">
                        <i class="bi bi-plus-circle" aria-hidden="true"></i>
                    </div>

                    <div class="dashboard-card-content">

                        <h3>
                            Crear torneo
                        </h3>

                        <p>
                            Crea y configura un nuevo
                            torneo deportivo.
                        </p>

                    </div>

                    <span class="dashboard-card-arrow">
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </span>

                </a>


                <a href="<?= BASE_URL ?>/mis-torneos" class="dashboard-card">

                    <div class="dashboard-card-icon">
                        <i class="bi bi-clipboard-check" aria-hidden="true"></i>
                    </div>

                    <div class="dashboard-card-content">

                        <h3>
                            Mis torneos
                        </h3>

                        <p>
                            Administra los torneos que
                            has creado.
                        </p>

                    </div>

                    <span class="dashboard-card-arrow">
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </span>

                </a>


                <a href="<?= BASE_URL ?>/solicitudes" class="dashboard-card">

                    <div class="dashboard-card-icon">
                        <i class="bi bi-clipboard-check" aria-hidden="true"></i>
                    </div>

                    <div class="dashboard-card-content">

                        <h3>
                            Inscripciones
                        </h3>

                        <p>
                            Revisa, aprueba o rechaza
                            solicitudes de inscripción.
                        </p>

                    </div>

                    <span class="dashboard-card-arrow">
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </span>

                </a>


                <a href="<?= BASE_URL ?>/perfil" class="dashboard-card">

                    <div class="dashboard-card-icon">
                        <i class="bi bi-person" aria-hidden="true"></i>
                    </div>

                    <div class="dashboard-card-content">

                        <h3>
                            Mi perfil
                        </h3>

                        <p>
                            Consulta y administra tu
                            información personal.
                        </p>

                    </div>

                    <span class="dashboard-card-arrow">
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </span>

                </a>


            <?php elseif ($rol === 'administrador'): ?>

                <a href="<?= BASE_URL ?>/administracion" class="dashboard-card">

                    <div class="dashboard-card-icon">
                        <i class="bi bi-people" aria-hidden="true"></i>
                    </div>

                    <div class="dashboard-card-content">

                        <h3>
                            Usuarios
                        </h3>

                        <p>
                            Administra los usuarios
                            registrados en HERMES.
                        </p>

                    </div>

                    <span class="dashboard-card-arrow">
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </span>

                </a>


                <a href="<?= BASE_URL ?>/torneos" class="dashboard-card">

                    <div class="dashboard-card-icon">
                        <i class="bi bi-trophy" aria-hidden="true"></i>
                    </div>

                    <div class="dashboard-card-content">

                        <h3>
                            Gestionar torneos
                        </h3>

                        <p>
                            Supervisa y administra los
                            torneos de la plataforma.
                        </p>

                    </div>

                    <span class="dashboard-card-arrow">
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </span>

                </a>


                <a href="#" class="dashboard-card">

                    <div class="dashboard-card-icon">
                        <i class="bi bi-gear" aria-hidden="true"></i>
                    </div>

                    <div class="dashboard-card-content">

                        <h3>
                            Administración
                        </h3>

                        <p>
                            Gestiona la configuración
                            general de HERMES.
                        </p>

                    </div>

                    <span class="dashboard-card-arrow">
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </span>

                </a>


                <a href="#" class="dashboard-card">

                    <div class="dashboard-card-icon">
                        <i class="bi bi-bar-chart" aria-hidden="true"></i>
                    </div>

                    <div class="dashboard-card-content">

                        <h3>
                            Auditoría
                        </h3>

                        <p>
                            Consulta las acciones
                            registradas en el sistema.
                        </p>

                    </div>

                    <span class="dashboard-card-arrow">
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </span>

                </a>

            <?php endif; ?>

        </div>

    </section>


    <!-- Actividad reciente 
    <section class="dashboard-section">

        <div class="section-title">

            <h2>
                Actividad reciente
            </h2>

            <p>
                Últimas actividades realizadas en HERMES.
            </p>

        </div>


        <div class="dashboard-empty">

            <div class="dashboard-empty-icon">
                📭
            </div>

            <h3>
                No hay actividad reciente
            </h3>

            <p>
                Cuando realices acciones dentro del sistema,
                aparecerán aquí.
            </p>

        </div>

    </section>-->

</div>


<?php

require_once __DIR__ . '/../layouts/footer.php';

?>
