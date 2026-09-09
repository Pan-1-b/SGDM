<?php

class Auditoria
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function registrar($accion, $tabla = null, $registroId = null, $anteriores = null, $nuevos = null, $idusuario = null)
    {
        if ($idusuario === null && session_status() !== PHP_SESSION_NONE) {
            $idusuario = $_SESSION['usuario_id'] ?? null;
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO auditoria
                (idusuario, accion, tabla_afectada, registro_id, datos_anteriores, datos_nuevos, ip, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        return $stmt->execute([
            $idusuario ? (int)$idusuario : null,
            trim($accion),
            $tabla,
            $registroId !== null ? (int)$registroId : null,
            $anteriores === null ? null : json_encode($anteriores, JSON_UNESCAPED_UNICODE),
            $nuevos === null ? null : json_encode($nuevos, JSON_UNESCAPED_UNICODE),
            $_SERVER['REMOTE_ADDR'] ?? null,
            isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : null
        ]);
    }

    public function obtenerTodas($filtro = '')
    {
        $sql = "SELECT
                    a.*,
                    u.nombre AS usuario_nombre,
                    u.email AS usuario_email
                FROM auditoria a
                LEFT JOIN usuario u ON u.idusuario = a.idusuario";
        $params = [];

        if ($filtro !== '') {
            $sql .= " WHERE a.accion LIKE ?
                      OR a.tabla_afectada LIKE ?
                      OR u.nombre LIKE ?
                      OR u.email LIKE ?";
            $valor = '%' . $filtro . '%';
            $params = [$valor, $valor, $valor, $valor];
        }

        $sql .= " ORDER BY a.fecha DESC LIMIT 300";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
