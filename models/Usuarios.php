<?php

class Usuarios
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtener usuario por ID
     */
    public function obtenerUsuarioPorId($idusuario)
    {
        $sql = "SELECT
                    u.idusuario,
                    u.nombre,
                    u.email,
                    u.estado,
                    u.created_at,
                    u.updated_at,
                    r.idrol,
                    r.nombre AS rol
                FROM usuario u
                LEFT JOIN usuario_rol ur
                    ON ur.idusuario = u.idusuario
                LEFT JOIN rol r
                    ON r.idrol = ur.idrol
                WHERE u.idusuario = ?
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idusuario]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener usuario por email
     */
    public function obtenerUsuarioPorEmail($email)
    {
        $sql = "SELECT
                    u.idusuario,
                    u.nombre,
                    u.email,
                    u.contrasena,
                    u.estado,
                    u.created_at,
                    u.updated_at,
                    r.idrol,
                    r.nombre AS rol
                FROM usuario u
                LEFT JOIN usuario_rol ur
                    ON ur.idusuario = u.idusuario
                LEFT JOIN rol r
                    ON r.idrol = ur.idrol
                WHERE u.email = ?
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verificar si un email ya está registrado
     */
    public function existeEmail($email)
    {
        $sql = "SELECT idusuario
                FROM usuario
                WHERE email = ?
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function existeEmailParaOtroUsuario($email, $idusuario)
    {
        $sql = "SELECT idusuario
                FROM usuario
                WHERE email = ?
                AND idusuario <> ?
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email, $idusuario]);

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function actualizarPerfil($idusuario, $nombre, $email, $password = null)
    {
        if ($password !== null) {
            $sql = "UPDATE usuario
                    SET nombre = ?, email = ?, contrasena = ?
                    WHERE idusuario = ?";
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $params = [$nombre, $email, $passwordHash, $idusuario];
        } else {
            $sql = "UPDATE usuario
                    SET nombre = ?, email = ?
                    WHERE idusuario = ?";
            $params = [$nombre, $email, $idusuario];
        }

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Crear usuario
     */
    public function crearUsuario($nombre, $email, $password)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuario (
                    nombre,
                    email,
                    contrasena,
                    estado
                ) VALUES (?, ?, ?, 'activo')";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $nombre,
            $email,
            $passwordHash
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Obtener rol por nombre
     */
    public function obtenerRolPorNombre($nombreRol)
    {
        if ($nombreRol === 'jugador') {
            $nombreRol = 'jugador';
        }

        $sql = "SELECT
                    idrol,
                    nombre
                FROM rol
                WHERE nombre = ?
                AND activo = TRUE
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nombreRol]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Asignar rol a usuario
     */
    public function asignarRol($idusuario, $idrol)
    {
        $sql = "INSERT INTO usuario_rol (
                    idusuario,
                    idrol
                ) VALUES (?, ?)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $idusuario,
            $idrol
        ]);
    }


    /**
     * Actualizar estado del usuario
     */
    public function actualizarEstado($idusuario, $estado)
    {
        $sql = "UPDATE usuario
                SET estado = ?
                WHERE idusuario = ?";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $estado,
            $idusuario
        ]);
    }

    /**
     * Obtener todos los usuarios
     */
    public function obtenerUsuarios()
    {
        $sql = "SELECT
                    u.idusuario,
                    u.nombre,
                    u.email,
                    u.estado,
                    u.created_at,
                    u.updated_at,
                    r.idrol,
                    r.nombre AS rol
                FROM usuario u
                LEFT JOIN usuario_rol ur
                    ON ur.idusuario = u.idusuario
                LEFT JOIN rol r
                    ON r.idrol = ur.idrol
                ORDER BY u.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerUsuariosAdministracion($filtro = '', $rol = '')
    {
        $sql = "SELECT
                    u.idusuario,
                    u.nombre,
                    u.email,
                    u.estado,
                    u.created_at,
                    u.updated_at,
                    r.nombre AS rol
                FROM usuario u
                INNER JOIN usuario_rol ur ON ur.idusuario = u.idusuario
                INNER JOIN rol r ON r.idrol = ur.idrol
                WHERE r.nombre IN ('jugador', 'organizador')";
        $params = [];

        if ($filtro !== '') {
            $sql .= " AND (u.nombre LIKE ? OR u.email LIKE ?)";
            $valor = '%' . $filtro . '%';
            $params[] = $valor;
            $params[] = $valor;
        }

        if ($rol !== '' && in_array($rol, ['jugador', 'organizador'], true)) {
            $sql .= " AND r.nombre = ?";
            $params[] = $rol;
        }

        $sql .= " ORDER BY u.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerUsuarioAdministrable($idusuario)
    {
        $sql = "SELECT
                    u.idusuario, u.nombre, u.email, u.estado,
                    r.nombre AS rol
                FROM usuario u
                INNER JOIN usuario_rol ur ON ur.idusuario = u.idusuario
                INNER JOIN rol r ON r.idrol = ur.idrol
                WHERE u.idusuario = ?
                AND r.nombre IN ('jugador', 'organizador')
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$idusuario]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


      /**
     * Actualizar último acceso
     */
    public function actualizarUltimoAcceso($usuarioId)
    {
        $sql = "UPDATE usuario
                SET ultimo_acceso_at = CURRENT_TIMESTAMP
                WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $usuarioId
        ]);
    }

    public function obtenerParticipantes($termino = '')
    {
        $sql = "SELECT
                    u.idusuario,
                    u.nombre,
                    u.email,
                    r.nombre AS rol
                FROM usuario u
                INNER JOIN usuario_rol ur
                    ON ur.idusuario = u.idusuario
                INNER JOIN rol r
                    ON r.idrol = ur.idrol
                WHERE u.estado = 'activo'
                AND r.nombre = 'jugador'
                AND (:termino_filtro = ''
                     OR u.nombre LIKE :termino_nombre
                     OR u.email LIKE :termino_email)
                ORDER BY u.nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $terminoSql = '%' . trim($termino) . '%';
        $stmt->bindValue(':termino_filtro', trim($termino), PDO::PARAM_STR);
        $stmt->bindValue(':termino_nombre', $terminoSql, PDO::PARAM_STR);
        $stmt->bindValue(':termino_email', $terminoSql, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerRangoParticipantes($idusuario)
    {
        $sql = "SELECT
                    u.idusuario,
                    u.nombre,
                    u.email
                FROM usuario u
                INNER JOIN usuario_rol ur
                    ON ur.idusuario = u.idusuario
                INNER JOIN rol r
                    ON r.idrol = ur.idrol
                WHERE u.idusuario <> ?
                AND u.estado = 'activo'
                AND r.nombre = 'jugador'
                ORDER BY u.nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idusuario]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
