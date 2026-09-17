<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main>
    <section class="torneo-detail">
        <div class="section-header">
            <div>
                <h1><?= htmlspecialchars($torneo['nombretorneo']) ?></h1>
                <p>Detalle del torneo y sus opciones de inscripción.</p>
            </div>
            <a href="<?= BASE_URL ?>/torneos" class="btn btn-secondary">Volver</a>
        </div>

        <div class="tournament-detail-layout">
            <div class="tournament-meta-grid">
                <div class="tournament-meta-card">
                    <span class="meta-label">Tipo</span>
                    <strong><?= htmlspecialchars($torneo['tipo']) ?></strong>
                </div>
                <div class="tournament-meta-card">
                    <span class="meta-label">Modalidad</span>
                    <strong><?= htmlspecialchars($torneo['modalidad']) ?></strong>
                </div>
                <div class="tournament-meta-card">
                    <span class="meta-label">Categoría</span>
                    <strong><?= htmlspecialchars($torneo['categoria']) ?></strong>
                </div>
                <div class="tournament-meta-card">
                    <span class="meta-label">Organizador</span>
                    <strong><?= htmlspecialchars($torneo['organizador']) ?></strong>
                </div>
                <div class="tournament-meta-card">
                    <span class="meta-label">Estado</span>
                    <span class="torneo-estado"><?= htmlspecialchars($torneo['estado']) ?></span>
                </div>
                <div class="tournament-meta-card">
                    <span class="meta-label">Inicio</span>
                    <strong><?= date('d/m/Y', strtotime($torneo['fechainicio'])) ?></strong>
                </div>
                <div class="tournament-meta-card">
                    <span class="meta-label">Fin</span>
                    <strong><?= date('d/m/Y', strtotime($torneo['fechafin'])) ?></strong>
                </div>
                <div class="tournament-meta-card">
                    <span class="meta-label">Hora</span>
                    <strong><?= htmlspecialchars($torneo['horainicio']) ?></strong>
                </div>
            </div>

            <div class="tournament-section detail-description">
                <h2>Descripción</h2>
                <p><?= nl2br(htmlspecialchars($torneo['descripcion'] ?? 'Sin descripción.')) ?></p>
            </div>

            <div class="tournament-section tournament-participants">
                <h2><?= $torneo['modalidad'] === 'equipos' ? 'Equipos inscritos' : 'Jugadores inscritos' ?></h2>

                <?php if ($torneo['modalidad'] === 'equipos'): ?>
                    <?php if (empty($equiposInscritos)): ?>
                        <p>Aún no hay equipos aprobados en este torneo.</p>
                    <?php else: ?>
                        <div class="mini-list">
                            <?php foreach ($equiposInscritos as $equipo): ?>
                                <div class="mini-item">
                                    <div>
                                        <strong><?= htmlspecialchars($equipo['nombre']) ?></strong>
                                        <small><?= count($equipo['integrantes']) ?> integrante(s)</small>
                                    </div>
                                    <div class="team-members">
                                        <?php foreach ($equipo['integrantes'] as $integrante): ?>
                                            <span class="relationship-status active-status">
                                                <?= htmlspecialchars($integrante['nombre']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php elseif (empty($participantesAprobados)): ?>
                    <p>Aún no hay jugadores aprobados en este torneo.</p>
                <?php else: ?>
                    <div class="mini-list">
                        <?php foreach ($participantesAprobados as $participante): ?>
                            <div class="mini-item">
                                <div>
                                    <strong><?= htmlspecialchars($participante['nombre']) ?></strong>
                                    <small><?= htmlspecialchars($participante['email']) ?></small>
                                </div>
                                <span class="relationship-status active-status">Inscrito</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <section class="tournament-section tournament-matches">
                <div class="tournament-section-header">
                    <div>
                        <span class="meta-label">Competencia</span>
                        <h2>Partidos y resultados</h2>
                    </div>
                    <?php if ($esOrganizadorDelTorneo): ?>
                        <a href="<?= BASE_URL ?>/torneos/<?= (int)$torneo['idtorneo'] ?>/gestion" class="btn btn-secondary">
                            <i class="bi bi-gear"></i> Gestionar
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($rondasPublicas)): ?>
                    <div class="public-rounds">
                        <?php foreach ($rondasPublicas as $ronda): ?>
                            <div class="public-round">
                                <span>Ronda <?= (int)$ronda['numero'] ?></span>
                                <strong><?= htmlspecialchars($ronda['nombre'] ?: 'Sin nombre') ?></strong>
                                <small><?= ucfirst(str_replace('_', ' ', $ronda['estado'])) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php
                $partidosEnCurso = array_filter($partidosPublicos, static function ($partido) {
                    return $partido['estado'] === 'en_curso';
                });
                $partidosProgramados = array_filter($partidosPublicos, static function ($partido) {
                    return $partido['estado'] === 'programado';
                });
                $partidosResultados = array_filter($partidosPublicos, static function ($partido) {
                    return $partido['estado'] === 'finalizado' || $partido['estado'] === 'cancelado';
                });
                ?>

                <div class="public-match-layout">
                    <aside class="match-summary">
                        <div class="match-summary-item">
                            <i class="bi bi-lightning-charge"></i>
                            <span>Partidos en curso</span>
                            <strong><?= count($partidosEnCurso) ?></strong>
                        </div>
                        <div class="match-summary-item">
                            <i class="bi bi-calendar-event"></i>
                            <span>Partidos programados</span>
                            <strong><?= count($partidosProgramados) ?></strong>
                        </div>
                        <div class="match-summary-item">
                            <i class="bi bi-trophy"></i>
                            <span>Resultados</span>
                            <strong><?= count($partidosResultados) ?></strong>
                        </div>
                    </aside>

                    <div class="match-content" data-live-matches data-matches-endpoint="<?= BASE_URL ?>/torneos/<?= (int)$torneo['idtorneo'] ?>/partidos-publicos">
                        <div class="match-tabs" role="tablist" aria-label="Partidos del torneo">
                            <button type="button" class="match-tab is-active" data-match-tab="en-curso">
                                <i class="bi bi-lightning-charge"></i> En curso <span><?= count($partidosEnCurso) ?></span>
                            </button>
                            <button type="button" class="match-tab" data-match-tab="programados">
                                <i class="bi bi-calendar-event"></i> Programados <span><?= count($partidosProgramados) ?></span>
                            </button>
                            <button type="button" class="match-tab" data-match-tab="resultados">
                                <i class="bi bi-trophy"></i> Resultados <span><?= count($partidosResultados) ?></span>
                            </button>
                        </div>

                        <?php
                        $gruposPartidos = [
                            'en-curso' => $partidosEnCurso,
                            'programados' => $partidosProgramados,
                            'resultados' => $partidosResultados
                        ];
                        foreach ($gruposPartidos as $grupo => $partidos): ?>
                            <div class="match-panel <?= $grupo === 'en-curso' ? 'is-active' : '' ?>" data-match-panel="<?= $grupo ?>">
                                <?php if (empty($partidos)): ?>
                                    <p class="empty-match-state">No hay partidos en esta categoría.</p>
                                <?php else: ?>
                                    <div class="match-card-list">
                                        <?php foreach ($partidos as $partido): ?>
                                            <article class="match-card" data-match-id="<?= (int)$partido['idpartido'] ?>">
                                                <div class="match-card-heading">
                                                    <span>Ronda <?= (int)$partido['numero_ronda'] ?><?= $partido['nombre_ronda'] ? ' · ' . htmlspecialchars($partido['nombre_ronda']) : '' ?></span>
                                                    <span class="match-status match-status-<?= htmlspecialchars($partido['estado']) ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $partido['estado'])) ?>
                                                    </span>
                                                </div>
                                                <div class="match-scoreboard">
                                                    <strong><?= htmlspecialchars($partido['local_nombre']) ?></strong>
                                                    <?php if ($partido['idresultado']): ?>
                                                        <b><?= (int)$partido['puntoslocal'] ?> - <?= (int)$partido['puntosvisitante'] ?></b>
                                                    <?php else: ?>
                                                        <b>vs</b>
                                                    <?php endif; ?>
                                                    <strong><?= htmlspecialchars($partido['visitante_nombre']) ?></strong>
                                                </div>
                                                <div class="match-card-footer">
                                                    <span><i class="bi bi-calendar3"></i> <?= date('d/m/Y H:i', strtotime($partido['fecha'])) ?></span>
                                                    <?php if (!empty($partido['observaciones'])): ?>
                                                        <small><?= htmlspecialchars($partido['observaciones']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
         


<pre>


<?php
/*var_dump([
    'puedeInscribirse' => $puedeInscribirse,
    'autenticado' => isset($_SESSION['usuario_id']),
    'rol_jugador' => AuthMiddleware::tieneRol('jugador'),
    'estado_torneo' => $torneo['estado'],
    'modalidad' => $torneo['modalidad'],
    'inscripcionActual' => $inscripcionActual ?? 'todavia no calculada'
]);*/
?>
</pre>

            <?php if ($puedeInscribirse): ?>
                <div class="tournament-section tournament-registration">
                    <h2>Inscripción</h2>
                <?php $inscripcionActual = null; foreach ($misInscripciones as $inscripcion): if ((int)$inscripcion['idtorneo'] === (int)$torneo['idtorneo']) { $inscripcionActual = $inscripcion; break; } endforeach; ?>

                <?php if ($torneo['estado'] === 'inscripciones'): ?>
                    <?php if ($inscripcionActual): ?>
                        <div class="status-box success-box">
                            <strong>Tu solicitud:</strong> <?= htmlspecialchars($inscripcionActual['estado']) ?>
                        </div>
                    <?php elseif ($torneo['modalidad'] === 'individual'): ?>
                        <form action="<?= BASE_URL ?>/torneos/inscribirse/<?= (int)$torneo['idtorneo'] ?>" method="POST" class="inline-form js-json-form">
                            <button type="submit" class="btn btn-primary">Solicitar inscripción al organizador</button>
                        </form>
                    <?php else: ?>
                        <?php if (empty($misEquipos)): ?>
                            <div class="status-box warning-box">
                                No tienes equipos activos para inscribir. Crea o únete a un equipo antes de continuar.
                            </div>
                        <?php else: ?>
                            <form action="<?= BASE_URL ?>/torneos/inscribirse/<?= (int)$torneo['idtorneo'] ?>" method="POST" class="inline-form js-json-form">
                                <label for="idequipo">Seleccione un equipo</label>
                                <select id="idequipo" name="idequipo" required>
                                    <option value="">Selecciona un equipo</option>
                                    <?php foreach ($misEquipos as $equipo): ?>
                                        <option value="<?= (int)$equipo['idequipo'] ?>"><?= htmlspecialchars($equipo['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-primary">Inscribir equipo</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="status-box neutral-box">
                        Este torneo no está actualmente en fase de inscripción.
                    </div>
                <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if (!$puedeInscribirse && !isset($_SESSION['usuario_id'])): ?>
                <div class="tournament-section tournament-registration">
                    <h2>Inscripción</h2>
                    <div class="status-box neutral-box">
                        Inicia sesión o crea una cuenta para participar en este torneo.
                    </div>
                    <div class="form-actions">
                        <a href="<?= BASE_URL ?>/login" class="btn btn-primary">Iniciar sesión</a>
                        <a href="<?= BASE_URL ?>/registro" class="btn btn-secondary">Crear cuenta</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-match-tab]').forEach(function (tab) {
        tab.addEventListener('click', function () {
            const target = tab.dataset.matchTab;
            document.querySelectorAll('[data-match-tab]').forEach(function (item) {
                item.classList.toggle('is-active', item === tab);
            });
            document.querySelectorAll('[data-match-panel]').forEach(function (panel) {
                panel.classList.toggle('is-active', panel.dataset.matchPanel === target);
            });
        });
    });

    const liveMatches = document.querySelector('[data-live-matches]');
    if (!liveMatches) {
        return;
    }

    const endpoint = liveMatches.dataset.matchesEndpoint;
    const panels = {
        'en_curso': liveMatches.querySelector('[data-match-panel="en-curso"]'),
        'programado': liveMatches.querySelector('[data-match-panel="programados"]'),
        'finalizado': liveMatches.querySelector('[data-match-panel="resultados"]'),
        'cancelado': liveMatches.querySelector('[data-match-panel="resultados"]')
    };

    function escapeHtml(value) {
        const element = document.createElement('span');
        element.textContent = value === null || value === undefined ? '' : String(value);
        return element.innerHTML;
    }

    function formatDate(value) {
        const date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) {
            return value;
        }
        return date.toLocaleDateString('es-ES') + ' ' + date.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function groupFor(status) {
        return status === 'en_curso' ? 'en_curso' : (status === 'programado' ? 'programado' : 'finalizado');
    }

    function renderMatches(matches) {
        const grouped = {
            'en_curso': [],
            'programado': [],
            'finalizado': []
        };

        matches.forEach(function (match) {
            grouped[groupFor(match.estado)].push(match);
        });

        Object.keys(grouped).forEach(function (group) {
            const panel = panels[group];
            if (!panel) {
                return;
            }

            if (grouped[group].length === 0) {
                panel.innerHTML = '<p class="empty-match-state">No hay partidos en esta categoría.</p>';
                return;
            }

            const list = document.createElement('div');
            list.className = 'match-card-list';
            grouped[group].forEach(function (match) {
                const score = match.idresultado
                    ? escapeHtml(match.puntoslocal) + ' - ' + escapeHtml(match.puntosvisitante)
                    : 'vs';
                const observations = match.observaciones
                    ? '<small>' + escapeHtml(match.observaciones) + '</small>'
                    : '';
                list.insertAdjacentHTML('beforeend',
                    '<article class="match-card" data-match-id="' + escapeHtml(match.idpartido) + '">' +
                        '<div class="match-card-heading">' +
                            '<span>Ronda ' + escapeHtml(match.numero_ronda) +
                            (match.nombre_ronda ? ' · ' + escapeHtml(match.nombre_ronda) : '') + '</span>' +
                            '<span class="match-status match-status-' + escapeHtml(match.estado) + '">' +
                                escapeHtml(match.estado.replace('_', ' ')) +
                            '</span>' +
                        '</div>' +
                        '<div class="match-scoreboard">' +
                            '<strong>' + escapeHtml(match.local_nombre) + '</strong>' +
                            '<b>' + score + '</b>' +
                            '<strong>' + escapeHtml(match.visitante_nombre) + '</strong>' +
                        '</div>' +
                        '<div class="match-card-footer">' +
                            '<span><i class="bi bi-calendar3"></i> ' + formatDate(match.fecha) + '</span>' +
                            observations +
                        '</div>' +
                    '</article>'
                );
            });
            panel.replaceChildren(list);
        });

        ['en_curso', 'programado', 'finalizado'].forEach(function (group) {
            const count = grouped[group].length;
            const tabName = group === 'en_curso' ? 'en-curso' : (group === 'programado' ? 'programados' : 'resultados');
            const tab = liveMatches.querySelector('[data-match-tab="' + tabName + '"] span');
            if (tab) {
                tab.textContent = count;
            }
            const summary = liveMatches.closest('.tournament-section').querySelector(
                '.match-summary-item:nth-child(' + (group === 'en_curso' ? 1 : (group === 'programado' ? 2 : 3)) + ') strong'
            );
            if (summary) {
                summary.textContent = count;
            }
        });
    }

    async function refreshLiveMatches() {
        try {
            const response = await fetch(endpoint, { headers: { Accept: 'application/json' } });
            if (!response.ok) {
                return;
            }
            const payload = await response.json();
            if (payload.success && Array.isArray(payload.data)) {
                renderMatches(payload.data);
            }
        } catch (error) {
            console.error('No se pudieron actualizar los partidos en vivo.', error);
        }
    }

    refreshLiveMatches();
    window.setInterval(refreshLiveMatches, 5000);
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
