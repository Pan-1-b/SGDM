<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main>
    <section class="torneos-page">
        <div class="section-header">
            <div>
                <h1>Otros jugadores</h1>
                <p>Busca jugadores y consulta su información. Si administras un equipo, también podrás invitarlos.</p>
            </div>
        </div>

        <div class="panel-card full-width">
            <div class="panel-header-inline">
                <h2>Filtrar</h2>
                <form method="GET" action="<?= BASE_URL ?>/usuarios/participantes" class="search-form">
                    <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Buscar por nombre o email" />
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </form>
            </div>

            <?php if (empty($participantes)): ?>
                <p>No se encontraron participantes.</p>
            <?php else: ?>
                <?php if (empty($equiposAdministrables)): ?>
                    <div class="status-box warning-box">
                        Puedes consultar otros jugadores, pero necesitas administrar un equipo para enviar invitaciones.
                    </div>
                <?php endif; ?>
                <div class="mini-list">
                    <?php foreach ($participantes as $participante): ?>
                        <?php if ((int)$participante['idusuario'] === (int)$_SESSION['usuario_id']) continue; ?>
                        <?php
                        $relacionesParticipante = [];
                        $puedeInvitar = false;
                        foreach ($relacionesGestionables as $relacion) {
                            if ((int)$relacion['idusuario'] === (int)$participante['idusuario']) {
                                $relacionesParticipante[(int)$relacion['idequipo']] = $relacion;
                            }
                        }
                        ?>
                        <div class="mini-item">
                            <div>
                                <strong><?= htmlspecialchars($participante['nombre']) ?></strong>
                                <small><?= htmlspecialchars($participante['email']) ?></small>
                            </div>
                            <div class="participant-actions">
                                <?php if (empty($equiposAdministrables)): ?>
                                    <span class="relationship-status request-status">Sin equipos administrables para invitar</span>
                                <?php endif; ?>
                                <?php foreach ($equiposAdministrables as $equipo): ?>
                                    <?php $relacion = $relacionesParticipante[(int)$equipo['idequipo']] ?? null; ?>
                                    <?php if ($relacion && $relacion['origen_solicitud'] === 'invitacion' && (int)$relacion['solicitado_por'] === (int)$_SESSION['usuario_id'] && $relacion['estado'] === 'pendiente'): ?>
                                        <div class="relationship-status invitation-status">
                                            <span>Invitación enviada a <?= htmlspecialchars($equipo['nombre']) ?></span>
                                            <form action="<?= BASE_URL ?>/equipos/<?= (int)$equipo['idequipo'] ?>/invitaciones/<?= (int)$participante['idusuario'] ?>/cancelar" method="POST" class="js-json-form">
                                                <button type="submit" class="btn btn-danger">Cancelar invitación</button>
                                            </form>
                                        </div>
                                    <?php elseif ($relacion && $relacion['origen_solicitud'] === 'solicitud' && $relacion['estado'] === 'pendiente'): ?>
                                        <span class="relationship-status request-status">Solicitud recibida para <?= htmlspecialchars($equipo['nombre']) ?></span>
                                    <?php elseif ($relacion && $relacion['estado'] === 'activo'): ?>
                                        <span class="relationship-status active-status">Ya pertenece a <?= htmlspecialchars($equipo['nombre']) ?></span>
                                    <?php else: ?>
                                        <?php $puedeInvitar = true; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                <?php if ($puedeInvitar): ?>
                                    <form action="<?= BASE_URL ?>/usuarios/participantes" method="POST" class="inline-form js-json-form">
                                        <input type="hidden" name="idusuario" value="<?= (int)$participante['idusuario'] ?>">
                                        <select name="idequipo" required>
                                            <option value="">Invitar a…</option>
                                            <?php foreach ($equiposAdministrables as $equipo): ?>
                                                <?php $relacion = $relacionesParticipante[(int)$equipo['idequipo']] ?? null; ?>
                                                <?php if (!$relacion || $relacion['estado'] === 'inactivo'): ?>
                                                    <option value="<?= (int)$equipo['idequipo'] ?>"><?= htmlspecialchars($equipo['nombre']) ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-secondary">Invitar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
