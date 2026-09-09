<?php

require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/../models/Auditoria.php';
require_once __DIR__ . '/../core/AuthMiddleware.php';

class AdministracionController
{
    private $pdo;
    private $usuarios;
    private $auditoria;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
        $this->usuarios = new Usuarios($pdo);
        $this->auditoria = new Auditoria($pdo);
    }

    public function index()
    {
        AuthMiddleware::verificarRol('administrador');

        $filtro = trim($_GET['q'] ?? '');
        $rol = trim($_GET['rol'] ?? '');
        $usuarios = $this->usuarios->obtenerUsuariosAdministracion($filtro, $rol);
        $auditorias = $this->auditoria->obtenerTodas($filtro);
        $titulo = 'Administración - HERMES';

        require_once __DIR__ . '/../views/administracion/index.php';
    }

    public function actualizarEstado($idusuario)
    {
        AuthMiddleware::verificarRol('administrador');
        header('Content-Type: application/json; charset=utf-8');

        $usuario = $this->usuarios->obtenerUsuarioAdministrable($idusuario);
        $estado = $_POST['estado'] ?? '';

        if (!$usuario || (int)$usuario['idusuario'] === (int)$_SESSION['usuario_id']) {
            $this->responder(false, 'No puedes modificar este usuario.');
        }

        if (!in_array($estado, ['activo', 'bloqueado'], true)) {
            $this->responder(false, 'El estado seleccionado no es válido.');
        }

        if (!$this->usuarios->actualizarEstado((int)$idusuario, $estado)) {
            http_response_code(400);
            $this->responder(false, 'No se pudo actualizar el estado del usuario.');
        }

        $this->auditoria->registrar(
            $estado === 'bloqueado' ? 'bloquear_usuario' : 'desbloquear_usuario',
            'usuario',
            $idusuario,
            ['estado' => $usuario['estado']],
            ['estado' => $estado]
        );

        $this->responder(true, 'Estado del usuario actualizado correctamente.', BASE_URL . '/administracion');
    }

    private function responder($success, $message, $redirect = null)
    {
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => null,
            'redirect' => $redirect
        ]);
        exit;
    }
}
