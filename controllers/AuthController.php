<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/../models/Auditoria.php';

class AuthController
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
     * Mostrar formulario de login
     */
    public function login()
    {
        require_once __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Procesar login
     */
    public function autenticar()
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

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        /*
         * Validar campos
         */
        if (empty($email) || empty($password)) {

            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => 'Debes ingresar correo y contraseña.',
                'data' => null,
                'redirect' => null
            ]);

            exit;
        }

        /*
         * Validar formato del correo
         */
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

        /*
         * Buscar usuario
         */
        $usuario = $this->usuarios->obtenerUsuarioPorEmail($email);

        /*
         * Usuario inexistente
         */
        if (!$usuario) {

            http_response_code(401);

            $this->auditoria->registrar('login_fallido', 'usuario', null, null, ['email' => $email, 'motivo' => 'usuario_no_encontrado']);

            echo json_encode([
                'success' => false,
                'message' => 'Correo o contraseña incorrectos.',
                'data' => null,
                'redirect' => null
            ]);

            exit;
        }

        /*
         * Comprobar estado
         */
        if ($usuario['estado'] !== 'activo') {

            http_response_code(403);

            $this->auditoria->registrar('login_bloqueado', 'usuario', $usuario['idusuario'], null, ['email' => $email, 'estado' => $usuario['estado']], $usuario['idusuario']);

            echo json_encode([
                'success' => false,
                'message' => 'Tu cuenta no se encuentra activa.',
                'data' => null,
                'redirect' => null
            ]);

            exit;
        }

        /*
         * Comprobar contraseña
         *
         * En sgdm2 el campo se llama:
         * contrasena
         */
        if (!password_verify($password, $usuario['contrasena'])) {

            http_response_code(401);

            $this->auditoria->registrar('login_fallido', 'usuario', $usuario['idusuario'], null, ['email' => $email, 'motivo' => 'contrasena_incorrecta'], $usuario['idusuario']);

            echo json_encode([
                'success' => false,
                'message' => 'Correo o contraseña incorrectos.',
                'data' => null,
                'redirect' => null
            ]);

            exit;
        }

        /*
         * Regenerar ID de sesión
         */
        session_regenerate_id(true);

        /*
         * Guardar información del usuario
         *
         * Campos correspondientes a sgdm2:
         *
         * idusuario
         * nombre
         * email
         * rol
         */
        $_SESSION['usuario_id'] = $usuario['idusuario'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
        $this->auditoria->registrar('inicio_sesion', 'usuario', $usuario['idusuario'], null, [
            'rol' => $usuario['rol']
        ], $usuario['idusuario']);

        /*
         * Login exitoso
         */
        echo json_encode([
            'success' => true,
            'message' => 'Autenticación exitosa. Bienvenido a HERMES.',
            'data' => null,
            'redirect' => BASE_URL . '/dashboard'
        ]);

        exit;
    }

    /**
     * Cerrar sesión
     */
   public function logout()
{
    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {

        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();

    echo json_encode([
        'success' => true,
        'message' => 'Sesión cerrada correctamente.',
        'data' => null,
        'redirect' => BASE_URL . '/login'
    ]);

    exit;
}
}