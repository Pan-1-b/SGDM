<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../layouts/header.php';
?>

<main>
    <section class="torneos-page">
        <div class="section-header">
            <div>
                <h1>Editar torneo</h1>
                <p>Actualiza la información del torneo seleccionado.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="<?= BASE_URL ?>/torneos/editar/<?= (int)$torneo['idtorneo'] ?>" method="POST" class="torneo-form js-json-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nombre">Nombre del torneo</label>
                            <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($torneo['nombretorneo']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="tipo">Tipo</label>
                            <select id="tipo" name="tipo" required>
                                <option value="">Selecciona una opción</option>
                                <option value="liga" <?= $torneo['tipo'] === 'liga' ? 'selected' : '' ?>>Liga</option>
                                <option value="eliminacion_directa" <?= $torneo['tipo'] === 'eliminacion_directa' ? 'selected' : '' ?>>Eliminación directa</option>
                                <option value="suizo" <?= $torneo['tipo'] === 'suizo' ? 'selected' : '' ?>>Suizo</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="modalidad">Modalidad</label>
                            <select id="modalidad" name="modalidad" required>
                                <option value="">Selecciona una opción</option>
                                <option value="individual" <?= $torneo['modalidad'] === 'individual' ? 'selected' : '' ?>>Individual</option>
                                <option value="equipos" <?= $torneo['modalidad'] === 'equipos' ? 'selected' : '' ?>>Equipos</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="idcategoria">Categoría</label>
                            <select id="idcategoria" name="idcategoria" required>
                                <option value="">Selecciona una categoría</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= (int)$categoria['idcategoria'] ?>" <?= (int)$torneo['idcategoria'] === (int)$categoria['idcategoria'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($categoria['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="fechainicio">Fecha de inicio</label>
                            <input type="date" id="fechainicio" name="fechainicio" value="<?= htmlspecialchars($torneo['fechainicio']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="fechafin">Fecha de finalización</label>
                            <input type="date" id="fechafin" name="fechafin" value="<?= htmlspecialchars($torneo['fechafin']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="horainicio">Hora de inicio</label>
                            <input type="time" id="horainicio" name="horainicio" value="<?= htmlspecialchars($torneo['horainicio']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="5"><?= htmlspecialchars($torneo['descripcion'] ?? '') ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Actualizar torneo</button>
                        <a href="<?= BASE_URL ?>/mis-torneos" class="btn btn-secondary">Volver</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
