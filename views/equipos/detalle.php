<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main>
    <section class="torneos-page">
        <div class="section-header">
            <div>
                <h1><?= htmlspecialchars($equipo['nombre']) ?></h1>
                <p>Miembros, solicitudes y gestión del equipo.</p>
            </div>
            <a href="<?= BASE_URL ?>/equipos" class="btn btn-secondary">Volver</a>
        </div>

        <div class="detail-card">
            <div class="detail-meta">
                <div><strong>Categoría:</strong> <?= htmlspecialchars($equipo['categoria']) ?></div>
                <div><strong>Capitán:</strong> <?= htmlspecialchars($equipo['capitan']) ?></div>
                <div><strong>Creador:</strong> <?= htmlspecialchars($equipo['creador']) ?></div>
                <div><strong>Estado:</strong> <span class="torneo-estado"><?= htmlspecialchars($equipo['estado']) ?></span></div>
            </div>

            <?php if ((int)$equipo['idcreador'] === (int)$_SESSION['usuario_id']): ?>
                <div class="panel-card">
                    <h2>Administrar equipo</h2>
                    <div class="response-row">
                        <a href="<?= BASE_URL ?>/equipos/<?= (int)$equipo['idequipo'] ?>/editar" class="btn btn-secondary">Editar equipo</a>
                        <form action="<?= BASE_URL ?>/equipos/<?= (int)$equipo['idequipo'] ?>/capitan" method="POST" class="inline-form js-json-form">
                            <label for="idcapitan">Asignar capitán</label>
                            <select id="idcapitan" name="idcapitan" required>
                                <option value="<?= (int)$equipo['idcreador'] ?>" <?= (int)$equipo['idcapitan'] === (int)$equipo['idcreador'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($equipo['creador']) ?> (creador)
                                </option>
                                <?php foreach ($miembrosActivos as $miembro): ?>
                                    <option value="<?= (int)$miembro['idusuario'] ?>" <?= (int)$equipo['idcapitan'] === (int)$miembro['idusuario'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($miembro['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary">Guardar capitán</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <div class="panel-card">
                <h2>Miembros</h2>
                <?php if (empty($miembros)): ?>
                    <p>No hay miembros todavía.</p>
                <?php else: ?>
                    <div class="mini-list">
                        <?php foreach ($miembros as $miembro): ?>
                            <div class="mini-item">
                                <div>
                                    <strong><?= htmlspecialchars($miembro['nombre']) ?></strong>
                                    <small><?= htmlspecialchars($miembro['estado']) ?></small>
                                </div>
                                <?php if ($puedeAdministrar && (int)$miembro['idusuario'] !== (int)$equipo['idcreador']): ?>
                                    <form action="<?= BASE_URL ?>/equipos/<?= (int)$equipo['idequipo'] ?>/miembros/<?= (int)$miembro['idusuario'] ?>/eliminar" method="POST" class="js-json-form">
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($puedeAdministrar): ?>
            <div class="panel-card">
                <h2>Solicitudes pendientes</h2>
                <?php if (empty($solicitudes)): ?>
                    <p>No hay solicitudes pendientes.</p>
                <?php else: ?>
                    <div class="mini-list">
                        <?php foreach ($solicitudes as $solicitud): ?>
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
            </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
