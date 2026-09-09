<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main>
    <section class="torneos-page">
        <div class="section-header">
            <div>
                <h1>Mis torneos</h1>
                <p>Administra únicamente los torneos que has creado.</p>
            </div>
            <a href="<?= BASE_URL ?>/torneos/crear" class="btn btn-primary">Crear torneo</a>
        </div>

        <?php if (empty($torneos)): ?>
            <div class="dashboard-empty">
                <h3>Aún no tienes torneos</h3>
                <p>Crea tu primer torneo para comenzar a recibir inscripciones.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($torneos as $torneo): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <article class="card torneo-card h-100">
                            <div class="card-body">
                                <h3 class="card-title"><?= htmlspecialchars($torneo['nombretorneo']) ?></h3>
                                <p class="card-text"><?= htmlspecialchars($torneo['descripcion'] ?? '') ?></p>
                                <div class="torneo-info">
                                    <span><strong>Tipo:</strong> <?= htmlspecialchars($torneo['tipo']) ?></span>
                                    <span><strong>Modalidad:</strong> <?= htmlspecialchars($torneo['modalidad']) ?></span>
                                    <span><strong>Inicio:</strong> <?= date('d/m/Y', strtotime($torneo['fechainicio'])) ?></span>
                                </div>
                                <span class="torneo-estado"><?= htmlspecialchars($torneo['estado']) ?></span>
                                <div class="my-tournament-participants">
                                    <h4><?= $torneo['modalidad'] === 'equipos' ? 'Equipos inscritos' : 'Jugadores inscritos' ?></h4>
                                    <?php if ($torneo['modalidad'] === 'equipos'): ?>
                                        <?php if (empty($torneo['equipos_inscritos'])): ?>
                                            <small>Aún no hay equipos aprobados.</small>
                                        <?php else: ?>
                                            <div class="my-tournament-list">
                                                <?php foreach ($torneo['equipos_inscritos'] as $equipo): ?>
                                                    <div class="my-tournament-team">
                                                        <strong><?= htmlspecialchars($equipo['nombre']) ?></strong>
                                                        <small><?= count($equipo['integrantes']) ?> integrante(s)</small>
                                                        <div class="team-members">
                                                            <?php foreach ($equipo['integrantes'] as $integrante): ?>
                                                                <span class="relationship-status active-status"><?= htmlspecialchars($integrante['nombre']) ?></span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif (empty($torneo['participantes_inscritos'])): ?>
                                        <small>Aún no hay jugadores aprobados.</small>
                                    <?php else: ?>
                                        <div class="my-tournament-list">
                                            <?php foreach ($torneo['participantes_inscritos'] as $participante): ?>
                                                <div class="my-tournament-player">
                                                    <strong><?= htmlspecialchars($participante['nombre']) ?></strong>
                                                    <small><?= htmlspecialchars($participante['email']) ?></small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="torneo-actions">
                                    <a href="<?= BASE_URL ?>/torneos/editar/<?= (int)$torneo['idtorneo'] ?>" class="btn btn-secondary">Editar</a>
                                    <a href="<?= BASE_URL ?>/torneos/<?= (int)$torneo['idtorneo'] ?>/gestion" class="btn btn-primary">Gestionar partidos</a>
                                    <form action="<?= BASE_URL ?>/torneos/estado/<?= (int)$torneo['idtorneo'] ?>" method="POST" class="js-json-form torneo-estado-form">
                                        <label for="estado-<?= (int)$torneo['idtorneo'] ?>" class="sr-only">Estado del torneo</label>
                                        <select id="estado-<?= (int)$torneo['idtorneo'] ?>" name="estado" class="form-select">
                                            <?php foreach (['borrador', 'inscripciones', 'en_curso', 'finalizado', 'cancelado'] as $estado): ?>
                                                <option value="<?= htmlspecialchars($estado) ?>" <?= $torneo['estado'] === $estado ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars(str_replace('_', ' ', ucfirst($estado))) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-primary">Guardar estado</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
