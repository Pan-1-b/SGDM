<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../layouts/header.php';
$fechaMinima = (new DateTimeImmutable('today', new DateTimeZone('America/La_Paz')))->format('Y-m-d');
?>

<main>
    <section class="torneos-page">
        <div class="section-header">
            <div>
                <h1>Crear torneo</h1>
                <p>Completa la información para publicar un nuevo torneo.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="<?= BASE_URL ?>/torneos/crear" method="POST" class="torneo-form js-json-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nombre">Nombre del torneo</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>

                        <div class="form-group">
                            <label for="tipo">Tipo</label>
                            <select id="tipo" name="tipo" required>
                                <option value="">Selecciona una opción</option>
                                <option value="liga">Liga</option>
                                <option value="eliminacion_directa">Eliminación directa</option>
                                <option value="suizo">Suizo</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="modalidad">Modalidad</label>
                            <select id="modalidad" name="modalidad" required>
                                <option value="">Selecciona una opción</option>
                                <option value="individual">Individual</option>
                                <option value="equipos">Equipos</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="idcategoria">Categoría</label>
                            <select id="idcategoria" name="idcategoria" required>
                                <option value="">Selecciona una categoría</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= (int)$categoria['idcategoria'] ?>">
                                        <?= htmlspecialchars($categoria['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="fechainicio">Fecha de inicio</label>
                            <input type="date" id="fechainicio" name="fechainicio" min="<?= $fechaMinima ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="fechafin">Fecha de finalización</label>
                            <input type="date" id="fechafin" name="fechafin" min="<?= $fechaMinima ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="horainicio">Hora de inicio</label>
                            <input type="time" id="horainicio" name="horainicio" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="5" placeholder="Describe el torneo, reglas, requisitos y condiciones."></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Guardar torneo</button>
                        <a href="<?= BASE_URL ?>/mis-torneos" class="btn btn-secondary">Volver</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
