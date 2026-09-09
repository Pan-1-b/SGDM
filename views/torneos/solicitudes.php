<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main>
    <section class="torneos-page">
        <div class="section-header">
            <div>
                <h1>Solicitudes de inscripción</h1>
                <p>Solo se muestran solicitudes de tus propios torneos.</p>
            </div>
        </div>

        <?php if (empty($solicitudes)): ?>
            <div class="dashboard-empty">
                <h3>No hay solicitudes</h3>
                <p>Las solicitudes relacionadas con tus torneos aparecerán aquí.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Torneo</th>
                            <th>Solicitante</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitudes as $solicitud): ?>
                            <tr>
                                <td><?= htmlspecialchars($solicitud['nombretorneo']) ?></td>
                                <td><?= htmlspecialchars($solicitud['solicitante']) ?></td>
                                <td>
                                    <form action="<?= BASE_URL ?>/solicitudes/<?= (int)$solicitud['idinscripcion'] ?>/estado" method="POST" class="inline-form js-json-form">
                                        <select name="estado" aria-label="Estado de la solicitud">
                                            <?php foreach (['pendiente', 'aprobada', 'rechazada', 'cancelada'] as $estado): ?>
                                                <option value="<?= $estado ?>" <?= $solicitud['estado'] === $estado ? 'selected' : '' ?>>
                                                    <?= ucfirst($estado) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-secondary">Actualizar</button>
                                    </form>
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
