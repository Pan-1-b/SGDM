<?php

require_once __DIR__ . '/../models/Equipos.php';
require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/../models/Torneos.php';
require_once __DIR__ . '/../core/AuthMiddleware.php';

class EquiposController
{
    private $pdo;
    private $equipos;
    private $usuarios;
    private $torneos;

    public function __construct()
    {
        global $pdo;

        $this->pdo = $pdo;
        $this->equipos = new Equipos($this->pdo);
        $this->usuarios = new Usuarios($this->pdo);
        $this->torneos = new Torneos($this->pdo);
    }

    public function index()
    {
        AuthMiddleware::verificarRol('jugador');

        $termino = trim($_GET['q'] ?? '');
        $misEquipos = $this->equipos->obtenerEquiposPorUsuario($_SESSION['usuario_id']);
        $equiposDisponibles = $this->equipos->obtenerEquiposDisponibles($_SESSION['usuario_id'], $termino);
        $invitaciones = $this->equipos->obtenerInvitacionesPendientes($_SESSION['usuario_id']);
        $solicitudesPorEquipo = [];

        foreach ($misEquipos as $equipo) {
            if ($this->equipos->puedeAdministrarEquipo($equipo['idequipo'], $_SESSION['usuario_id'])) {
                $solicitudesPorEquipo[$equipo['idequipo']] =
                    $this->equipos->obtenerSolicitudesPendientes($equipo['idequipo']);
            }
        }

        $categorias = $this->torneos->obtenerCategorias();
        $titulo = 'Equipos - HERMES';

        require_once __DIR__ . '/../views/equipos/index.php';
    }

    public function crear()
    {
        AuthMiddleware::verificarRol('jugador');

        $categorias = $this->torneos->obtenerCategorias();
        $titulo = 'Crear equipo - HERMES';

        require_once __DIR__ . '/../views/equipos/crear.php';
    }

    public function guardar()
    {
        AuthMiddleware::verificarRol('jugador');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->responder(false, 'Método no permitido.', BASE_URL . '/equipos');
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $idcategoria = (int)($_POST['idcategoria'] ?? 0);

        if ($nombre === '' || $idcategoria <= 0) {
            http_response_code(400);
            $this->responder(false, 'Debes indicar nombre y categoría para el equipo.');
        }

        $idequipo = $this->equipos->crearEquipo($_SESSION['usuario_id'], $nombre, $idcategoria);

        if (!$idequipo) {
            http_response_code(500);
            $this->responder(false, 'No se pudo crear el equipo.');
        }

        $this->responder(true, 'Equipo creado correctamente.', BASE_URL . '/equipos');
    }

    public function editar($idequipo)
    {
        AuthMiddleware::verificarRol('jugador');

        $equipo = $this->equipos->obtenerEquipoPorId((int)$idequipo);

        if (!$equipo || (int)$equipo['idcreador'] !== (int)$_SESSION['usuario_id']) {
            http_response_code(404);
            echo 'Equipo no encontrado o sin permisos.';
            exit;
        }

        $categorias = $this->torneos->obtenerCategorias();
        $titulo = 'Editar equipo - HERMES';

        require_once __DIR__ . '/../views/equipos/editar.php';
    }

    public function actualizar($idequipo)
    {
        AuthMiddleware::verificarRol('jugador');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->responder(false, 'Método no permitido.');
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $idcategoria = (int)($_POST['idcategoria'] ?? 0);
        $estado = $_POST['estado'] ?? 'activo';

        if ($nombre === '' || $idcategoria <= 0 || !in_array($estado, ['activo', 'inactivo'], true)) {
            http_response_code(400);
            $this->responder(false, 'Los datos del equipo no son válidos.');
        }

        if (!$this->equipos->actualizarEquipo(
            (int)$idequipo,
            (int)$_SESSION['usuario_id'],
            $nombre,
            $idcategoria,
            $estado
        )) {
            http_response_code(403);
            $this->responder(false, 'No puedes modificar este equipo.');
        }

        $this->responder(true, 'Equipo actualizado correctamente.', BASE_URL . '/equipos/' . (int)$idequipo);
    }

    public function asignarCapitan($idequipo)
    {
        AuthMiddleware::verificarRol('jugador');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->responder(false, 'Método no permitido.');
        }

        $idcapitan = (int)($_POST['idcapitan'] ?? 0);

        if ($idcapitan <= 0 || !$this->equipos->asignarCapitan(
            (int)$idequipo,
            (int)$_SESSION['usuario_id'],
            $idcapitan
        )) {
            http_response_code(400);
            $this->responder(false, 'Solo el creador puede asignar como capitán a un integrante activo.');
        }

