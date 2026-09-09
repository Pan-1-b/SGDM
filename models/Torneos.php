<?php

class Torneos
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /*
    |--------------------------------------------------------------------------
    | OBTENER TORNEOS RECIENTES
    |--------------------------------------------------------------------------
    */

    public function obtenerTorneosRecientes()
    {
        $sql = "SELECT 
                    t.idtorneo,
                    t.nombretorneo,
                    t.tipo,
                    t.modalidad,
                    t.descripcion,
                    t.fechainicio,
                    t.fechafin,
                    t.horainicio,
                    t.idorganizador,
                    u.nombre AS organizador,
                    t.estado
                FROM torneo t
                INNER JOIN usuario u
                    ON u.idusuario = t.idorganizador
                WHERE t.estado IN ('inscripciones', 'en_curso')
                ORDER BY t.fechainicio ASC
                LIMIT 2";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /*
    |--------------------------------------------------------------------------
    | OBTENER TORNEOS PÚBLICOS
    |--------------------------------------------------------------------------
    */

    public function obtenerTorneosPublicos()
    {
        $sql = "SELECT 
                    t.idtorneo,
                    t.nombretorneo,
                    t.tipo,
                    t.modalidad,
                    t.descripcion,
                    t.fechainicio,
                    t.fechafin,
                    t.horainicio,
                    t.idorganizador,
                    u.nombre AS organizador,
                    t.estado
                FROM torneo t
                INNER JOIN usuario u
                    ON u.idusuario = t.idorganizador
                WHERE t.estado IN ('inscripciones', 'en_curso')
                ORDER BY t.fechainicio ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTorneosPorOrganizador($idorganizador)
    {
        $sql = "SELECT
                    t.idtorneo,
                    t.nombretorneo,
                    t.tipo,
                    t.modalidad,
                    t.descripcion,
                    t.fechainicio,
                    t.fechafin,
                    t.horainicio,
                    t.idorganizador,
                    u.nombre AS organizador,
                    t.estado
                FROM torneo t
                INNER JOIN usuario u
                    ON u.idusuario = t.idorganizador
                WHERE t.idorganizador = ?
                ORDER BY t.fechainicio ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idorganizador]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarTorneosPorOrganizador($idorganizador)
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM torneo
             WHERE idorganizador = ?"
        );
        $stmt->execute([(int)$idorganizador]);

        return (int)$stmt->fetchColumn();
    }

    public function esPropietario($idtorneo, $idorganizador)
    {
        $sql = "SELECT idtorneo
                FROM torneo
                WHERE idtorneo = ?
                AND idorganizador = ?
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idtorneo, $idorganizador]);

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function obtenerSolicitudesPorOrganizador($idorganizador)
    {
        $sql = "SELECT
            i.idinscripcion,
            i.idtorneo,
                    t.nombretorneo,
                    i.idusuario,
                    i.idequipo,
                    COALESCE(e.nombre, u.nombre, 'Usuario sin nombre') AS solicitante,
                    i.estado,
                    i.fecha_solicitud,
                    i.fecha_respuesta
                FROM inscribe i
                INNER JOIN torneo t
                    ON t.idtorneo = i.idtorneo
                LEFT JOIN usuario u
                    ON u.idusuario = i.idusuario
                LEFT JOIN equipo e
                    ON e.idequipo = i.idequipo
                WHERE t.idorganizador = ?
                ORDER BY i.fecha_solicitud DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idorganizador]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarSolicitudesPendientes($idorganizador)
    {
        $sql = "SELECT COUNT(*) AS total
                FROM inscribe i
                INNER JOIN torneo t
                    ON t.idtorneo = i.idtorneo
                WHERE t.idorganizador = ?
                AND i.estado = 'pendiente'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$idorganizador]);

        return (int)$stmt->fetchColumn();
    }

    public function obtenerResumenUsuario($idusuario)
    {
        $sql = "SELECT
                    COUNT(DISTINCT CASE WHEN i.estado = 'aprobada' THEN i.idtorneo END) AS torneos,
                    COUNT(CASE WHEN i.estado = 'pendiente' THEN 1 END) AS inscripciones_pendientes
                FROM inscribe i
                LEFT JOIN equipo e
                    ON e.idequipo = i.idequipo
                LEFT JOIN integra miembro
                    ON miembro.idequipo = e.idequipo
                    AND miembro.idusuario = ?
                    AND miembro.estado = 'activo'
                WHERE (
                    i.idusuario = ?
                    OR (
                        i.idequipo IS NOT NULL
                        AND e.estado = 'activo'
                        AND (
                            e.idcreador = ?
                            OR e.idcapitan = ?
                            OR miembro.idusuario IS NOT NULL
                        )
                    )
                )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            (int)$idusuario,
            (int)$idusuario,
            (int)$idusuario,
            (int)$idusuario
        ]);

        $resumen = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        return [
            'torneos' => (int)($resumen['torneos'] ?? 0),
            'inscripciones_pendientes' => (int)($resumen['inscripciones_pendientes'] ?? 0)
        ];
    }

    public function actualizarEstadoInscripcion($idinscripcion, $idorganizador, $estado)
    {
        $estadosPermitidos = ['pendiente', 'aprobada', 'rechazada', 'cancelada'];

        if (!in_array($estado, $estadosPermitidos, true)) {
            return false;
        }

        $sql = "UPDATE inscribe i
                INNER JOIN torneo t
                    ON t.idtorneo = i.idtorneo
                SET i.estado = ?,
                    i.fecha_respuesta = CASE
                        WHEN ? = 'pendiente' THEN NULL
                        ELSE CURRENT_TIMESTAMP
                    END
                WHERE i.idinscripcion = ?
                AND t.idorganizador = ?";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $estado,
            $estado,
            (int)$idinscripcion,
            (int)$idorganizador
        ]);
    }

    public function obtenerTorneoPorId($idtorneo)
    {
        $sql = "SELECT 
                    t.idtorneo,
                    t.nombretorneo,
                    t.tipo,
                    t.modalidad,
                    t.idcategoria,
                    t.descripcion,
                    t.fechainicio,
                    t.fechafin,
                    t.horainicio,
                    t.idorganizador,
                    u.nombre AS organizador,
                    t.estado
                FROM torneo t
                INNER JOIN usuario u
                    ON u.idusuario = t.idorganizador
                WHERE t.idtorneo = ?
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idtorneo]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerParticipantesAprobados($idtorneo)
    {
        $sql = "SELECT
                    u.idusuario,
                    u.nombre,
                    u.email,
                    i.fecha_respuesta
                FROM inscribe i
                INNER JOIN usuario u
                    ON u.idusuario = i.idusuario
                WHERE i.idtorneo = ?
                AND i.estado = 'aprobada'
                ORDER BY u.nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$idtorneo]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEquiposInscritosAprobados($idtorneo)
    {
        $sql = "SELECT
                    e.idequipo,
                    e.nombre
                FROM inscribe i
                INNER JOIN equipo e
                    ON e.idequipo = i.idequipo
                WHERE i.idtorneo = ?
                AND i.estado = 'aprobada'
                ORDER BY e.nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$idtorneo]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerIntegrantesEquipoInscrito($idequipo)
    {
        $sql = "SELECT
                    u.idusuario,
                    u.nombre,
                    u.email
                FROM equipo e
                INNER JOIN usuario u
                    ON u.idusuario = e.idcreador
                WHERE e.idequipo = ?
                UNION
                SELECT
                    u.idusuario,
                    u.nombre,
                    u.email
                FROM integra i
                INNER JOIN usuario u
                    ON u.idusuario = i.idusuario
                WHERE i.idequipo = ?
                AND i.estado = 'activo'
                ORDER BY nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$idequipo, (int)$idequipo]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCategorias()
    {
        $sql = "SELECT
                    idcategoria,
                    nombre,
                    descripcion
                FROM categoria
                WHERE activo = 1
                ORDER BY nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /*
    |--------------------------------------------------------------------------
    | CREAR TORNEO
    |--------------------------------------------------------------------------
    */

    public function crearTorneo(
        $nombre,
        $tipo,
        $modalidad,
        $idcategoria,
        $descripcion,
        $fechainicio,
        $fechafin,
        $horainicio,
        $idorganizador
    ) {
        $sql = "INSERT INTO torneo (
                    nombretorneo,
                    tipo,
                    modalidad,
                    idcategoria,
                    descripcion,
                    fechainicio,
                    fechafin,
                    horainicio,
                    idorganizador,
                    estado
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, 'inscripciones'
                )";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $nombre,
            $tipo,
            $modalidad,
            $idcategoria,
            $descripcion,
            $fechainicio,
            $fechafin,
            $horainicio,
            $idorganizador
        ]);

        return $this->pdo->lastInsertId();
    }


    /*
    |--------------------------------------------------------------------------
    | MODIFICAR TORNEO
    |--------------------------------------------------------------------------
    */

    public function modificarTorneo(
        $idtorneo,
        $idorganizador,
        $nombre,
        $tipo,
        $modalidad,
        $idcategoria,
        $descripcion,
        $fechainicio,
        $fechafin,
        $horainicio
    ) {
        $sql = "UPDATE torneo
                SET
                    nombretorneo = ?,
                    tipo = ?,
                    modalidad = ?,
                    idcategoria = ?,
                    descripcion = ?,
                    fechainicio = ?,
                    fechafin = ?,
                    horainicio = ?
                WHERE idtorneo = ?
                AND idorganizador = ?";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $nombre,
            $tipo,
            $modalidad,
            $idcategoria,
            $descripcion,
            $fechainicio,
            $fechafin,
            $horainicio,
            $idtorneo,
            $idorganizador
        ]);
    }

    public function actualizarEstadoTorneo($idtorneo, $idorganizador, $estado)
    {
        $sql = "UPDATE torneo
                SET estado = ?
                WHERE idtorneo = ?
                AND idorganizador = ?";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $estado,
            $idtorneo,
            $idorganizador
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ELIMINAR TORNEO
    |--------------------------------------------------------------------------
    |
    | No se elimina físicamente de la base de datos.
    | Simplemente se cambia su estado.
    |
    */

    public function eliminarTorneo($idtorneo, $idorganizador)
    {
        $sql = "UPDATE torneo
                SET estado = 'cancelado'
                WHERE idtorneo = ?
                AND idorganizador = ?";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $idtorneo,
            $idorganizador
        ]);
    }

    public function obtenerTorneosEnInscripciones()
    {
        $sql = "SELECT
                    t.idtorneo,
                    t.nombretorneo,
                    t.tipo,
                    t.modalidad,
                    t.descripcion,
                    t.fechainicio,
                    t.fechafin,
                    t.horainicio,
                    t.estado,
                    u.nombre AS organizador
                FROM torneo t
                INNER JOIN usuario u
                    ON u.idusuario = t.idorganizador
                WHERE t.estado = 'inscripciones'
                ORDER BY t.fechainicio ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerMiInscripcion($idtorneo, $idusuario)
    {
        $sql = "SELECT *
                FROM inscribe
                WHERE idtorneo = ?
                AND idusuario = ?
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idtorneo, $idusuario]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerInscripcionesPorUsuario($idusuario)
    {
        $sql = "SELECT
                    i.idinscripcion,
                    i.idtorneo,
                    t.nombretorneo,
                    t.modalidad,
                    i.estado,
                    i.fecha_solicitud
                FROM inscribe i
                INNER JOIN torneo t
                    ON t.idtorneo = i.idtorneo
                WHERE i.idusuario = ?
                ORDER BY i.fecha_solicitud DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idusuario]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSolicitudesPorUsuario($idusuario)
    {
        $sql = "SELECT
                    i.idinscripcion,
                    i.idtorneo,
                    t.nombretorneo,
                    t.modalidad,
                    COALESCE(u.nombre, e.nombre) AS participante,
                    CASE
                        WHEN i.idusuario IS NOT NULL THEN 'Individual'
                        ELSE 'Equipo'
                    END AS tipo_inscripcion,
                    i.estado,
                    i.fecha_solicitud,
                    i.fecha_respuesta
                FROM inscribe i
                INNER JOIN torneo t
                    ON t.idtorneo = i.idtorneo
                LEFT JOIN usuario u
                    ON u.idusuario = i.idusuario
                LEFT JOIN equipo e
                    ON e.idequipo = i.idequipo
                WHERE i.idusuario = ?
                   OR EXISTS (
                        SELECT 1
                        FROM equipo eu
                        LEFT JOIN integra iu
                            ON iu.idequipo = eu.idequipo
                            AND iu.idusuario = ?
                            AND iu.estado = 'activo'
                        WHERE eu.idequipo = i.idequipo
                        AND (
                            eu.idcreador = ?
                            OR eu.idcapitan = ?
                            OR iu.idusuario IS NOT NULL
                        )
                   )
                ORDER BY i.fecha_solicitud DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idusuario, $idusuario, $idusuario, $idusuario]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function solicitarInscripcionIndividual($idtorneo, $idusuario)
    {
        $yaExiste = $this->obtenerMiInscripcion($idtorneo, $idusuario);

        if ($yaExiste) {
            return false;
        }

        $sql = "INSERT INTO inscribe (
                    idtorneo,
                    idusuario,
                    idequipo,
                    estado
                ) VALUES (?, ?, NULL, 'pendiente')";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([$idtorneo, $idusuario]);
    }

    public function solicitarInscripcionEquipo($idtorneo, $idequipo)
    {
        $sql = "SELECT 1
                FROM inscribe
                WHERE idtorneo = ?
                AND idequipo = ?
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idtorneo, $idequipo]);

        if ($stmt->fetch(PDO::FETCH_ASSOC) !== false) {
            return false;
        }

        $sql = "INSERT INTO inscribe (
                    idtorneo,
                    idusuario,
                    idequipo,
                    estado
                ) VALUES (?, NULL, ?, 'pendiente')";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([$idtorneo, $idequipo]);
    }

    public function obtenerEquiposDelUsuario($idusuario)
    {
        $sql = "SELECT e.idequipo, e.nombre
                FROM equipo e
                WHERE e.idcreador = ?
                OR e.idcapitan = ?
                OR EXISTS (
                    SELECT 1
                    FROM integra i
                    WHERE i.idequipo = e.idequipo
                    AND i.idusuario = ?
                    AND i.estado = 'activo'
                )
                ORDER BY e.nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idusuario, $idusuario, $idusuario]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerRondasGestion($idtorneo, $idorganizador, $filtro = '')
    {
        $sql = "SELECT r.*
                FROM ronda r
                INNER JOIN torneo t ON t.idtorneo = r.idtorneo
                WHERE r.idtorneo = ?
                AND t.idorganizador = ?";
        $params = [(int)$idtorneo, (int)$idorganizador];

        if ($filtro !== '') {
            $sql .= " AND (r.nombre LIKE ? OR CAST(r.numero AS CHAR) LIKE ? OR r.estado = ?)";
            $params[] = '%' . $filtro . '%';
            $params[] = '%' . $filtro . '%';
            $params[] = $filtro;
        }

        $sql .= " ORDER BY r.numero ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPartidosGestion($idtorneo, $idorganizador, $filtro = '')
    {
        $sql = "SELECT
                    p.*,
                    r.numero AS numero_ronda,
                    r.nombre AS nombre_ronda,
                    COALESCE(ul.nombre, el.nombre) AS local_nombre,
                    COALESCE(uv.nombre, ev.nombre) AS visitante_nombre,
                    res.idresultado,
                    res.puntoslocal,
                    res.puntosvisitante,
                    res.idinscripcion_ganador,
                    res.observaciones
                FROM partido p
                INNER JOIN torneo t ON t.idtorneo = p.idtorneo
                INNER JOIN ronda r ON r.idronda = p.idronda
                INNER JOIN inscribe il ON il.idinscripcion = p.idinscripcion_local
                INNER JOIN inscribe iv ON iv.idinscripcion = p.idinscripcion_visitante
                LEFT JOIN usuario ul ON ul.idusuario = il.idusuario
                LEFT JOIN equipo el ON el.idequipo = il.idequipo
                LEFT JOIN usuario uv ON uv.idusuario = iv.idusuario
                LEFT JOIN equipo ev ON ev.idequipo = iv.idequipo
                LEFT JOIN resultado res ON res.idpartido = p.idpartido
                WHERE p.idtorneo = ?
                AND t.idorganizador = ?";
        $params = [(int)$idtorneo, (int)$idorganizador];

        if ($filtro !== '') {
            $sql .= " AND (COALESCE(ul.nombre, el.nombre) LIKE ?
                       OR COALESCE(uv.nombre, ev.nombre) LIKE ?
                       OR r.nombre LIKE ?
                       OR p.estado = ?)";
            $value = '%' . $filtro . '%';
            $params[] = $value;
            $params[] = $value;
            $params[] = $value;
            $params[] = $filtro;
        }

        $sql .= " ORDER BY p.fecha ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPartidosPublicos($idtorneo)
    {
        $sql = "SELECT
                    p.idpartido,
                    p.idronda,
                    p.fecha,
                    p.estado,
                    r.numero AS numero_ronda,
                    r.nombre AS nombre_ronda,
                    COALESCE(ul.nombre, el.nombre) AS local_nombre,
                    COALESCE(uv.nombre, ev.nombre) AS visitante_nombre,
                    res.idresultado,
                    res.puntoslocal,
                    res.puntosvisitante,
                    res.observaciones
                FROM partido p
                INNER JOIN ronda r ON r.idronda = p.idronda
                INNER JOIN inscribe il ON il.idinscripcion = p.idinscripcion_local
                INNER JOIN inscribe iv ON iv.idinscripcion = p.idinscripcion_visitante
                LEFT JOIN usuario ul ON ul.idusuario = il.idusuario
                LEFT JOIN equipo el ON el.idequipo = il.idequipo
                LEFT JOIN usuario uv ON uv.idusuario = iv.idusuario
                LEFT JOIN equipo ev ON ev.idequipo = iv.idequipo
                LEFT JOIN resultado res ON res.idpartido = p.idpartido
                WHERE p.idtorneo = ?
                ORDER BY p.fecha ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$idtorneo]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerRondasPublicas($idtorneo)
    {
        $stmt = $this->pdo->prepare(
            "SELECT idronda, numero, nombre, fechainicio, fechafin, estado
             FROM ronda
             WHERE idtorneo = ?
             ORDER BY numero ASC"
        );
        $stmt->execute([(int)$idtorneo]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerInscripcionesAprobadasParaGestion($idtorneo, $idorganizador)
    {
        $sql = "SELECT
                    i.idinscripcion,
                    COALESCE(u.nombre, e.nombre) AS nombre,
                    CASE WHEN i.idusuario IS NULL THEN 'Equipo' ELSE 'Individual' END AS tipo
                FROM inscribe i
                INNER JOIN torneo t ON t.idtorneo = i.idtorneo
                LEFT JOIN usuario u ON u.idusuario = i.idusuario
                LEFT JOIN equipo e ON e.idequipo = i.idequipo
                WHERE i.idtorneo = ?
                AND t.idorganizador = ?
                AND i.estado = 'aprobada'
                ORDER BY nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$idtorneo, (int)$idorganizador]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearRonda($idtorneo, $idorganizador, $numero, $nombre, $fechainicio, $fechafin, $estado)
    {
        if (!$this->esPropietario($idtorneo, $idorganizador)) {
            return false;
        }

        $sql = "INSERT INTO ronda (idtorneo, numero, nombre, fechainicio, fechafin, estado)
                VALUES (?, ?, ?, NULLIF(?, ''), NULLIF(?, ''), 'pendiente')";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            (int)$idtorneo,
            (int)$numero,
            trim($nombre),
            $fechainicio,
            $fechafin
        ]);
    }

    public function actualizarRonda($idronda, $idtorneo, $idorganizador, $numero, $nombre, $fechainicio, $fechafin, $estado)
    {
        $sql = "UPDATE ronda r
                INNER JOIN torneo t ON t.idtorneo = r.idtorneo
                SET r.numero = ?, r.nombre = ?, r.fechainicio = NULLIF(?, ''),
                    r.fechafin = NULLIF(?, '')
                WHERE r.idronda = ? AND r.idtorneo = ? AND t.idorganizador = ?";
        $stmt = $this->pdo->prepare($sql);

        $actualizado = $stmt->execute([
            (int)$numero, trim($nombre), $fechainicio, $fechafin,
            (int)$idronda, (int)$idtorneo, (int)$idorganizador
        ]);

        if (!$actualizado) {
            return false;
        }

        return $this->sincronizarEstadoRonda($idronda, $idtorneo, $idorganizador);
    }

    public function eliminarRonda($idronda, $idtorneo, $idorganizador)
    {
        $sql = "DELETE r
                FROM ronda r
                INNER JOIN torneo t ON t.idtorneo = r.idtorneo
                WHERE r.idronda = ? AND r.idtorneo = ? AND t.idorganizador = ?
                AND NOT EXISTS (
                    SELECT 1 FROM partido p WHERE p.idronda = r.idronda
                )";
        $stmt = $this->pdo->prepare($sql);

        if (!$stmt->execute([(int)$idronda, (int)$idtorneo, (int)$idorganizador])) {
            return false;
        }

        return $stmt->rowCount() > 0;
    }

    public function crearPartido($idtorneo, $idorganizador, $idronda, $local, $visitante, $fecha, $estado)
    {
        $sql = "INSERT INTO partido
                    (idtorneo, idronda, idinscripcion_local, idinscripcion_visitante, fecha, estado)
                SELECT ?, r.idronda, ?, ?, ?, ?
                FROM ronda r
                INNER JOIN torneo t ON t.idtorneo = r.idtorneo
                INNER JOIN inscribe il ON il.idinscripcion = ?
                INNER JOIN inscribe iv ON iv.idinscripcion = ?
                WHERE r.idronda = ? AND r.idtorneo = ? AND t.idorganizador = ?
                AND il.idtorneo = r.idtorneo AND iv.idtorneo = r.idtorneo
                AND il.estado = 'aprobada' AND iv.estado = 'aprobada'
                AND il.idinscripcion <> iv.idinscripcion";
        $stmt = $this->pdo->prepare($sql);

        $creado = $stmt->execute([
            (int)$idtorneo, (int)$local, (int)$visitante, $fecha, $estado,
            (int)$local, (int)$visitante, (int)$idronda, (int)$idtorneo, (int)$idorganizador
        ]);

        if (!$creado) {
            return false;
        }

        return $this->sincronizarEstadoRonda($idronda, $idtorneo, $idorganizador);
    }

    public function actualizarPartido($idpartido, $idtorneo, $idorganizador, $idronda, $local, $visitante, $fecha, $estado)
    {
        $rondaAnterior = $this->obtenerRondaDePartido($idpartido, $idtorneo, $idorganizador);
        if (!$rondaAnterior) {
            return false;
        }

        $sql = "UPDATE partido p
                INNER JOIN torneo t ON t.idtorneo = p.idtorneo
                SET p.idronda = ?, p.idinscripcion_local = ?, p.idinscripcion_visitante = ?,
                    p.fecha = ?, p.estado = ?
                WHERE p.idpartido = ? AND p.idtorneo = ? AND t.idorganizador = ?
                AND ? <> ?
                AND EXISTS (SELECT 1 FROM inscribe i WHERE i.idinscripcion = ? AND i.idtorneo = p.idtorneo AND i.estado = 'aprobada')
                AND EXISTS (SELECT 1 FROM inscribe i WHERE i.idinscripcion = ? AND i.idtorneo = p.idtorneo AND i.estado = 'aprobada')";
        $stmt = $this->pdo->prepare($sql);

        $actualizado = $stmt->execute([
            (int)$idronda, (int)$local, (int)$visitante, $fecha, $estado,
            (int)$idpartido, (int)$idtorneo, (int)$idorganizador,
            (int)$local, (int)$visitante, (int)$local, (int)$visitante
        ]);

        if (!$actualizado) {
            return false;
        }

        $estadoRondaNueva = $this->sincronizarEstadoRonda($idronda, $idtorneo, $idorganizador);
        $estadoRondaAnterior = (int)$rondaAnterior['idronda'] === (int)$idronda
            ? true
            : $this->sincronizarEstadoRonda($rondaAnterior['idronda'], $idtorneo, $idorganizador);

        return $estadoRondaNueva && $estadoRondaAnterior;
    }

    public function eliminarPartido($idpartido, $idtorneo, $idorganizador)
    {
        $partido = $this->obtenerPartidoGestionable($idpartido, $idtorneo, $idorganizador);
        if (!$partido || $partido['estado'] === 'finalizado') {
            return false;
        }

        $iniciaTransaccion = !$this->pdo->inTransaction();
        if ($iniciaTransaccion) {
            $this->pdo->beginTransaction();
        }

        try {
            $resultadoStmt = $this->pdo->prepare(
                "DELETE FROM resultado
                 WHERE idpartido = ?"
            );
            $resultadoStmt->execute([(int)$idpartido]);

            if ($partido['estado'] !== 'cancelado') {
                $sql = "UPDATE partido p
                        INNER JOIN torneo t ON t.idtorneo = p.idtorneo
                        SET p.estado = 'cancelado'
                        WHERE p.idpartido = ? AND p.idtorneo = ? AND t.idorganizador = ?
                        AND p.estado <> 'finalizado'";
                $stmt = $this->pdo->prepare($sql);
                if (!$stmt->execute([(int)$idpartido, (int)$idtorneo, (int)$idorganizador])) {
                    if ($iniciaTransaccion) {
                        $this->pdo->rollBack();
                    }
                    return false;
                }
            }

            if ($iniciaTransaccion) {
                $this->pdo->commit();
            }
        } catch (PDOException $exception) {
            if ($iniciaTransaccion && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }

        $ronda = $this->obtenerRondaDePartido($idpartido, $idtorneo, $idorganizador);
        return $ronda
            && $this->sincronizarEstadoRonda($ronda['idronda'], $idtorneo, $idorganizador);
    }

    private function obtenerPartidoGestionable($idpartido, $idtorneo, $idorganizador)
    {
        $stmt = $this->pdo->prepare(
            "SELECT p.idronda, p.estado
             FROM partido p
             INNER JOIN torneo t ON t.idtorneo = p.idtorneo
             WHERE p.idpartido = ? AND p.idtorneo = ? AND t.idorganizador = ?"
        );
        $stmt->execute([(int)$idpartido, (int)$idtorneo, (int)$idorganizador]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardarResultado($idpartido, $idtorneo, $idorganizador, $local, $visitante, $ganador, $observaciones, $finalizar = true)
    {
        $partido = $this->obtenerPartidoGestionable($idpartido, $idtorneo, $idorganizador);
        if (
            !$partido
            || $partido['estado'] === 'cancelado'
            || (!$finalizar && !in_array($partido['estado'], ['programado', 'en_curso'], true))
        ) {
            return false;
        }

        $sql = "INSERT INTO resultado
                    (idpartido, puntoslocal, puntosvisitante, idinscripcion_ganador, observaciones)
                SELECT ?, ?, ?, NULLIF(?, 0), ?
                FROM partido p
                INNER JOIN torneo t ON t.idtorneo = p.idtorneo
                WHERE p.idpartido = ? AND p.idtorneo = ? AND t.idorganizador = ?
                AND p.estado <> 'cancelado'
                AND (? = 0 OR ? IN (p.idinscripcion_local, p.idinscripcion_visitante))
                ON DUPLICATE KEY UPDATE
                    puntoslocal = VALUES(puntoslocal),
                    puntosvisitante = VALUES(puntosvisitante),
                    idinscripcion_ganador = VALUES(idinscripcion_ganador),
                    observaciones = VALUES(observaciones)";
        $stmt = $this->pdo->prepare($sql);

        $guardado = $stmt->execute([
            (int)$idpartido, (int)$local, (int)$visitante, (int)$ganador, trim($observaciones),
            (int)$idpartido, (int)$idtorneo, (int)$idorganizador, (int)$ganador, (int)$ganador
        ]);

        if (!$guardado) {
            return false;
        }

        $estadoStmt = $this->pdo->prepare(
            "UPDATE partido p
             INNER JOIN torneo t ON t.idtorneo = p.idtorneo
             SET p.estado = ?
             WHERE p.idpartido = ? AND p.idtorneo = ? AND t.idorganizador = ?
             AND p.estado <> 'cancelado'"
        );
        $estadoStmt->execute([
            $finalizar ? 'finalizado' : 'en_curso',
            (int)$idpartido,
            (int)$idtorneo,
            (int)$idorganizador
        ]);

        $ronda = $this->obtenerRondaDePartido($idpartido, $idtorneo, $idorganizador);
        return $ronda
            && $this->sincronizarEstadoRonda($ronda['idronda'], $idtorneo, $idorganizador);
    }

    public function eliminarResultado($idpartido, $idtorneo, $idorganizador)
    {
        return false;
    }

    private function obtenerRondaDePartido($idpartido, $idtorneo, $idorganizador)
    {
        $stmt = $this->pdo->prepare(
            "SELECT p.idronda
             FROM partido p
             INNER JOIN torneo t ON t.idtorneo = p.idtorneo
             WHERE p.idpartido = ? AND p.idtorneo = ? AND t.idorganizador = ?"
        );
        $stmt->execute([(int)$idpartido, (int)$idtorneo, (int)$idorganizador]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function sincronizarEstadoRonda($idronda, $idtorneo, $idorganizador)
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(p.estado IN ('finalizado', 'cancelado')) AS resueltos,
                SUM(p.estado = 'en_curso') AS en_curso
             FROM partido p
             INNER JOIN ronda r ON r.idronda = p.idronda
             INNER JOIN torneo t ON t.idtorneo = r.idtorneo
             WHERE p.idronda = ? AND r.idtorneo = ? AND t.idorganizador = ?"
        );
        $stmt->execute([(int)$idronda, (int)$idtorneo, (int)$idorganizador]);
        $datos = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$datos) {
            return false;
        }

        $estado = 'pendiente';
        if ((int)$datos['total'] > 0 && (int)$datos['resueltos'] === (int)$datos['total']) {
            $estado = 'finalizada';
        } elseif ((int)$datos['en_curso'] > 0) {
            $estado = 'en_curso';
        }

        $update = $this->pdo->prepare(
            "UPDATE ronda r
             INNER JOIN torneo t ON t.idtorneo = r.idtorneo
             SET r.estado = ?
             WHERE r.idronda = ? AND r.idtorneo = ? AND t.idorganizador = ?"
        );
        return $update->execute([$estado, (int)$idronda, (int)$idtorneo, (int)$idorganizador]);
    }
}