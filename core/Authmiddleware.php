<?php

class AuthMiddleware
{
    public static function verificar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            header('Location:'. BASE_URL . '/login');
            exit;
        }
    }

    public static function tieneRol($rol)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_rol'])) {
            return false;
        }

        $rolActual = $_SESSION['usuario_rol'];

        if ($rolActual === $rol) {
            return true;
        }

        $aliasRoles = [
            'jugador' => ['participante'],
            'participante' => ['jugador'],
            'organizador' => ['organizador'],
            'administrador' => ['administrador']
        ];

        return isset($aliasRoles[$rol])
            && in_array($rolActual, $aliasRoles[$rol], true);
    }

    public static function verificarRol($rol)
    {
        self::verificar();

        if (!self::tieneRol($rol)) {
            http_response_code(403);
            echo "No tienes permisos para acceder a esta sección.";
            exit;
        }
    }
}