        $this->responder(true, 'Capitán asignado correctamente.', BASE_URL . '/equipos/' . (int)$idequipo);
    }

    public function detalle($idequipo)
    {
        AuthMiddleware::verificarRol('jugador');

        $equipo = $this->equipos->obtenerEquipoPorId((int)$idequipo);

        if (!$equipo || !$this->equipos->obtenerMiEquipoPorId((int)$idequipo, $_SESSION['usuario_id'])) {
            http_response_code(404);
            echo 'Equipo no encontrado.';
            exit;
        }

        $miembros = $this->equipos->obtenerMiembrosEquipo((int)$idequipo);
        $puedeAdministrar = $this->equipos->puedeAdministrarEquipo((int)$idequipo, $_SESSION['usuario_id']);
        $solicitudes = $puedeAdministrar
            ? $this->equipos->obtenerSolicitudesPendientes((int)$idequipo)
            : [];
        $miembrosActivos = array_values(array_filter($miembros, function ($miembro) {
            return $miembro['estado'] === 'activo';
        }));
        $titulo = $equipo['nombre'] . ' - HERMES';

        require_once __DIR__ . '/../views/equipos/detalle.php';
    }

    public function solicitarUnion($idequipo)
    {
        AuthMiddleware::verificarRol('jugador');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->responder(false, 'Método no permitido.');
        }

        if ($this->equipos->tieneConflictoDeCategoria($_SESSION['usuario_id'], (int)$idequipo)) {
            http_response_code(409);
            $this->responder(false, 'Ya perteneces a un equipo activo de esta categoría. Debes salir o quedar inactivo antes de solicitar unirte a otro.');
        }

        if (!$this->equipos->solicitarUnion($_SESSION['usuario_id'], (int)$idequipo)) {
            http_response_code(400);
            $this->responder(false, 'No se pudo enviar la solicitud o ya existe una solicitud previa.');
        }

        $this->responder(true, 'Solicitud enviada correctamente.', BASE_URL . '/equipos');
    }

    public function invitarUsuario($idequipo)
    {
        AuthMiddleware::verificarRol('jugador');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->responder(false, 'Método no permitido.');
        }

        $idusuario = (int)($_POST['idusuario'] ?? 0);

        if ($idusuario <= 0) {
            http_response_code(400);
            $this->responder(false, 'Debes seleccionar un usuario para invitar.');
        }

        if ($this->equipos->tieneConflictoDeCategoria($idusuario, (int)$idequipo)) {
            http_response_code(409);
            $this->responder(false, 'Ese jugador ya pertenece a un equipo activo de la misma categoría.');
        }

        if (!$this->equipos->invitarUsuario($_SESSION['usuario_id'], (int)$idequipo, $idusuario)) {
            http_response_code(400);
            $this->responder(false, 'No se pudo enviar la invitación.');
        }

        $this->responder(true, 'Invitación enviada correctamente.', BASE_URL . '/equipos/' . (int)$idequipo);
    }

    public function responderInvitacion($idequipo)
    {
        AuthMiddleware::verificarRol('jugador');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->responder(false, 'Método no permitido.');
        }

        $estado = $_POST['estado'] ?? 'inactivo';
        $idusuario = (int)$_SESSION['usuario_id'];

        if ($estado === 'activo' && $this->equipos->tieneConflictoDeCategoria($idusuario, (int)$idequipo)) {
            http_response_code(409);
            $this->responder(false, 'No puedes aceptar esta invitación porque ya perteneces a un equipo activo de la misma categoría.');
        }

        if (!$this->equipos->responderInvitacion($idusuario, (int)$idequipo, $estado)) {
            http_response_code(400);
            $this->responder(false, 'No se pudo actualizar tu invitación.');
        }

        $this->responder(true, 'Invitación actualizada correctamente.', BASE_URL . '/equipos');
    }

    public function responderSolicitud($idequipo, $idusuario)
    {
        AuthMiddleware::verificarRol('jugador');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->responder(false, 'Método no permitido.');
        }

        $estado = $_POST['estado'] ?? 'inactivo';

        if ($estado === 'activo' && $this->equipos->tieneConflictoDeCategoria((int)$idusuario, (int)$idequipo)) {
            http_response_code(409);
            $this->responder(false, 'No puedes aceptar esta solicitud porque el jugador ya pertenece a un equipo activo de la misma categoría.');
        }

        if (!$this->equipos->responderSolicitud((int)$idusuario, (int)$idequipo, $estado, (int)$_SESSION['usuario_id'])) {
            http_response_code(400);
            $this->responder(false, 'No se pudo responder a la solicitud.');
        }

        $this->responder(true, 'Solicitud actualizada correctamente.', BASE_URL . '/equipos/' . (int)$idequipo);
    }

    public function cancelarSolicitud($idequipo)
    {
        AuthMiddleware::verificarRol('jugador');
        if (!$this->equipos->cancelarSolicitud((int)$_SESSION['usuario_id'], (int)$idequipo)) {
            http_response_code(400);
            $this->responder(false, 'No se pudo cancelar la solicitud.');
        }
        $this->responder(true, 'Solicitud cancelada correctamente.', BASE_URL . '/equipos');
    }

    public function salirEquipo($idequipo)
    {
        AuthMiddleware::verificarRol('jugador');
        if (!$this->equipos->salirEquipo((int)$_SESSION['usuario_id'], (int)$idequipo)) {
            http_response_code(400);
            $this->responder(false, 'No puedes salir de este equipo.');
        }
        $this->responder(true, 'Has salido del equipo correctamente.', BASE_URL . '/equipos');
    }

    public function eliminarMiembro($idequipo, $idusuario)
    {
        AuthMiddleware::verificarRol('jugador');
        if (!$this->equipos->eliminarMiembro((int)$_SESSION['usuario_id'], (int)$idequipo, (int)$idusuario)) {
            http_response_code(403);
            $this->responder(false, 'Solo el creador puede eliminar a un miembro activo.');
        }
        $this->responder(true, 'Miembro eliminado correctamente.', BASE_URL . '/equipos/' . (int)$idequipo);
    }

    public function cancelarInvitacion($idequipo, $idusuario)
    {
        AuthMiddleware::verificarRol('jugador');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->responder(false, 'Método no permitido.');
        }

        if (!$this->equipos->cancelarInvitacion(
            (int)$idusuario,
            (int)$idequipo,
            (int)$_SESSION['usuario_id']
        )) {
            http_response_code(400);
            $this->responder(false, 'No se pudo cancelar la invitación.');
        }

        $this->responder(true, 'Invitación cancelada correctamente.', BASE_URL . '/usuarios/participantes');
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
}
