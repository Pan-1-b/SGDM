<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/../models/Equipos.php';
require_once __DIR__ . '/../models/Auditoria.php';
require_once __DIR__ . '/../core/AuthMiddleware.php';

class UsuariosController
{
    private $pdo;
    private $usuarios;
    private $auditoria;

    public function __construct()
    {
        global $pdo;

        $this->pdo = $pdo;
        $this->usuarios = new Usuarios($this->pdo);
        $this->auditoria = new Auditoria($this->pdo);
    }

    /**
     * Mostrar formulario de registro
     */
    public function registro()
    {
        require_once __DIR__ . '/../views/auth/registro.php';
    }

    public function perfil()
    {
        AuthMiddleware::verificar();

        $usuario = $this->usuarios->obtenerUsuarioPorId($_SESSION['usuario_id']);

        if (!$usuario) {
            http_response_code(404);
            echo 'Usuario no encontrado.';
            exit;
        }

        $titulo = 'Mi perfil - HERMES';

        require_once __DIR__ . '/../views/usuarios/perfil.php';
    }

    public function participantes()
    {
        AuthMiddleware::verificarRol('jugador');

        $busqueda = trim($_GET['q'] ?? '');
        $participantes = $this->usuarios->obtenerParticipantes($busqueda);
        $equipos = new Equipos($this->pdo);
        $misEquipos = $equipos->obtenerEquiposPorUsuario($_SESSION['usuario_id']);
        $equiposAdministrables = array_values(array_filter($misEquipos, function ($equipo) {
            return (int)$equipo['idcreador'] === (int)$_SESSION['usuario_id']
                || (int)$equipo['idcapitan'] === (int)$_SESSION['usuario_id'];
        }));
        $relacionesGestionables = $equipos->obtenerRelacionesGestionables($_SESSION['usuario_id']);
        $titulo = 'Participantes - HERMES';

        require_once __DIR__ . '/../views/usuarios/participantes.php';
    }

