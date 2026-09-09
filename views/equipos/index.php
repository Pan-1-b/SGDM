<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main>
    <section class="torneos-page">
        <div class="section-header">
            <div>
                <h1>Equipos</h1>
                <p>Gestiona tus equipos, solicitudes e invitaciones.</p>
            </div>
            <a href="<?= BASE_URL ?>/equipos/crear" class="btn btn-primary">Crear equipo</a>
        </div>

        <div class="panel-grid">
            <div class="panel-card">
                <h2>Mis equipos</h2>
                <?php if (empty($misEquipos)): ?>
                    <p>Aún no perteneces a ningún equipo.</p>
                <?php else: ?>
                    <div class="mini-list">
                        <?php foreach ($misEquipos as $equipo): ?>
                            <div class="mini-item">
                                <div>
                                    <strong><?= htmlspecialchars($equipo['nombre']) ?></strong>
                                    <small><?= htmlspecialchars($equipo['categoria']) ?> · Capitán: <?= htmlspecialchars($equipo['capitan']) ?></small>
                                </div>
                                <div class="response-row">
                                    <a href="<?= BASE_URL ?>/equipos/<?= (int)$equipo['idequipo'] ?>" class="btn btn-secondary">Ver</a>
                                    <?php if ((int)$equipo['idcreador'] === (int)$_SESSION['usuario_id']): ?>
                                        <a href="<?= BASE_URL ?>/equipos/<?= (int)$equipo['idequipo'] ?>/editar" class="btn btn-secondary">Editar</a>
                                    <?php endif; ?>
                                </div>
                                <?php if ((int)$equipo['idcreador'] !== (int)$_SESSION['usuario_id']): ?>
                                    <form action="<?= BASE_URL ?>/equipos/<?= (int)$equipo['idequipo'] ?>/salir" method="POST" class="js-json-form">
                                        <button type="submit" class="btn btn-danger">Salir del equipo</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($solicitudesPorEquipo[$equipo['idequipo']])): ?>
                                <div class="team-requests">
                                    <strong>Solicitudes pendientes</strong>
                                    <?php foreach ($solicitudesPorEquipo[$equipo['idequipo']] as $solicitud): ?>
                                        <div class="mini-item">
                                            <div>
                                                <strong><?= htmlspecialchars($solicitud['nombre']) ?></strong>
                                                <small><?= htmlspecialchars($solicitud['email']) ?></small>
                                            </div>
                                            <div class="response-row">
                                                <form action="<?= BASE_URL ?>/equipos/<?= (int)$equipo['idequipo'] ?>/solicitudes/<?= (int)$solicitud['idusuario'] ?>/responder" method="POST" class="js-json-form">
                                                    <input type="hidden" name="estado" value="activo">
                                                    <button type="submit" class="btn btn-primary">Aceptar</button>
                                                </form>
                                                <form action="<?= BASE_URL ?>/equipos/<?= (int)$equipo['idequipo'] ?>/solicitudes/<?= (int)$solicitud['idusuario'] ?>/responder" method="POST" class="js-json-form">
                                                    <input type="hidden" name="estado" value="inactivo">
                                                    <button type="submit" class="btn btn-danger">Rechazar</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="panel-card">
                <h2>Invitaciones pendientes</h2>
                <?php if (empty($invitaciones)): ?>
                    <p>No tienes invitaciones pendientes.</p>
                <?php else: ?>
                    <div class="mini-list">
                        <?php foreach ($invitaciones as $invitacion): ?>
                            <div class="mini-item">
                                <div>
                                    <strong><?= htmlspecialchars($invitacion['nombre_equipo']) ?></strong>
                                    <small>Invitado por <?= htmlspecialchars($invitacion['invitado_por']) ?></small>
                                </div>
                                <div class="response-row">
                                    <form action="<?= BASE_URL ?>/equipos/<?= (int)$invitacion['idequipo'] ?>/invitacion/responder" method="POST" class="js-json-form">
                                        <input type="hidden" name="estado" value="activo">
                                        <button type="submit" class="btn btn-primary">Aceptar</button>
                                    </form>
                                    <form action="<?= BASE_URL ?>/equipos/<?= (int)$invitacion['idequipo'] ?>/invitacion/responder" method="POST" class="js-json-form">
                                        <input type="hidden" name="estado" value="inactivo">
                                        <button type="submit" class="btn btn-danger">Rechazar</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel-card full-width">
            <div class="panel-header-inline">
                <h2>Buscar equipos</h2>
                <form method="GET" action="<?= BASE_URL ?>/equipos" class="search-form">
                    <input type="text" name="q" value="<?= htmlspecialchars($termino ?? '') ?>" placeholder="Buscar por nombre" />
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </form>
            </div>

            <?php if (empty($equiposDisponibles)): ?>
                <p>No hay equipos disponibles para unirte.</p>
            <?php else: ?>
                <div class="mini-list">
                    <?php foreach ($equiposDisponibles as $equipo): ?>
                        <div class="mini-item">
                            <div>
                                <strong><?= htmlspecialchars($equipo['nombre']) ?></strong>
                                <small><?= htmlspecialchars($equipo['categoria']) ?> · creador: <?= htmlspecialchars($equipo['creador']) ?></small>
                            </div>
                            <?php if (($equipo['integracion_estado'] ?? null) === 'pendiente' && ($equipo['integracion_origen'] ?? null) === 'solicitud'): ?>
                                <div class="response-row">
                                    <span class="status-box warning-box">Solicitud enviada</span>
                                    <form action="<?= BASE_URL ?>/equipos/<?= (int)$equipo['idequipo'] ?>/cancelar-solicitud" method="POST" class="js-json-form">
                                        <button type="submit" class="btn btn-danger">Cancelar solicitud</button>
                                    </form>
                                </div>
                            <?php elseif (($equipo['integracion_estado'] ?? null) === 'activo'): ?>
                                <span class="status-box success-box">Ya perteneces a este equipo</span>
                            <?php elseif (!empty($equipo['conflicto_categoria'])): ?>
                                <span class="status-box warning-box">Ya perteneces a un equipo de la misma categoría</span>
                            <?php else: ?>
                                <form action="<?= BASE_URL ?>/equipos/<?= (int)$equipo['idequipo'] ?>/solicitar" method="POST" class="js-json-form">
                                    <button type="submit" class="btn btn-secondary">Solicitar unión</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
