<?php

require_once __DIR__ . '/../models/Torneos.php';
require_once __DIR__ . '/../models/Equipos.php';
require_once __DIR__ . '/../core/AuthMiddleware.php';
require_once __DIR__ . '/../models/Auditoria.php';

class TorneosController
{
    private $torneos;
    private $auditoria;

    public function __construct()
    {
        global $pdo;

        $this->torneos = new Torneos($pdo);
        $this->auditoria = new Auditoria($pdo);
    }

    private function responder($success, $message, $redirect = null)
    {
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => null,
            'redirect' => $redirect
        ]);

        exit;
    }

    public function index()
    {
        $torneos = $this->torneos->obtenerTorneosPublicos();

        require_once __DIR__ . '/../views/torneos/index.php';
    }

    public function detalle($id)
    {
        $torneo = $this->torneos->obtenerTorneoPorId((int)$id);

        if (!$torneo) {
            http_response_code(404);
            echo 'Torneo no encontrado.';
            exit;
        }

        $misInscripciones = [];
        $misEquipos = [];
        $autenticado = isset($_SESSION['usuario_id']);
        $puedeInscribirse = $autenticado && AuthMiddleware::tieneRol('jugador');

        if ($puedeInscribirse) {
            $misInscripciones = $this->torneos->obtenerSolicitudesPorUsuario($_SESSION['usuario_id']);
            $misEquipos = $this->torneos->obtenerEquiposDelUsuario($_SESSION['usuario_id']);
        }

        $participantesAprobados = [];
        $equiposInscritos = [];
        $partidosPublicos = $this->torneos->obtenerPartidosPublicos((int)$id);
        $rondasPublicas = $this->torneos->obtenerRondasPublicas((int)$id);
        $esOrganizadorDelTorneo =
            $autenticado
            && (int)$torneo['idorganizador'] === (int)$_SESSION['usuario_id']
            && AuthMiddleware::tieneRol('organizador');

        if ($torneo['modalidad'] === 'individual') {
            $participantesAprobados = $this->torneos->obtenerParticipantesAprobados((int)$id);
        } elseif ($torneo['modalidad'] === 'equipos') {
            $equiposInscritos = $this->torneos->obtenerEquiposInscritosAprobados((int)$id);

            foreach ($equiposInscritos as &$equipo) {
                $equipo['integrantes'] = $this->torneos->obtenerIntegrantesEquipoInscrito($equipo['idequipo']);
            }
            unset($equipo);
        }

        $titulo = $torneo['nombretorneo'] . ' - HERMES';

        require_once __DIR__ . '/../views/torneos/detalle.php';
    }

    public function partidosPublicos($id)
    {
        $torneo = $this->torneos->obtenerTorneoPorId((int)$id);
        if (!$torneo) {
            http_response_code(404);
            $this->responder(false, 'Torneo no encontrado.');
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'data' => $this->torneos->obtenerPartidosPublicos((int)$id)
        ]);
        exit;
    }

    public function inscribirse($id)
    {
        AuthMiddleware::verificarRol('jugador');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->responder(false, 'Método no permitido.');
        }

        $torneo = $this->torneos->obtenerTorneoPorId((int)$id);

        if (!$torneo || $torneo['estado'] !== 'inscripciones') {
            http_response_code(400);
            $this->responder(false, 'El torneo no está abierto a inscripciones.');
        }

        if ($torneo['modalidad'] === 'individual') {
            if (!$this->torneos->solicitarInscripcionIndividual((int)$id, $_SESSION['usuario_id'])) {
                http_response_code(400);
                $this->responder(false, 'Ya tienes una solicitud activa para este torneo.');
            }

            $this->responder(true, 'Solicitud de inscripción enviada correctamente.', BASE_URL . '/torneos/detalle/' . (int)$id);
        }

        if ($torneo['modalidad'] !== 'equipos') {
            http_response_code(400);
            $this->responder(false, 'La modalidad del torneo no permite esta acción.');
        }

        $idequipo = (int)($_POST['idequipo'] ?? 0);

        if ($idequipo <= 0) {
            http_response_code(400);
            $this->responder(false, 'Debes seleccionar un equipo.');
        }

        global $pdo;
        $equipos = new Equipos($pdo);
        $equipo = $equipos->obtenerMiEquipoPorId($idequipo, $_SESSION['usuario_id']);

        if (!$equipo) {
            http_response_code(403);
            $this->responder(false, 'No tienes permisos para inscribir ese equipo.');
        }

        if (!$this->torneos->solicitarInscripcionEquipo((int)$id, $idequipo)) {
            http_response_code(400);
            $this->responder(false, 'Ese equipo ya tiene una solicitud activa para este torneo.');
        }

        $this->responder(true, 'Solicitud del equipo enviada correctamente.', BASE_URL . '/torneos/detalle/' . (int)$id);
    }

    public function misTorneos()
    {
        AuthMiddleware::verificarRol('organizador');

        $torneos = $this->torneos->obtenerTorneosPorOrganizador($_SESSION['usuario_id']);
        foreach ($torneos as &$torneo) {
            if ($torneo['modalidad'] === 'equipos') {
                $torneo['equipos_inscritos'] = $this->torneos->obtenerEquiposInscritosAprobados($torneo['idtorneo']);
                foreach ($torneo['equipos_inscritos'] as &$equipo) {
                    $equipo['integrantes'] = $this->torneos->obtenerIntegrantesEquipoInscrito($equipo['idequipo']);
                }
                unset($equipo);
            } else {
                $torneo['participantes_inscritos'] = $this->torneos->obtenerParticipantesAprobados($torneo['idtorneo']);
            }
        }
        unset($torneo);
        $titulo = 'Mis torneos - HERMES';

        require_once __DIR__ . '/../views/torneos/mis.php';
    }

    public function gestion($id)
    {
        AuthMiddleware::verificarRol('organizador');

        $torneo = $this->torneos->obtenerTorneoPorId((int)$id);
        if (!$torneo || (int)$torneo['idorganizador'] !== (int)$_SESSION['usuario_id']) {
            http_response_code(404);
            echo 'Torneo no encontrado o sin permisos.';
            exit;
        }

        $filtro = trim($_GET['q'] ?? '');
        $rondas = $this->torneos->obtenerRondasGestion($id, $_SESSION['usuario_id'], $filtro);
        $partidos = $this->torneos->obtenerPartidosGestion($id, $_SESSION['usuario_id'], $filtro);
        $inscripciones = $this->torneos->obtenerInscripcionesAprobadasParaGestion($id, $_SESSION['usuario_id']);
        $titulo = 'Gestionar torneo - HERMES';

        require_once __DIR__ . '/../views/torneos/gestion.php';
    }

    public function guardarRonda($id)
    {
        AuthMiddleware::verificarRol('organizador');
        $datos = $this->datosRonda();
        if (!$this->torneos->crearRonda((int)$id, $_SESSION['usuario_id'], $datos['numero'], $datos['nombre'], $datos['fechainicio'], $datos['fechafin'], $datos['estado'])) {
            http_response_code(400);
            $this->responder(false, 'No se pudo crear la ronda. Verifica que el número no esté repetido.');
        }
        $this->auditoria->registrar('crear_ronda', 'ronda', null, null, $datos);
        $this->responder(true, 'Ronda creada correctamente.', BASE_URL . '/torneos/' . (int)$id . '/gestion');
    }

    public function actualizarRonda($id, $idronda)
    {
        AuthMiddleware::verificarRol('organizador');
        $datos = $this->datosRonda();
        if (!$this->torneos->actualizarRonda((int)$idronda, (int)$id, $_SESSION['usuario_id'], $datos['numero'], $datos['nombre'], $datos['fechainicio'], $datos['fechafin'], $datos['estado'])) {
            http_response_code(400);
            $this->responder(false, 'No se pudo actualizar la ronda.');
        }
        $this->auditoria->registrar('actualizar_ronda', 'ronda', $idronda, null, $datos);
        $this->responder(true, 'Ronda actualizada correctamente.', BASE_URL . '/torneos/' . (int)$id . '/gestion');
    }

    public function eliminarRonda($id, $idronda)
    {
        AuthMiddleware::verificarRol('organizador');
        if (!$this->torneos->eliminarRonda((int)$idronda, (int)$id, $_SESSION['usuario_id'])) {
            http_response_code(400);
            $this->responder(false, 'La ronda no se puede eliminar porque ya tiene partidos asociados.');
        }
        $this->auditoria->registrar('eliminar_ronda', 'ronda', $idronda);
        $this->responder(true, 'Ronda eliminada correctamente.', BASE_URL . '/torneos/' . (int)$id . '/gestion');
    }

    public function guardarPartido($id)
    {
        AuthMiddleware::verificarRol('organizador');
        $datos = $this->datosPartido();
        if (!$this->torneos->crearPartido((int)$id, $_SESSION['usuario_id'], $datos['idronda'], $datos['local'], $datos['visitante'], $datos['fecha'], $datos['estado'])) {
            http_response_code(400);
            $this->responder(false, 'No se pudo crear el partido. Verifica la ronda y las inscripciones aprobadas.');
        }
        $this->auditoria->registrar('crear_partido', 'partido', null, null, $datos);
        $this->responder(true, 'Partido creado correctamente.', BASE_URL . '/torneos/' . (int)$id . '/gestion');
    }

    public function actualizarPartido($id, $idpartido)
    {
        AuthMiddleware::verificarRol('organizador');
        $datos = $this->datosPartido();
        if (!$this->torneos->actualizarPartido((int)$idpartido, (int)$id, $_SESSION['usuario_id'], $datos['idronda'], $datos['local'], $datos['visitante'], $datos['fecha'], $datos['estado'])) {
            http_response_code(400);
            $this->responder(false, 'No se pudo actualizar el partido.');
        }
        $this->auditoria->registrar('actualizar_partido', 'partido', $idpartido, null, $datos);
        $this->responder(true, 'Partido actualizado correctamente.', BASE_URL . '/torneos/' . (int)$id . '/gestion');
    }

    public function eliminarPartido($id, $idpartido)
    {
        AuthMiddleware::verificarRol('organizador');
        if (!$this->torneos->eliminarPartido((int)$idpartido, (int)$id, $_SESSION['usuario_id'])) {
            http_response_code(400);
            $this->responder(false, 'No se pudo cancelar el partido. Un partido finalizado no puede cancelarse.');
        }
        $this->auditoria->registrar('cancelar_partido', 'partido', $idpartido, null, ['estado' => 'cancelado']);
        $this->responder(true, 'Partido cancelado correctamente.', BASE_URL . '/torneos/' . (int)$id . '/gestion');
    }

    public function guardarResultado($id, $idpartido)
    {
        AuthMiddleware::verificarRol('organizador');
        $local = filter_input(INPUT_POST, 'puntoslocal', FILTER_VALIDATE_INT);
        $visitante = filter_input(INPUT_POST, 'puntosvisitante', FILTER_VALIDATE_INT);
        $ganador = (int)($_POST['idinscripcion_ganador'] ?? 0);
        $observaciones = trim($_POST['observaciones'] ?? '');
        $finalizar = ($_POST['accion'] ?? 'finalizar') === 'finalizar';

        if ($local === false || $visitante === false || $local < 0 || $visitante < 0) {
            http_response_code(400);
            $this->responder(false, 'Los puntos deben ser números enteros no negativos.');
        }

        if (!$this->torneos->guardarResultado((int)$idpartido, (int)$id, $_SESSION['usuario_id'], $local, $visitante, $ganador, $observaciones, $finalizar)) {
            http_response_code(400);
            $this->responder(false, 'No se pudo guardar el resultado.');
        }
        $this->auditoria->registrar($finalizar ? 'finalizar_partido' : 'actualizar_marcador', 'resultado', $idpartido, null, [
            'puntoslocal' => $local,
            'puntosvisitante' => $visitante,
            'ganador' => $ganador,
            'observaciones' => $observaciones
        ]);
        $this->responder(
            true,
            $finalizar ? 'Resultado final guardado correctamente.' : 'Marcador actualizado. El partido continúa en curso.',
            BASE_URL . '/torneos/' . (int)$id . '/gestion'
        );
    }

    public function eliminarResultado($id, $idpartido)
    {
        AuthMiddleware::verificarRol('organizador');
        http_response_code(400);
        $this->responder(false, 'Los resultados no se eliminan directamente. Cancela el partido para invalidar su resultado.');
    }

    private function datosRonda()
    {
        $numero = (int)($_POST['numero'] ?? 0);
        $estado = $_POST['estado'] ?? 'pendiente';
        if ($numero <= 0 || !in_array($estado, ['pendiente', 'en_curso', 'finalizada'], true)) {
            http_response_code(400);
            $this->responder(false, 'Los datos de la ronda no son válidos.');
        }
        return [
            'numero' => $numero,
            'nombre' => trim($_POST['nombre'] ?? ''),
            'fechainicio' => trim($_POST['fechainicio'] ?? ''),
            'fechafin' => trim($_POST['fechafin'] ?? ''),
            'estado' => $estado
        ];
    }

    private function datosPartido()
    {
        $idronda = (int)($_POST['idronda'] ?? 0);
        $local = (int)($_POST['idinscripcion_local'] ?? 0);
        $visitante = (int)($_POST['idinscripcion_visitante'] ?? 0);
        $estado = $_POST['estado'] ?? 'programado';
        if ($idronda <= 0 || $local <= 0 || $visitante <= 0 || $local === $visitante || trim($_POST['fecha'] ?? '') === '' || !in_array($estado, ['programado', 'en_curso', 'finalizado', 'cancelado'], true)) {
            http_response_code(400);
            $this->responder(false, 'Los datos del partido no son válidos.');
        }
        return [
            'idronda' => $idronda,
            'local' => $local,
            'visitante' => $visitante,
            'fecha' => trim($_POST['fecha']),
            'estado' => $estado
        ];
    }

    public function solicitudes()
    {
        AuthMiddleware::verificarRol('organizador');

        $solicitudes = $this->torneos->obtenerSolicitudesPorOrganizador($_SESSION['usuario_id']);
        $titulo = 'Solicitudes - HERMES';

        require_once __DIR__ . '/../views/torneos/solicitudes.php';
    }

    public function actualizarEstadoSolicitud($idinscripcion)
    {
        AuthMiddleware::verificarRol('organizador');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->responder(false, 'Método no permitido.');
        }

        $estado = $_POST['estado'] ?? '';

        if (!$this->torneos->actualizarEstadoInscripcion(
            (int)$idinscripcion,
            (int)$_SESSION['usuario_id'],
            $estado
        )) {
            http_response_code(400);
            $this->responder(false, 'No se pudo actualizar el estado de la solicitud.');
        }

        $this->responder(true, 'Estado de la solicitud actualizado correctamente.', BASE_URL . '/solicitudes');
    }

    public function misSolicitudes()
    {
        AuthMiddleware::verificarRol('jugador');

        $solicitudes = $this->torneos->obtenerSolicitudesPorUsuario($_SESSION['usuario_id']);
        $titulo = 'Mis solicitudes - HERMES';

        require_once __DIR__ . '/../views/torneos/mis-solicitudes.php';
    }

    public function crear()
    {
        AuthMiddleware::verificarRol('organizador');

        $titulo = 'Crear torneo - HERMES';
        $categorias = $this->torneos->obtenerCategorias();

        require_once __DIR__ . '/../views/torneos/crear.php';
    }

    public function guardar()
    {
        AuthMiddleware::verificarRol('organizador');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->responder(false, 'Método no permitido.');
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $modalidad = trim($_POST['modalidad'] ?? '');
        $idcategoria = (int)($_POST['idcategoria'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');
        $fechainicio = trim($_POST['fechainicio'] ?? '');
        $fechafin = trim($_POST['fechafin'] ?? '');
        $horainicio = trim($_POST['horainicio'] ?? '');
        $idorganizador = $_SESSION['usuario_id'] ?? null;

        if (
            $nombre === '' ||
            $tipo === '' ||
            $modalidad === '' ||
            $idcategoria <= 0 ||
            $fechainicio === '' ||
            $fechafin === '' ||
            $horainicio === '' ||
            $idorganizador === null
        ) {
            http_response_code(400);
            $this->responder(false, 'Todos los campos obligatorios deben completarse.');
        }

        if (strtotime($fechainicio) > strtotime($fechafin)) {
            http_response_code(400);
            $this->responder(false, 'La fecha de inicio no puede ser mayor que la fecha de finalización.');
        }

        $torneoId = $this->torneos->crearTorneo(
            $nombre,
            $tipo,
            $modalidad,
            $idcategoria,
            $descripcion,
            $fechainicio,
            $fechafin,
            $horainicio,
            $idorganizador
        );

        if ($torneoId) {
            $this->responder(true, 'Torneo creado correctamente.', BASE_URL . '/torneos');
        }

        http_response_code(500);
        $this->responder(false, 'No se pudo crear el torneo.');
    }

    public function editar($id)
    {
        AuthMiddleware::verificarRol('organizador');

        $torneo = $this->torneos->obtenerTorneoPorId((int)$id);

        if (!$torneo || (int)$torneo['idorganizador'] !== (int)$_SESSION['usuario_id']) {
            http_response_code(404);
            $this->responder(false, 'No tienes permiso para modificar este torneo.');
        }

        $titulo = 'Editar torneo - HERMES';
        $categorias = $this->torneos->obtenerCategorias();

        require_once __DIR__ . '/../views/torneos/editar.php';
    }

    public function actualizar($id)
    {
        AuthMiddleware::verificarRol('organizador');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->responder(false, 'Método no permitido.');
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $modalidad = trim($_POST['modalidad'] ?? '');
        $idcategoria = (int)($_POST['idcategoria'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');
        $fechainicio = trim($_POST['fechainicio'] ?? '');
        $fechafin = trim($_POST['fechafin'] ?? '');
        $horainicio = trim($_POST['horainicio'] ?? '');

        if (
            $nombre === '' ||
            $tipo === '' ||
            $modalidad === '' ||
            $idcategoria <= 0 ||
            $fechainicio === '' ||
            $fechafin === '' ||
            $horainicio === ''
        ) {
            http_response_code(400);
            $this->responder(false, 'Todos los campos obligatorios deben completarse.');
        }

        if (strtotime($fechainicio) > strtotime($fechafin)) {
            http_response_code(400);
            $this->responder(false, 'La fecha de inicio no puede ser mayor que la fecha de finalización.');
        }

        $resultado = $this->torneos->modificarTorneo(
            (int)$id,
            (int)$_SESSION['usuario_id'],
            $nombre,
            $tipo,
            $modalidad,
            $idcategoria,
            $descripcion,
            $fechainicio,
            $fechafin,
            $horainicio
        );

        if ($resultado) {
            $this->responder(true, 'Torneo actualizado correctamente.', BASE_URL . '/mis-torneos');
        }

        http_response_code(500);
        $this->responder(false, 'No se pudo actualizar el torneo.');
    }

    public function actualizarEstado($id)
    {
        AuthMiddleware::verificarRol('organizador');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->responder(false, 'Método no permitido.');
        }

        $estado = trim($_POST['estado'] ?? '');
        $estadosPermitidos = ['borrador', 'inscripciones', 'en_curso', 'finalizado', 'cancelado'];

        if (!in_array($estado, $estadosPermitidos, true)) {
            http_response_code(400);
            $this->responder(false, 'El estado seleccionado no es válido.');
        }

        if (!$this->torneos->actualizarEstadoTorneo((int)$id, (int)$_SESSION['usuario_id'], $estado)) {
            http_response_code(500);
            $this->responder(false, 'No se pudo actualizar el estado del torneo o no tienes permiso para modificarlo.');
        }

        $this->responder(true, 'Estado del torneo actualizado correctamente.', BASE_URL . '/mis-torneos');
    }

    public function eliminar($id)
    {
        AuthMiddleware::verificarRol('organizador');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->responder(false, 'Método no permitido.');
        }

        if (!$this->torneos->eliminarTorneo((int)$id, (int)$_SESSION['usuario_id'])) {
            http_response_code(500);
            $this->responder(false, 'No se pudo cancelar el torneo o no tienes permiso para modificarlo.');
        }

        $this->responder(true, 'Torneo cancelado correctamente.', BASE_URL . '/mis-torneos');
    }
}