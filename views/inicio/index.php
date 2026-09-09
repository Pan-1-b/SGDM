<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main>

    <!-- HERO -->
    <section class="hero">
        <h1>
            Sistema de Gestión Deportiva Modular
        </h1>

        <p>
            Gestiona, organiza y consulta torneos
            deportivos desde una sola plataforma.
        </p>

        <a href="<?= BASE_URL ?>/torneos"
           class="btn btn-primary">
            Explorar torneos
        </a>
    </section>


    <!-- TORNEOS -->
    <section class="torneos">

        <div class="section-header">
            <h2>
                Torneos activos
            </h2>

            <a href="<?= BASE_URL ?>/torneos">
                Ver todos
            </a>
        </div>


        <div id="torneos-container">

            <?php if (empty($torneos)): ?>

                <p>
                    No hay torneos disponibles actualmente.
                </p>

            <?php else: ?>

                <div class="tournaments-grid">

                    <?php foreach ($torneos as $torneo): ?>

                        <article class="torneo-card">
                                <div class="torneo-card-body">
                                    <h3 class="torneo-card-title">
                                        <?= htmlspecialchars($torneo['nombretorneo']) ?>
                                    </h3>

                                    <p class="torneo-card-description">
                                        <?= htmlspecialchars($torneo['descripcion']) ?>
                                    </p>

                                    <div class="torneo-info">
                                        <span><i class="bi bi-diagram-3" aria-hidden="true"></i><strong>Tipo</strong><em><?= htmlspecialchars($torneo['tipo']) ?></em></span>
                                        <span><i class="bi bi-people" aria-hidden="true"></i><strong>Modalidad</strong><em><?= htmlspecialchars($torneo['modalidad']) ?></em></span>
                                        <span><i class="bi bi-person" aria-hidden="true"></i><strong>Organizador</strong><em><?= htmlspecialchars($torneo['organizador']) ?></em></span>
                                        <span><i class="bi bi-clock" aria-hidden="true"></i><strong>Hora de inicio</strong><em><?= htmlspecialchars($torneo['horainicio']) ?></em></span>
                                    </div>

                                    <div class="torneo-card-footer">
                                        <div class="torneo-dates">
                                            <span><i class="bi bi-calendar-event" aria-hidden="true"></i><strong>Inicio</strong><?= date('d/m/Y', strtotime($torneo['fechainicio'])) ?></span>
                                            <span><i class="bi bi-calendar-check" aria-hidden="true"></i><strong>Finalización</strong><?= date('d/m/Y', strtotime($torneo['fechafin'])) ?></span>
                                        </div>
                                        <span class="torneo-estado"><?= htmlspecialchars($torneo['estado']) ?></span>
                                        <a href="<?= BASE_URL ?>/torneos/detalle/<?= (int)$torneo['idtorneo'] ?>" class="btn btn-secondary"><i class="bi bi-arrow-right" aria-hidden="true"></i> Ver detalle</a>
                                    </div>
                                </div>
                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>

    </section>

</main>


<?php require_once __DIR__ . '/../layouts/footer.php'; ?>