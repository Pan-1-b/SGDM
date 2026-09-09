<?php

class Equipos
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function crearEquipo($idcreador, $nombre, $idcategoria)
    {
        $nombre = trim((string)$nombre);

        if ($nombre === '' || (int)$idcategoria <= 0) {
            return false;
        }

        $sql = "INSERT INTO equipo (
                    nombre,
                    idcategoria,
                    idcreador,
                    idcapitan,
                    estado
                ) VALUES (?, ?, ?, ?, 'activo')";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $nombre,
            (int)$idcategoria,
            (int)$idcreador,
            (int)$idcreador
        ]) ? (int)$this->pdo->lastInsertId() : false;
    }

    public function obtenerMiEquipoPorId($idequipo, $idusuario)
    {
        $sql = "SELECT e.*
                FROM equipo e
                WHERE e.idequipo = ?
                AND (
                    e.idcreador = ?
                    OR e.idcapitan = ?
                    OR EXISTS (
                        SELECT 1
                        FROM integra i
                        WHERE i.idequipo = e.idequipo
                        AND i.idusuario = ?
                        AND i.estado = 'activo'
                    )
                )
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idequipo, $idusuario, $idusuario, $idusuario]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerEquiposPorUsuario($idusuario)
    {
        $sql = "SELECT
                    e.idequipo,
                    e.nombre,
                    e.idcategoria,
                    e.idcreador,
                    e.idcapitan,
                    e.estado,
                    e.created_at,
                    c.nombre AS categoria,
                    creador.nombre AS creador,
                    capitan.nombre AS capitan
                FROM equipo e
                INNER JOIN categoria c
                    ON c.idcategoria = e.idcategoria
                INNER JOIN usuario creador
                    ON creador.idusuario = e.idcreador
                INNER JOIN usuario capitan
                    ON capitan.idusuario = e.idcapitan
                WHERE e.idcreador = ?
                   OR e.idcapitan = ?
                   OR EXISTS (
                        SELECT 1
                        FROM integra i
                        WHERE i.idequipo = e.idequipo
                        AND i.idusuario = ?
                        AND i.estado = 'activo'
                   )
                GROUP BY e.idequipo
                ORDER BY e.nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idusuario, $idusuario, $idusuario]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarEquiposDeUsuario($idusuario)
    {
        $sql = "SELECT COUNT(DISTINCT e.idequipo)
                FROM equipo e
                LEFT JOIN integra i
                    ON i.idequipo = e.idequipo
                    AND i.idusuario = ?
                    AND i.estado = 'activo'
                WHERE e.estado = 'activo'
                AND (
                    e.idcreador = ?
                    OR e.idcapitan = ?
                    OR i.idusuario IS NOT NULL
                )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            (int)$idusuario,
            (int)$idusuario,
            (int)$idusuario
        ]);

        return (int)$stmt->fetchColumn();
    }

    public function obtenerEquiposDisponibles($idusuario, $termino = '')
    {
        $sql = "SELECT
                    e.idequipo,
                    e.nombre,
                    e.estado,
                    c.nombre AS categoria,
                    creador.nombre AS creador,
                    integracion.estado AS integracion_estado,
                    integracion.origen_solicitud AS integracion_origen,
                    integracion.solicitado_por AS integracion_solicitado_por,
                    EXISTS (
                        SELECT 1
                        FROM equipo existente
                        LEFT JOIN integra miembro
                            ON miembro.idequipo = existente.idequipo
                            AND miembro.idusuario = :idusuario_conflicto
                            AND miembro.estado = 'activo'
                        WHERE existente.idcategoria = e.idcategoria
                        AND existente.idequipo <> e.idequipo
                        AND existente.estado = 'activo'
                        AND (
                            existente.idcreador = :idusuario_creador_conflicto
                            OR existente.idcapitan = :idusuario_capitan_conflicto
                            OR miembro.idusuario IS NOT NULL
                        )
                    ) AS conflicto_categoria
                FROM equipo e
                INNER JOIN categoria c
                    ON c.idcategoria = e.idcategoria
                INNER JOIN usuario creador
                    ON creador.idusuario = e.idcreador
                LEFT JOIN integra integracion
                    ON integracion.idequipo = e.idequipo
                    AND integracion.idusuario = :idusuario_relacion
                WHERE e.estado = 'activo'
                AND e.idcreador <> :idusuario_creador
                AND (:termino_filtro = '' OR e.nombre LIKE :termino_busqueda)
                ORDER BY e.nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $terminoSql = '%' . trim($termino) . '%';
        $stmt->bindValue(':idusuario_relacion', $idusuario, PDO::PARAM_INT);
        $stmt->bindValue(':idusuario_creador', $idusuario, PDO::PARAM_INT);
        $stmt->bindValue(':idusuario_conflicto', $idusuario, PDO::PARAM_INT);
        $stmt->bindValue(':idusuario_creador_conflicto', $idusuario, PDO::PARAM_INT);
        $stmt->bindValue(':idusuario_capitan_conflicto', $idusuario, PDO::PARAM_INT);
        $stmt->bindValue(':termino_filtro', trim($termino), PDO::PARAM_STR);
        $stmt->bindValue(':termino_busqueda', $terminoSql, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function tieneConflictoDeCategoria($idusuario, $idequipo)
    {
        $sql = "SELECT 1
                FROM equipo objetivo
                INNER JOIN equipo existente
                    ON existente.idcategoria = objetivo.idcategoria
                    AND existente.idequipo <> objetivo.idequipo
                    AND existente.estado = 'activo'
                LEFT JOIN integra miembro
                    ON miembro.idequipo = existente.idequipo
                    AND miembro.idusuario = ?
                    AND miembro.estado = 'activo'
                WHERE objetivo.idequipo = ?
                AND objetivo.estado = 'activo'
                AND (
                    existente.idcreador = ?
                    OR existente.idcapitan = ?
                    OR miembro.idusuario IS NOT NULL
                )
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            (int)$idusuario,
            (int)$idequipo,
            (int)$idusuario,
            (int)$idusuario
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function obtenerEquipoPorId($idequipo)
    {
        $sql = "SELECT
                    e.idequipo,
                    e.nombre,
                    e.estado,
                    e.idcategoria,
                    c.nombre AS categoria,
                    creador.idusuario AS idcreador,
                    creador.nombre AS creador,
                    capitan.idusuario AS idcapitan,
                    capitan.nombre AS capitan
                FROM equipo e
                INNER JOIN categoria c
                    ON c.idcategoria = e.idcategoria
                INNER JOIN usuario creador
                    ON creador.idusuario = e.idcreador
                INNER JOIN usuario capitan
                    ON capitan.idusuario = e.idcapitan
                WHERE e.idequipo = ?
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idequipo]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerRelacionesGestionables($idusuario)
    {
        $sql = "SELECT
                    i.idusuario,
                    i.idequipo,
                    i.origen_solicitud,
                    i.solicitado_por,
                    i.estado,
                    e.nombre AS nombre_equipo
                FROM integra i
                INNER JOIN equipo e
                    ON e.idequipo = i.idequipo
                WHERE i.solicitado_por = ?
                   OR e.idcreador = ?
                   OR e.idcapitan = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idusuario, $idusuario, $idusuario]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarEquipo($idequipo, $idcreador, $nombre, $idcategoria, $estado)
    {
        $sql = "UPDATE equipo
                SET nombre = ?,
                    idcategoria = ?,
                    estado = ?
                WHERE idequipo = ?
                AND idcreador = ?";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            trim($nombre),
            (int)$idcategoria,
            $estado,
            (int)$idequipo,
            (int)$idcreador
        ]);
    }

    public function asignarCapitan($idequipo, $idcreador, $idcapitan)
    {
        $sql = "UPDATE equipo e
                SET e.idcapitan = ?
                WHERE e.idequipo = ?
                AND e.idcreador = ?
                AND (
                    e.idcreador = ?
                    OR EXISTS (
                        SELECT 1
                        FROM integra i
                        WHERE i.idequipo = e.idequipo
                        AND i.idusuario = ?
                        AND i.estado = 'activo'
                    )
                )";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            (int)$idcapitan,
            (int)$idequipo,
            (int)$idcreador,
            (int)$idcapitan,
            (int)$idcapitan
        ]);
    }

    public function puedeAdministrarEquipo($idequipo, $idusuario)
    {
        $sql = "SELECT 1
                FROM equipo
                WHERE idequipo = ?
                AND (idcreador = ? OR idcapitan = ?)
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$idequipo, (int)$idusuario, (int)$idusuario]);

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function obtenerMiembrosEquipo($idequipo)
    {
        $sql = "SELECT
                    u.idusuario,
                    u.nombre,
                    u.email,
                    i.estado,
                    i.fecha_ingreso
                FROM integra i
                INNER JOIN usuario u
                    ON u.idusuario = i.idusuario
                WHERE i.idequipo = ?
                AND i.estado = 'activo'
                ORDER BY u.nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idequipo]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSolicitudesPendientes($idequipo)
    {
        $sql = "SELECT
                    i.idusuario,
                    u.nombre,
                    u.email,
                    i.estado,
                    i.fecha_solicitud
                FROM integra i
                INNER JOIN usuario u
                    ON u.idusuario = i.idusuario
                WHERE i.idequipo = ?
                AND i.estado = 'pendiente'
                AND i.origen_solicitud = 'solicitud'
                ORDER BY i.fecha_solicitud DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idequipo]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerInvitacionesPendientes($idusuario)
    {
        $sql = "SELECT
                    i.idequipo,
                    e.nombre AS nombre_equipo,
                    solicitante.nombre AS invitado_por,
                    i.estado,
                    i.fecha_solicitud
                FROM integra i
                INNER JOIN equipo e
                    ON e.idequipo = i.idequipo
                INNER JOIN usuario solicitante
                    ON solicitante.idusuario = i.solicitado_por
                WHERE i.idusuario = ?
                AND i.estado = 'pendiente'
                AND i.origen_solicitud = 'invitacion'
                ORDER BY i.fecha_solicitud DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idusuario]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarInvitacionesPendientes($idusuario)
    {
        $sql = "SELECT COUNT(*)
                FROM integra
                WHERE idusuario = ?
                AND estado = 'pendiente'
                AND origen_solicitud = 'invitacion'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$idusuario]);

        return (int)$stmt->fetchColumn();
    }

    public function contarSolicitudesPendientesCreador($idusuario)
    {
        $sql = "SELECT COUNT(*)
                FROM integra i
                INNER JOIN equipo e ON e.idequipo = i.idequipo
                WHERE e.idcreador = ?
                AND i.estado = 'pendiente'
                AND i.origen_solicitud = 'solicitud'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$idusuario]);

        return (int)$stmt->fetchColumn();
    }

    public function existeIntegracion($idusuario, $idequipo)
    {
        $sql = "SELECT 1
                FROM integra
                WHERE idusuario = ?
                AND idequipo = ?
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idusuario, $idequipo]);

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function solicitarUnion($idusuario, $idequipo)
    {
        if ($this->tieneConflictoDeCategoria($idusuario, $idequipo)) {
            return false;
        }

        $sql = "UPDATE integra
                SET fecha_solicitud = CURRENT_TIMESTAMP,
                    fecha_ingreso = NULL,
                    origen_solicitud = 'solicitud',
                    solicitado_por = ?,
                    estado = 'pendiente'
                WHERE idusuario = ?
                AND idequipo = ?
                AND estado = 'inactivo'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$idusuario, (int)$idusuario, (int)$idequipo]);

        if ($stmt->rowCount() > 0) {
            return true;
        }

        if ($this->existeIntegracion($idusuario, $idequipo)) {
            return false;
        }

        $sql = "INSERT INTO integra (
                    idusuario,
                    idequipo,
                    origen_solicitud,
                    solicitado_por,
                    estado
                ) VALUES (?, ?, 'solicitud', ?, 'pendiente')";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([$idusuario, $idequipo, $idusuario]);
    }

    public function invitarUsuario($idcapitan, $idequipo, $idusuario)
    {
        $equipo = $this->obtenerEquipoPorId($idequipo);

        if (
            !$equipo
            || (int)$idusuario === (int)$idcapitan
            || ((int)$equipo['idcapitan'] !== (int)$idcapitan && (int)$equipo['idcreador'] !== (int)$idcapitan)
        ) {
            return false;
        }

        if ($this->tieneConflictoDeCategoria($idusuario, $idequipo)) {
            return false;
        }

        $sql = "UPDATE integra
                SET fecha_solicitud = CURRENT_TIMESTAMP,
                    fecha_ingreso = NULL,
                    origen_solicitud = 'invitacion',
                    solicitado_por = ?,
                    estado = 'pendiente'
                WHERE idusuario = ?
                AND idequipo = ?
                AND estado = 'inactivo'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$idcapitan, (int)$idusuario, (int)$idequipo]);

        if ($stmt->rowCount() > 0) {
            return true;
        }

        if ($this->existeIntegracion($idusuario, $idequipo)) {
            return false;
        }

        $sql = "INSERT INTO integra (
                    idusuario,
                    idequipo,
                    origen_solicitud,
                    solicitado_por,
                    estado
                ) VALUES (?, ?, 'invitacion', ?, 'pendiente')";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([$idusuario, $idequipo, $idcapitan]);
    }

    public function responderSolicitud($idusuario, $idequipo, $estado, $idcapitan)
    {
        $equipo = $this->obtenerEquipoPorId($idequipo);

        if (!$equipo || ((int)$equipo['idcapitan'] !== (int)$idcapitan && (int)$equipo['idcreador'] !== (int)$idcapitan)) {
            return false;
        }

        $estado = in_array($estado, ['activo', 'inactivo'], true) ? $estado : 'inactivo';
        if ($estado === 'activo' && $this->tieneConflictoDeCategoria($idusuario, $idequipo)) {
            return false;
        }

        $sql = "UPDATE integra
                SET estado = ?,
                    fecha_ingreso = CASE WHEN ? = 'activo' THEN CURRENT_TIMESTAMP ELSE NULL END
                WHERE idequipo = ?
                AND idusuario = ?
                AND origen_solicitud = 'solicitud'
                AND estado = 'pendiente'";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([$estado, $estado, $idequipo, $idusuario]);
    }

    public function responderInvitacion($idusuario, $idequipo, $estado)
    {
        $estado = in_array($estado, ['activo', 'inactivo'], true) ? $estado : 'inactivo';
        if ($estado === 'activo' && $this->tieneConflictoDeCategoria($idusuario, $idequipo)) {
            return false;
        }

        $sql = "UPDATE integra
                SET estado = ?,
                    fecha_ingreso = CASE WHEN ? = 'activo' THEN CURRENT_TIMESTAMP ELSE NULL END
                WHERE idequipo = ?
                AND idusuario = ?
                AND origen_solicitud = 'invitacion'
                AND estado = 'pendiente'";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([$estado, $estado, $idequipo, $idusuario]);
    }

    public function cancelarInvitacion($idusuario, $idequipo, $solicitadoPor)
    {
        $sql = "UPDATE integra
                SET estado = 'inactivo'
                WHERE idusuario = ?
                AND idequipo = ?
                AND solicitado_por = ?
                AND origen_solicitud = 'invitacion'
                AND estado = 'pendiente'";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            (int)$idusuario,
            (int)$idequipo,
            (int)$solicitadoPor
        ]);
    }

    public function cancelarSolicitud($idusuario, $idequipo)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE integra
             SET estado = 'inactivo'
             WHERE idusuario = ? AND idequipo = ?
             AND origen_solicitud = 'solicitud' AND estado = 'pendiente'"
        );
        return $stmt->execute([(int)$idusuario, (int)$idequipo]);
    }

    public function salirEquipo($idusuario, $idequipo)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE integra i
             INNER JOIN equipo e ON e.idequipo = i.idequipo
             SET i.estado = 'inactivo'
             WHERE i.idusuario = ? AND i.idequipo = ?
             AND i.estado = 'activo' AND e.idcreador <> ?"
        );
        return $stmt->execute([(int)$idusuario, (int)$idequipo, (int)$idusuario]);
    }

    public function eliminarMiembro($idcreador, $idequipo, $idusuario)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE integra i
             INNER JOIN equipo e ON e.idequipo = i.idequipo
             SET i.estado = 'inactivo'
             WHERE e.idcreador = ? AND i.idequipo = ?
             AND i.idusuario = ? AND i.estado = 'activo'
             AND i.idusuario <> e.idcreador"
        );
        return $stmt->execute([(int)$idcreador, (int)$idequipo, (int)$idusuario]);
    }
}
