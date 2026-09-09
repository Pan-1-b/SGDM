<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main>
    <section class="torneos-page admin-page">
        <div class="section-header">
            <div>
                <span class="meta-label">Panel de control</span>
                <h1>Administración</h1>
                <p>Gestiona usuarios, bloqueos y revisa la actividad del sistema.</p>
            </div>
        </div>

        <div class="admin-grid">
            <section class="panel-card">
                <div class="management-panel-heading">
                    <h2>Usuarios</h2>
                    <span class="management-count"><?= count($usuarios) ?></span>
                </div>
                <form method="GET" class="management-search">
                    <input type="text" name="q" value="<?= htmlspecialchars($filtro) ?>" placeholder="Buscar por nombre o correo">
                    <select name="rol">
                        <option value="">Todos los roles</option>
                        <option value="participante" <?= $rol === 'participante' ? 'selected' : '' ?>>Jugadores</option>
                        <option value="organizador" <?= $rol === 'organizador' ? 'selected' : '' ?>>Organizadores</option>
                    </select>
                    <button class="btn btn-secondary" type="submit"><i class="bi bi-search"></i> Filtrar</button>
                </form>

                <div class="admin-user-list">
                    <?php foreach ($usuarios as $usuario): ?>
                        <article class="admin-user-item">
                            <div>
                                <strong><?= htmlspecialchars($usuario['nombre']) ?></strong>
                                <small><?= htmlspecialchars($usuario['email']) ?> · <?= htmlspecialchars($usuario['rol']) ?></small>
                            </div>
                            <div class="admin-user-actions">
                                <span class="match-status match-status-<?= $usuario['estado'] === 'bloqueado' ? 'cancelado' : 'finalizado' ?>">
                                    <?= htmlspecialchars($usuario['estado']) ?>
                                </span>
                                <form action="<?= BASE_URL ?>/administracion/usuarios/<?= (int)$usuario['idusuario'] ?>/estado" method="POST" class="js-json-form">
                                    <input type="hidden" name="estado" value="<?= $usuario['estado'] === 'bloqueado' ? 'activo' : 'bloqueado' ?>">
                                    <button class="btn <?= $usuario['estado'] === 'bloqueado' ? 'btn-secondary' : 'btn-danger' ?>" type="submit">
                                        <i class="bi bi-<?= $usuario['estado'] === 'bloqueado' ? 'unlock' : 'lock' ?>"></i>
                                        <?= $usuario['estado'] === 'bloqueado' ? 'Desbloquear' : 'Bloquear' ?>
                                    </button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="panel-card">
                <div class="management-panel-heading">
                    <h2>Auditoría</h2>
                    <span class="meta-label">Últimos 300 registros</span>
                </div>
                <div class="audit-list">
                    <?php foreach ($auditorias as $auditoria): ?>
                        <article class="audit-item">
                            <strong><?= htmlspecialchars($auditoria['accion']) ?></strong>
                            <span><?= htmlspecialchars($auditoria['usuario_nombre'] ?? 'Sistema') ?></span>
                            <small><?= htmlspecialchars($auditoria['tabla_afectada'] ?? '') ?> #<?= (int)$auditoria['registro_id'] ?> · <?= date('d/m/Y H:i', strtotime($auditoria['fecha'])) ?></small>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
