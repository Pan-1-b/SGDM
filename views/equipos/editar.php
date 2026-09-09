<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main>
    <section class="auth-container">
        <div class="auth-card">
            <h1>Editar equipo</h1>
            <p class="auth-subtitle">Actualiza la información del equipo.</p>

            <form action="<?= BASE_URL ?>/equipos/<?= (int)$equipo['idequipo'] ?>/editar" method="POST" class="js-json-form">
                <div class="form-group">
                    <label for="nombre">Nombre del equipo</label>
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($equipo['nombre']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="idcategoria">Categoría</label>
                    <select id="idcategoria" name="idcategoria" required>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= (int)$categoria['idcategoria'] ?>" <?= (int)$categoria['idcategoria'] === (int)$equipo['idcategoria'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($categoria['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado" required>
                        <option value="activo" <?= $equipo['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="inactivo" <?= $equipo['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>

                <div class="form-actions">
                    <a href="<?= BASE_URL ?>/equipos/<?= (int)$equipo['idequipo'] ?>" class="btn btn-secondary">Volver</a>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
