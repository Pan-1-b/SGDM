<?php
require_once __DIR__ . '/../layouts/header.php';

$rondaSeleccionada = (int)($_GET['ronda'] ?? ($rondas[0]['idronda'] ?? 0));
$partidosDeRonda = array_values(array_filter($partidos, static function ($partido) use ($rondaSeleccionada) {
    return (int)$partido['idronda'] === $rondaSeleccionada;
}));
$rondaActual = null;
foreach ($rondas as $ronda) {
    if ((int)$ronda['idronda'] === $rondaSeleccionada) {
        $rondaActual = $ronda;
        break;
    }
}
?>

<main>
    <section class="torneos-page">
        <div class="section-header management-page-header">
            <div>
                <span class="meta-label">Panel del organizador</span>
                <h1>Gestionar torneo: <?= htmlspecialchars($torneo['nombretorneo']) ?></h1>
                <p>Administra rondas, partidos y resultados de este torneo.</p>
            </div>
            <a href="<?= BASE_URL ?>/mis-torneos" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>

        <div class="tournament-management-layout">
            <aside class="rounds-sidebar panel-card">
                <div class="management-panel-heading">
                    <h2>Rondas</h2>
                    <span class="management-count"><?= count($rondas) ?></span>
                </div>

                <form method="GET" class="management-search">
                    <input type="text" name="q" value="<?= htmlspecialchars($filtro) ?>" placeholder="Buscar rondas o partidos">
                    <button class="btn btn-secondary" type="submit" aria-label="Filtrar">
                        <i class="bi bi-search"></i>
                    </button>
                </form>

                <form action="<?= BASE_URL ?>/torneos/<?= (int)$torneo['idtorneo'] ?>/gestion/rondas" method="POST" class="management-form js-json-form">
                    <div class="form-grid">
                        <input type="number" name="numero" min="1" placeholder="Número" required>
                        <input type="text" name="nombre" placeholder="Nombre de la ronda">
                        <input type="datetime-local" name="fechainicio">
                        <input type="datetime-local" name="fechafin">
                        <input type="hidden" name="estado" value="pendiente">
                    </div>
                    <button class="btn btn-primary btn-block" type="submit">
                        <i class="bi bi-plus-lg"></i> Crear ronda
                    </button>
                </form>

                <div class="round-list">
                    <?php foreach ($rondas as $ronda): ?>
                        <a class="round-selector <?= (int)$ronda['idronda'] === $rondaSeleccionada ? 'is-selected' : '' ?>"
                           href="?ronda=<?= (int)$ronda['idronda'] ?>&q=<?= urlencode($filtro) ?>">
                            <span class="round-number"><?= (int)$ronda['numero'] ?></span>
                            <span class="round-info">
                                <strong>Ronda <?= (int)$ronda['numero'] ?><?= $ronda['nombre'] ? ' | ' . htmlspecialchars($ronda['nombre']) : '' ?></strong>
                                <small><i class="bi bi-calendar3"></i> <?= $ronda['fechainicio'] ? date('d/m/Y', strtotime($ronda['fechainicio'])) : 'Sin fecha' ?></small>
                            </span>
                            <span class="round-state round-state-<?= htmlspecialchars($ronda['estado']) ?>">
                                <?= ucfirst(str_replace('_', ' ', $ronda['estado'])) ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </aside>

            <section class="round-management-content">
                <?php if ($rondaActual): ?>
                    <section class="panel-card round-editor-card">
                        <div class="management-panel-heading">
                            <div>
                                <span class="meta-label">Gestión de ronda</span>
                                <h2>Ronda <?= (int)$rondaActual['numero'] ?><?= $rondaActual['nombre'] ? ': ' . htmlspecialchars($rondaActual['nombre']) : '' ?></h2>
                            </div>
                            <span class="round-state round-state-<?= htmlspecialchars($rondaActual['estado']) ?>">
                                <?= ucfirst(str_replace('_', ' ', $rondaActual['estado'])) ?>
                            </span>
                        </div>
                        <form action="<?= BASE_URL ?>/torneos/<?= (int)$torneo['idtorneo'] ?>/gestion/rondas/<?= (int)$rondaActual['idronda'] ?>" method="POST" class="round-edit-form js-json-form">
                            <div class="form-grid">
                                <input type="number" name="numero" min="1" value="<?= (int)$rondaActual['numero'] ?>" required>
                                <input type="text" name="nombre" value="<?= htmlspecialchars($rondaActual['nombre'] ?? '') ?>" placeholder="Nombre de la ronda">
                                <input type="datetime-local" name="fechainicio" value="<?= $rondaActual['fechainicio'] ? date('Y-m-d\TH:i', strtotime($rondaActual['fechainicio'])) : '' ?>">
                                <input type="datetime-local" name="fechafin" value="<?= $rondaActual['fechafin'] ? date('Y-m-d\TH:i', strtotime($rondaActual['fechafin'])) : '' ?>">
                                <input type="hidden" name="estado" value="<?= htmlspecialchars($rondaActual['estado']) ?>">
                            </div>
                            <div class="form-actions">
                                <button class="btn btn-secondary" type="submit"><i class="bi bi-save"></i> Guardar cambios</button>
                            </div>
                        </form>
                        <form action="<?= BASE_URL ?>/torneos/<?= (int)$torneo['idtorneo'] ?>/gestion/rondas/<?= (int)$rondaActual['idronda'] ?>/eliminar" method="POST" class="inline-form js-json-form">
                            <button class="btn btn-danger" type="submit"><i class="bi bi-trash"></i> Eliminar ronda</button>
                        </form>
                    </section>

                    <section class="panel-card create-match-card">
                        <div class="management-panel-heading">
                            <h2>Partidos de la ronda</h2>
                            <span class="management-count"><?= count($partidosDeRonda) ?></span>
                        </div>
                        <?php if (count($inscripciones) < 2): ?>
                            <p>Necesitas al menos dos inscripciones aprobadas para crear un partido.</p>
                        <?php else: ?>
                            <form action="<?= BASE_URL ?>/torneos/<?= (int)$torneo['idtorneo'] ?>/gestion/partidos" method="POST" class="management-form js-json-form">
                                <input type="hidden" name="idronda" value="<?= (int)$rondaActual['idronda'] ?>">
                                <div class="form-grid">
                                    <select name="idinscripcion_local" required>
                                        <option value="">Participante local</option>
                                        <?php foreach ($inscripciones as $inscripcion): ?>
                                            <option value="<?= (int)$inscripcion['idinscripcion'] ?>"><?= htmlspecialchars($inscripcion['nombre']) ?> (<?= $inscripcion['tipo'] ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select name="idinscripcion_visitante" required>
                                        <option value="">Participante visitante</option>
                                        <?php foreach ($inscripciones as $inscripcion): ?>
                                            <option value="<?= (int)$inscripcion['idinscripcion'] ?>"><?= htmlspecialchars($inscripcion['nombre']) ?> (<?= $inscripcion['tipo'] ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="datetime-local" name="fecha" required>
                                    <input type="hidden" name="estado" value="programado">
                                </div>
                                <button class="btn btn-primary btn-block" type="submit"><i class="bi bi-plus-lg"></i> Crear partido</button>
                            </form>
                        <?php endif; ?>
                    </section>

                    <section class="panel-card existing-matches-card">
                        <div class="management-panel-heading">
                            <h2>Partidos existentes</h2>
                            <span class="meta-label"><?= count($partidosDeRonda) ?> partido(s)</span>
                        </div>
                        <?php if (empty($partidosDeRonda)): ?>
                            <p class="empty-match-state">Todavía no hay partidos en esta ronda.</p>
                        <?php else: ?>
                            <div class="managed-match-list">
                                <?php foreach ($partidosDeRonda as $partido): ?>
                                    <article class="managed-match-card">
                                        <div class="managed-match-summary">
                                            <span class="managed-match-date"><i class="bi bi-calendar3"></i> <?= date('d/m/Y', strtotime($partido['fecha'])) ?><br><?= date('H:i', strtotime($partido['fecha'])) ?></span>
                                            <strong><?= htmlspecialchars($partido['local_nombre']) ?> <b>vs</b> <?= htmlspecialchars($partido['visitante_nombre']) ?></strong>
                                            <span class="managed-score"><?= $partido['idresultado'] ? (int)$partido['puntoslocal'] . ' - ' . (int)$partido['puntosvisitante'] : '— - —' ?></span>
                                            <span class="match-status match-status-<?= htmlspecialchars($partido['estado']) ?>"><?= ucfirst(str_replace('_', ' ', $partido['estado'])) ?></span>
                                        </div>
                                        <details class="managed-match-details">
                                            <summary><i class="bi bi-pencil"></i> Editar</summary>
                                            <form action="<?= BASE_URL ?>/torneos/<?= (int)$torneo['idtorneo'] ?>/gestion/partidos/<?= (int)$partido['idpartido'] ?>" method="POST" class="management-form js-json-form">
                                                <div class="form-grid">
                                                    <select name="idronda" required>
                                                        <?php foreach ($rondas as $ronda): ?><option value="<?= (int)$ronda['idronda'] ?>" <?= (int)$ronda['idronda'] === (int)$partido['idronda'] ? 'selected' : '' ?>>Ronda <?= (int)$ronda['numero'] ?></option><?php endforeach; ?>
                                                    </select>
                                                    <select name="idinscripcion_local" required><?php foreach ($inscripciones as $inscripcion): ?><option value="<?= (int)$inscripcion['idinscripcion'] ?>" <?= (int)$inscripcion['idinscripcion'] === (int)$partido['idinscripcion_local'] ? 'selected' : '' ?>>Local: <?= htmlspecialchars($inscripcion['nombre']) ?></option><?php endforeach; ?></select>
                                                    <select name="idinscripcion_visitante" required><?php foreach ($inscripciones as $inscripcion): ?><option value="<?= (int)$inscripcion['idinscripcion'] ?>" <?= (int)$inscripcion['idinscripcion'] === (int)$partido['idinscripcion_visitante'] ? 'selected' : '' ?>>Visitante: <?= htmlspecialchars($inscripcion['nombre']) ?></option><?php endforeach; ?></select>
                                                    <input type="datetime-local" name="fecha" value="<?= date('Y-m-d\TH:i', strtotime($partido['fecha'])) ?>" required>
                                                    <select name="estado"><?php foreach (['programado', 'en_curso', 'finalizado', 'cancelado'] as $estado): ?><option value="<?= $estado ?>" <?= $partido['estado'] === $estado ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $estado)) ?></option><?php endforeach; ?></select>
                                                </div>
                                                <button class="btn btn-secondary" type="submit">Guardar cambios</button>
                                            </form>
                                            <form action="<?= BASE_URL ?>/torneos/<?= (int)$torneo['idtorneo'] ?>/gestion/partidos/<?= (int)$partido['idpartido'] ?>/resultado" method="POST" class="inline-form score-form js-json-form">
                                                <div class="score-input">
                                                    <label for="puntoslocal-<?= (int)$partido['idpartido'] ?>">Local: <?= htmlspecialchars($partido['local_nombre']) ?></label>
                                                    <input id="puntoslocal-<?= (int)$partido['idpartido'] ?>" type="number" name="puntoslocal" min="0" value="<?= $partido['idresultado'] ? (int)$partido['puntoslocal'] : 0 ?>" required>
                                                </div>
                                                <div class="score-input">
                                                    <label for="puntosvisitante-<?= (int)$partido['idpartido'] ?>">Visitante: <?= htmlspecialchars($partido['visitante_nombre']) ?></label>
                                                    <input id="puntosvisitante-<?= (int)$partido['idpartido'] ?>" type="number" name="puntosvisitante" min="0" value="<?= $partido['idresultado'] ? (int)$partido['puntosvisitante'] : 0 ?>" required>
                                                </div>
                                                <div class="score-input">
                                                    <label for="ganador-<?= (int)$partido['idpartido'] ?>">Ganador</label>
                                                    <select id="ganador-<?= (int)$partido['idpartido'] ?>" name="idinscripcion_ganador">
                                                        <option value="0">Empate / sin ganador</option>
                                                        <option value="<?= (int)$partido['idinscripcion_local'] ?>" <?= (int)$partido['idinscripcion_ganador'] === (int)$partido['idinscripcion_local'] ? 'selected' : '' ?>><?= htmlspecialchars($partido['local_nombre']) ?></option>
                                                        <option value="<?= (int)$partido['idinscripcion_visitante'] ?>" <?= (int)$partido['idinscripcion_ganador'] === (int)$partido['idinscripcion_visitante'] ? 'selected' : '' ?>><?= htmlspecialchars($partido['visitante_nombre']) ?></option>
                                                    </select>
                                                </div>
                                                <input class="score-notes" type="text" name="observaciones" value="<?= htmlspecialchars($partido['observaciones'] ?? '') ?>" placeholder="Observaciones">
                                                <div class="result-actions">
                                                    <button class="btn btn-secondary" type="submit" name="accion" value="actualizar">
                                                        <i class="bi bi-arrow-repeat"></i> Actualizar marcador
                                                    </button>
                                                    <button class="btn btn-primary" type="submit" name="accion" value="finalizar">
                                                        <i class="bi bi-check2-circle"></i> Finalizar partido
                                                    </button>
                                                </div>
                                            </form>
                                            <div class="form-actions">
                                                <form action="<?= BASE_URL ?>/torneos/<?= (int)$torneo['idtorneo'] ?>/gestion/partidos/<?= (int)$partido['idpartido'] ?>/eliminar" method="POST" class="js-json-form"><button class="btn btn-danger" type="submit">Cancelar partido</button></form>
                                            </div>
                                        </details>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php else: ?>
                    <section class="panel-card empty-match-state">Crea una ronda o selecciona una ronda para comenzar.</section>
                <?php endif; ?>
            </section>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
