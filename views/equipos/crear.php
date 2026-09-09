<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main>
    <section class="auth-container">
        <div class="auth-card">
            <h1>Crear equipo</h1>
            <p class="auth-subtitle">Registra un nuevo equipo para poder invitar o inscribirlo en torneos.</p>

            <form action="<?= BASE_URL ?>/equipos/crear" method="POST" class="js-json-form">
                <div class="form-group">
                    <label for="nombre">Nombre del equipo</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej. Los Halcones" required>
                </div>

                <div class="form-group">
                    <label for="idcategoria">Categoría</label>
                    <select id="idcategoria" name="idcategoria" required>
                        <option value="">Selecciona una categoría</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= (int)$categoria['idcategoria'] ?>"><?= htmlspecialchars($categoria['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Crear equipo</button>
            </form>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
