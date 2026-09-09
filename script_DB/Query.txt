-- =====================================================
-- SGDM - Sistema de Gestión Deportiva Modular
-- Sistema de Gestión Deportiva Modular
-- =====================================================

--CREATE DATABASE IF NOT EXISTS sgdm
--    CHARACTER SET utf8mb4
--    COLLATE utf8mb4_unicode_ci;




--USE sgdm2;


-- =====================================================
-- 1. ROLES
-- =====================================================

CREATE TABLE rol (
    idrol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    activo BOOLEAN NOT NULL DEFAULT TRUE
);


-- =====================================================
-- 2. USUARIO
-- =====================================================

CREATE TABLE usuario (
    idusuario INT AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(100) NOT NULL,

    email VARCHAR(150) NOT NULL UNIQUE,

    contrasena VARCHAR(255) NOT NULL,

    estado ENUM(
        'activo',
        'inactivo',
        'bloqueado'
    ) NOT NULL DEFAULT 'activo',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


-- =====================================================
-- 3. USUARIO_ROL
-- Un usuario puede tener uno o varios roles.
--
-- Ejemplo:
-- Juan -> jugador
-- Carlos -> organizador
-- Pedro -> jugador + organizador
-- =====================================================

CREATE TABLE usuario_rol (
    idusuario INT NOT NULL,
    idrol INT NOT NULL,

    PRIMARY KEY (idusuario, idrol),

    CONSTRAINT fk_usuario_rol_usuario
        FOREIGN KEY (idusuario)
        REFERENCES usuario(idusuario)
        ON DELETE CASCADE,

    CONSTRAINT fk_usuario_rol_rol
        FOREIGN KEY (idrol)
        REFERENCES rol(idrol)
        ON DELETE CASCADE
);


-- =====================================================
-- 4. CATEGORIA
-- =====================================================

CREATE TABLE categoria (
    idcategoria INT AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(100) NOT NULL UNIQUE,

    descripcion VARCHAR(255),

    activo BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


-- =====================================================
-- 5. EQUIPO
--
-- El usuario que crea el equipo queda como creador/capitán
-- inicialmente.
-- =====================================================

CREATE TABLE equipo (
    idequipo INT AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(100) NOT NULL,

    idcategoria INT NOT NULL,

    idcreador INT NOT NULL,

    idcapitan INT NOT NULL,

    estado ENUM(
        'activo',
        'inactivo'
    ) NOT NULL DEFAULT 'activo',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_equipo_categoria
        FOREIGN KEY (idcategoria)
        REFERENCES categoria(idcategoria),

    CONSTRAINT fk_equipo_creador
        FOREIGN KEY (idcreador)
        REFERENCES usuario(idusuario),

    CONSTRAINT fk_equipo_capitan
        FOREIGN KEY (idcapitan)
        REFERENCES usuario(idusuario)
);


-- =====================================================
-- 6. INTEGRA
-- Relación N:N entre usuarios/jugadores y equipos.
--
-- Un jugador puede pertenecer a varios equipos.
-- Un equipo puede tener varios jugadores.
-- =====================================================

CREATE TABLE integra (
    idusuario INT NOT NULL,
    idequipo INT NOT NULL,

    fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_ingreso DATETIME NULL,

    origen_solicitud ENUM(
        'invitacion',
        'solicitud'
    ) NOT NULL,

    solicitado_por INT NOT NULL,

    estado ENUM(
        'pendiente',
        'activo',
        'inactivo'
    ) NOT NULL DEFAULT 'pendiente',

    PRIMARY KEY (idusuario, idequipo),

    CONSTRAINT fk_integra_usuario
        FOREIGN KEY (idusuario)
        REFERENCES usuario(idusuario)
        ON DELETE CASCADE,

    CONSTRAINT fk_integra_equipo
        FOREIGN KEY (idequipo)
        REFERENCES equipo(idequipo)
        ON DELETE CASCADE,

    CONSTRAINT fk_integra_solicitado_por
        FOREIGN KEY (solicitado_por)
        REFERENCES usuario(idusuario)
        ON DELETE CASCADE
);


-- =====================================================
-- 7. TORNEO
-- =====================================================

CREATE TABLE torneo (
    idtorneo INT AUTO_INCREMENT PRIMARY KEY,
    nombretorneo VARCHAR(150) NOT NULL,

    tipo ENUM(
        'liga',
        'eliminacion_directa',
        'suizo'
    ) NOT NULL,

    modalidad ENUM(
        'individual',
        'equipos'
    ) NOT NULL,

    idcategoria INT NOT NULL,
    descripcion TEXT,
    fechainicio DATE NOT NULL,
    fechafin DATE NOT NULL,
    horainicio TIME NOT NULL,
    idorganizador INT NOT NULL,

    estado ENUM(
        'borrador',
        'inscripciones',
        'en_curso',
        'finalizado',
        'cancelado'
    ) NOT NULL DEFAULT 'borrador',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_torneo_categoria
        FOREIGN KEY (idcategoria)
        REFERENCES categoria(idcategoria),

    CONSTRAINT fk_torneo_organizador
        FOREIGN KEY (idorganizador)
        REFERENCES usuario(idusuario)
);


-- =====================================================
-- 8. Participante
-- =====================================================

/* la tabla Participante ya no se utilizara */


-- =====================================================
-- 9. INSCRIBE
-- =====================================================
--
-- Una inscripción puede ser:
--
-- 1. Individual:
--      idusuario = usuario participante
--      idequipo  = NULL
--
-- 2. Por equipo:
--      idusuario = NULL
--      idequipo  = equipo participante
--
-- La modalidad del torneo determina cuál corresponde.
-- =====================================================

CREATE TABLE inscribe (

    idinscripcion INT AUTO_INCREMENT PRIMARY KEY,

    idtorneo INT NOT NULL,

    idusuario INT NULL,

    idequipo INT NULL,

    estado ENUM(
        'pendiente',
        'aprobada',
        'rechazada',
        'cancelada'
    ) NOT NULL DEFAULT 'pendiente',

    fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_respuesta DATETIME NULL,

    CONSTRAINT fk_inscribe_torneo
        FOREIGN KEY (idtorneo)
        REFERENCES torneo(idtorneo)
        ON DELETE CASCADE,

    CONSTRAINT fk_inscribe_usuario
        FOREIGN KEY (idusuario)
        REFERENCES usuario(idusuario)
        ON DELETE CASCADE,

    CONSTRAINT fk_inscribe_equipo
        FOREIGN KEY (idequipo)
        REFERENCES equipo(idequipo)
        ON DELETE CASCADE,

    CONSTRAINT chk_inscribe_participante
        CHECK (
            (idusuario IS NOT NULL AND idequipo IS NULL)
            OR
            (idusuario IS NULL AND idequipo IS NOT NULL)
        )
);

-- =====================================================
-- 10. RONDA
--
-- Sirve para los diferentes formatos:
--
-- Liga:
--   Jornada 1
--   Jornada 2
--
-- Eliminación:
--   Octavos
--   Cuartos
--   Semifinal
--   Final
--
-- Suizo:
--   Ronda 1
--   Ronda 2
--   Ronda 3
-- =====================================================

CREATE TABLE ronda (
    idronda INT AUTO_INCREMENT PRIMARY KEY,

    idtorneo INT NOT NULL,

    numero INT NOT NULL,

    nombre VARCHAR(100),

    fechainicio DATETIME NULL,

    fechafin DATETIME NULL,

    estado ENUM(
        'pendiente',
        'en_curso',
        'finalizada'
    ) NOT NULL DEFAULT 'pendiente',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_ronda_torneo
        FOREIGN KEY (idtorneo)
        REFERENCES torneo(idtorneo)
        ON DELETE CASCADE,

    CONSTRAINT uq_ronda_torneo_numero
        UNIQUE (idtorneo, numero)
);


-- =====================================================
-- 11. PARTIDO
--
-- Ahora sabemos:
--
-- - A qué torneo pertenece
-- - A qué ronda pertenece
-- - Qué equipos participan (a través de las inscripciones)
-- - Que usuarios participan (a través de las inscripciones)
--
-- Cada lado del partido corresponde a una inscripción
-- aprobada del torneo.
-- =====================================================

CREATE TABLE partido (

    idpartido INT AUTO_INCREMENT PRIMARY KEY,

    idtorneo INT NOT NULL,

    idronda INT NOT NULL,

    idinscripcion_local INT NOT NULL,

    idinscripcion_visitante INT NOT NULL,

    fecha DATETIME NOT NULL,

    estado ENUM(
        'programado',
        'en_curso',
        'finalizado',
        'cancelado'
    ) NOT NULL DEFAULT 'programado',

    CONSTRAINT fk_partido_torneo
        FOREIGN KEY (idtorneo)
        REFERENCES torneo(idtorneo)
        ON DELETE CASCADE,

    CONSTRAINT fk_partido_ronda
        FOREIGN KEY (idronda)
        REFERENCES ronda(idronda)
        ON DELETE CASCADE,

    CONSTRAINT fk_partido_inscripcion_local
        FOREIGN KEY (idinscripcion_local)
        REFERENCES inscribe(idinscripcion),

    CONSTRAINT fk_partido_inscripcion_visitante
        FOREIGN KEY (idinscripcion_visitante)
        REFERENCES inscribe(idinscripcion),

    CONSTRAINT chk_partido_inscripciones_diferentes
        CHECK (
            idinscripcion_local <> idinscripcion_visitante
        )
);

-- =====================================================
-- 12. RESULTADO
--
-- Un partido puede tener un único resultado.
--
-- idequipoganado puede ser NULL cuando:
-- - hay empate
-- - todavía no se ha determinado ganador
-- =====================================================

CREATE TABLE resultado (

    idresultado INT AUTO_INCREMENT PRIMARY KEY,

    idpartido INT NOT NULL UNIQUE,

    puntoslocal INT NOT NULL DEFAULT 0,

    puntosvisitante INT NOT NULL DEFAULT 0,

    idinscripcion_ganador INT NULL,

    observaciones VARCHAR(500),

    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_resultado_partido
        FOREIGN KEY (idpartido)
        REFERENCES partido(idpartido)
        ON DELETE CASCADE,

    CONSTRAINT fk_resultado_ganador
        FOREIGN KEY (idinscripcion_ganador)
        REFERENCES inscribe(idinscripcion),

    CONSTRAINT chk_resultado_puntos_local
        CHECK (puntoslocal >= 0),

    CONSTRAINT chk_resultado_puntos_visitante
        CHECK (puntosvisitante >= 0)
);


-- =====================================================
-- 13. AUDITORIA
--
-- Permite saber quién realizó una acción y cuándo.
--
-- Ejemplo:
--
-- Usuario 15
-- SOLICITAR_INSCRIPCION
-- tabla: inscribe
-- registro: torneo 1 / equipo 5
-- =====================================================

CREATE TABLE auditoria (
    idauditoria BIGINT AUTO_INCREMENT PRIMARY KEY,

    idusuario INT NULL,

    accion VARCHAR(100) NOT NULL,

    tabla_afectada VARCHAR(100),

    registro_id BIGINT,

    datos_anteriores JSON NULL,

    datos_nuevos JSON NULL,

    ip VARCHAR(45),

    user_agent VARCHAR(500),

    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_auditoria_usuario
        FOREIGN KEY (idusuario)
        REFERENCES usuario(idusuario)
        ON DELETE SET NULL
);


-- =====================================================
-- 14. DATOS INICIALES
-- =====================================================

INSERT INTO rol (nombre, descripcion)
VALUES
    ('jugador', 'Usuario que participa en equipos'),
    ('organizador', 'Usuario que crea y administra torneos'),
    ('administrador', 'Usuario que administra la plataforma');


-- =====================================================
-- 15. ÍNDICES
-- =====================================================
/*
CREATE INDEX idx_equipo_categoria
    ON equipo(idcategoria);

CREATE INDEX idx_equipo_creador
    ON equipo(idcreador);

CREATE INDEX idx_integra_equipo
    ON integra(idequipo);

CREATE INDEX idx_torneo_organizador
    ON torneo(idorganizador);

CREATE INDEX idx_torneo_categoria
    ON torneo(idcategoria);

CREATE INDEX idx_torneo_tipo
    ON torneo(tipo);

CREATE INDEX idx_torneo_estado
    ON torneo(estado);

CREATE INDEX idx_inscribe_estado
    ON inscribe(estado);

CREATE INDEX idx_ronda_torneo
    ON ronda(idtorneo);

CREATE INDEX idx_partido_torneo
    ON partido(idtorneo);

CREATE INDEX idx_partido_ronda
    ON partido(idronda);

CREATE INDEX idx_resultado_partido
    ON resultado(idpartido);

CREATE INDEX idx_auditoria_usuario
    ON auditoria(idusuario);

CREATE INDEX idx_auditoria_fecha
    ON auditoria(fecha);

*/





/*_________datos de prueba________*/

INSERT INTO usuario (nombre, email, contrasena, estado)
VALUES
('Carlos Pérez', 'carlos@torneos.test', '$2y$10$abcdefghijklmnopqrstuv', 'activo'),
('María López', 'maria@torneos.test', '$2y$10$abcdefghijklmnopqrstuv', 'activo'),
('Juan García', 'juan@torneos.test', '$2y$10$abcdefghijklmnopqrstuv', 'activo'),
('Pedro Rodríguez', 'pedro@torneos.test', '$2y$10$abcdefghijklmnopqrstuv', 'activo');

INSERT INTO usuario_rol (idusuario, idrol)
VALUES
(1, 2),
(2, 2),
(3, 2);

INSERT INTO categoria (nombre, descripcion)
VALUES
('Fútbol', 'Torneos de fútbol'),
('Fútbol Sala', 'Torneos de fútbol sala'),
('Baloncesto', 'Torneos de baloncesto'),
('Voleibol', 'Torneos de voleibol'),
('Videojuegos', 'Torneos de videojuegos competitivos');


INSERT INTO torneo
(
    nombretorneo,
    tipo,
    descripcion,
    fechainicio,
    fechafin,
    horainicio,
    idorganizador,
    idcategoria,
    estado
)
VALUES

(
    'Copa Apertura 2026',
    'eliminacion_directa',
    'Torneo competitivo de fútbol con eliminación directa.',
    '2026-09-15',
    '2026-09-30',
    '19:00:00',
    1,
    1,
    'inscripciones'
),

(
    'Liga Gamer Bolivia',
    'liga',
    'Competencia de videojuegos con formato de liga.',
    '2026-09-20',
    '2026-10-20',
    '20:00:00',
    2,
    5,
    'inscripciones'
),

(
    'Torneo Nacional de Fútbol',
    'suizo',
    'Torneo nacional con equipos de diferentes regiones.',
    '2026-09-10',
    '2026-10-05',
    '18:30:00',
    1,
    1,
    'en_curso'
),

(
    'Copa Universitaria 2026',
    'liga',
    'Competencia deportiva entre equipos universitarios.',
    '2026-10-01',
    '2026-10-30',
    '17:00:00',
    3,
    3,
    'inscripciones'
),

(
    'Desafío de Primavera',
    'suizo',
    'Torneo abierto para jugadores y equipos registrados.',
    '2026-10-10',
    '2026-10-25',
    '16:00:00',
    2,
    2,
    'inscripciones'
);