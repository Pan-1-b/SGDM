<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main>
    <section class="torneos-page">
        <div class="section-header">
            <div>
                <h1>Mis solicitudes</h1>
                <p>Consulta únicamente tus solicitudes de inscripción individuales y de tus equipos.</p>
            </div>
        </div>

        <?php if (empty($solicitudes)): ?>
            <div class="dashboard-empty">
                <h3>No tienes solicitudes</h3>
                <p>Cuando solicites una inscripción, aparecerá aquí su estado.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Torneo</th>
                            <th>Inscripción</th>
                            <th>Participante</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitudes as $solicitud): ?>
                            <tr>
                                <td>
                                    <a href="<?= BASE_URL ?>/torneos/detalle/<?= (int)$solicitud['idtorneo'] ?>">
                                        <?= htmlspecialchars($solicitud['nombretorneo']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($solicitud['tipo_inscripcion']) ?></td>
                                <td><?= htmlspecialchars($solicitud['participante']) ?></td>
                                <td>
                                    <span class="torneo-estado"><?= htmlspecialchars($solicitud['estado']) ?></span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