    public function invitarParticipante()
    {
        AuthMiddleware::verificarRol('jugador');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Método no permitido.',
                'data' => null,
                'redirect' => null
            ]);
            exit;
        }

        $idusuario = (int)($_POST['idusuario'] ?? 0);
        $idequipo = (int)($_POST['idequipo'] ?? 0);

        if ($idusuario <= 0 || $idequipo <= 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Debes seleccionar un participante y un equipo.',
                'data' => null,
                'redirect' => null
            ]);
            exit;
        }

        $equipos = new Equipos($this->pdo);

        if (!$equipos->invitarUsuario($_SESSION['usuario_id'], $idequipo, $idusuario)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo invitar al participante.',
                'data' => null,
                'redirect' => BASE_URL . '/usuarios/participantes'
            ]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Invitación enviada correctamente.',
            'data' => null,
            'redirect' => BASE_URL . '/usuarios/participantes'
        ]);
        exit;
    }

    public function actualizarPerfil()
    {
        header('Content-Type: application/json; charset=utf-8');
        AuthMiddleware::verificar();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->responderPerfil(false, 'Método no permitido.');
        }

        $idusuario = (int)$_SESSION['usuario_id'];
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $passwordActual = $_POST['password_actual'] ?? '';
        $passwordNueva = $_POST['password_nueva'] ?? '';
        $passwordConfirmacion = $_POST['password_confirmacion'] ?? '';

        if ($nombre === '' || $email === '') {
            http_response_code(400);
            $this->responderPerfil(false, 'El nombre y el correo electrónico son obligatorios.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            $this->responderPerfil(false, 'El correo electrónico no es válido.');
        }

        if ($this->usuarios->existeEmailParaOtroUsuario($email, $idusuario)) {
            http_response_code(409);
            $this->responderPerfil(false, 'El correo electrónico ya está registrado.');
        }

        $password = null;

        if ($passwordNueva !== '' || $passwordConfirmacion !== '' || $passwordActual !== '') {
            $usuario = $this->usuarios->obtenerUsuarioPorEmail($_SESSION['usuario_email']);

            if (!$usuario || $passwordActual === '' || !password_verify($passwordActual, $usuario['contrasena'])) {
                http_response_code(400);
                $this->responderPerfil(false, 'La contraseña actual no es correcta.');
            }

            if (strlen($passwordNueva) < 8) {
                http_response_code(400);
                $this->responderPerfil(false, 'La nueva contraseña debe tener al menos 8 caracteres.');
            }

            if ($passwordNueva !== $passwordConfirmacion) {
                http_response_code(400);
                $this->responderPerfil(false, 'Las nuevas contraseñas no coinciden.');
            }

            $password = $passwordNueva;
        }

        if (!$this->usuarios->actualizarPerfil($idusuario, $nombre, $email, $password)) {
            http_response_code(500);
            $this->responderPerfil(false, 'No se pudo actualizar el perfil.');
        }

        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['usuario_email'] = $email;
        $this->auditoria->registrar(
            'actualizar_perfil',
            'usuario',
            $idusuario,
            null,
            ['nombre' => $nombre, 'email' => $email, 'cambio_contrasena' => $password !== null]
        );

        $this->responderPerfil(true, 'Perfil actualizado correctamente.', BASE_URL . '/perfil');
    }

    private function responderPerfil($success, $message, $redirect = null)
    {
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => null,
            'redirect' => $redirect
        ]);

        exit;
    }

    /**
     * Registrar usuario
     */
   public function registrar()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            http_response_code(405);

            echo json_encode([
                'success' => false,
                'message' => 'Método no permitido.',
                'data' => null,
                'redirect' => null
            ]);

            exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirmacion = $_POST['password_confirmacion'] ?? '';
        $rol = $_POST['rol'] ?? '';

        if (
            empty($nombre) ||
            empty($email) ||
            empty($password) ||
            empty($passwordConfirmacion) ||
            empty($rol)
        ) {

            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => 'Todos los campos son obligatorios.',
                'data' => null,
                'redirect' => null
            ]);

            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => 'El correo electrónico no es válido.',
                'data' => null,
                'redirect' => null
            ]);

            exit;
        }

        if ($password !== $passwordConfirmacion) {

            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => 'Las contraseñas no coinciden.',
                'data' => null,
                'redirect' => null
            ]);

            exit;
        }

        if (strlen($password) < 8) {

            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => 'La contraseña debe tener al menos 8 caracteres.',
                'data' => null,
                'redirect' => null
            ]);

            exit;
        }

        if (!in_array($rol, ['jugador', 'organizador'])) {

            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => 'El rol seleccionado no es válido.',
                'data' => null,
                'redirect' => null
            ]);

            exit;
        }

        if ($this->usuarios->existeEmail($email)) {

            http_response_code(409);

            echo json_encode([
                'success' => false,
                'message' => 'El correo electrónico ya está registrado.',
                'data' => null,
                'redirect' => null
            ]);

            exit;
        }

        try {

            $this->pdo->beginTransaction();

            $usuarioId = $this->usuarios->crearUsuario(
                $nombre,
                $email,
                $password
            );

            if (!$usuarioId) {
                throw new Exception('No se pudo crear el usuario.');
            }

            $rolData = $this->usuarios->obtenerRolPorNombre($rol);

            if (!$rolData) {
                throw new Exception('El rol seleccionado no existe.');
            }

            $asignado = $this->usuarios->asignarRol(
                $usuarioId,
                $rolData['idrol']
            );

            if (!$asignado) {
                throw new Exception('No se pudo asignar el rol.');
            }

            $this->auditoria->registrar(
                'registrar_usuario',
                'usuario',
                $usuarioId,
                null,
                ['nombre' => $nombre, 'email' => $email, 'rol' => $rol],
                $usuarioId
            );

            $this->pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Registro exitoso. Ahora puedes iniciar sesión.',
                'data' => null,
                'redirect' => BASE_URL . '/login'
            ]);

            exit;

        } catch (Exception $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            error_log($e->getMessage());

            http_response_code(500);

            echo json_encode([
                'success' => false,
                'message' => 'No se pudo completar el registro.',
                'data' => null,
                'redirect' => null
            ]);

            exit;
        }
    }
}