-- =====================================================================
-- SISTEMA DE GESTIÓN DE DENUNCIAS CIUDADANAS (SGDC)
-- Base de Datos: PostgreSQL 16
-- Compatible con: Laravel 11 + Fortify + Spatie Permission
-- Versión: 2.0 (Con mejoras de integridad y trazabilidad)
-- =====================================================================

-- =====================================================================
-- 1. EXTENSIONES REQUERIDAS
-- =====================================================================
CREATE EXTENSION IF NOT EXISTS pgcrypto;  -- Para cifrado adicional si es necesario

-- =====================================================================
-- 2. TIPOS ENUMERADOS (Para integridad de datos)
-- =====================================================================

CREATE TYPE estado_notificacion AS ENUM ('PENDIENTE', 'ENVIADA', 'FALLIDA', 'LEIDA');
CREATE TYPE canal_notificacion AS ENUM ('email', 'sms', 'web', 'push');
CREATE TYPE severidad_evento AS ENUM ('BAJA', 'MEDIA', 'ALTA', 'CRITICA');
CREATE TYPE canal_2fa AS ENUM ('sms', 'email', 'app');

-- =====================================================================
-- 3. TABLAS DE ÁREAS/DEPARTAMENTOS
-- =====================================================================

CREATE TABLE areas (
    id                  BIGSERIAL PRIMARY KEY,
    nombre              VARCHAR(100) NOT NULL UNIQUE,
    codigo              VARCHAR(20) UNIQUE,
    descripcion         VARCHAR(255),
    email_contacto      VARCHAR(150),
    telefono            VARCHAR(20),
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    creado_en           TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    actualizado_en      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_areas_activo ON areas (activo) WHERE activo = TRUE;

-- =====================================================================
-- 4. TABLAS DE USUARIOS Y AUTENTICACIÓN
-- =====================================================================

CREATE TABLE usuarios (
    id                          BIGSERIAL PRIMARY KEY,
    nombre                      VARCHAR(150) NOT NULL,
    apellido                    VARCHAR(150),
    email                       VARCHAR(150) NOT NULL UNIQUE,
    dni                         VARCHAR(15),
    telefono                    VARCHAR(20),
    direccion                   VARCHAR(255),
    area_id                     BIGINT,                         -- Área del funcionario
    password_hash               VARCHAR(255) NOT NULL,          -- bcrypt via Laravel Hash::make()
    remember_token              VARCHAR(100),                   -- Para "Recuérdame" de Laravel
    email_verificado_en         TIMESTAMPTZ,

    -- Campos para 2FA (Laravel Fortify TOTP)
    two_factor_secret           TEXT,                           -- Secreto TOTP cifrado (AES-256)
    two_factor_recovery_codes   TEXT,                           -- Códigos de recuperación cifrados
    two_factor_confirmed_at     TIMESTAMPTZ,                    -- Fecha de confirmación 2FA

    -- Control de acceso
    intentos_fallidos           SMALLINT NOT NULL DEFAULT 0,
    bloqueado_hasta             TIMESTAMPTZ,
    ultimo_login                TIMESTAMPTZ,

    activo                      BOOLEAN NOT NULL DEFAULT TRUE,
    eliminado_en                TIMESTAMPTZ,                    -- Soft delete
    creado_en                   TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    actualizado_en              TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    CONSTRAINT fk_usuario_area FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE SET NULL,
    CONSTRAINT chk_intentos_fallidos CHECK (intentos_fallidos >= 0 AND intentos_fallidos <= 10)
);

CREATE INDEX idx_usuarios_email ON usuarios (email);
CREATE INDEX idx_usuarios_dni ON usuarios (dni) WHERE dni IS NOT NULL;
CREATE INDEX idx_usuarios_activo ON usuarios (activo) WHERE activo = TRUE;
CREATE INDEX idx_usuarios_area ON usuarios (area_id) WHERE area_id IS NOT NULL;

-- Agregar responsable de área después de crear usuarios
ALTER TABLE areas ADD COLUMN responsable_id BIGINT;
ALTER TABLE areas ADD CONSTRAINT fk_area_responsable FOREIGN KEY (responsable_id) REFERENCES usuarios(id) ON DELETE SET NULL;

-- =====================================================================
-- 5. ROLES Y PERMISOS (Compatible con Spatie Permission)
-- =====================================================================

CREATE TABLE roles (
    id                  BIGSERIAL PRIMARY KEY,
    nombre              VARCHAR(50) NOT NULL UNIQUE,    -- 'ciudadano','funcionario','admin'
    guard_name          VARCHAR(50) NOT NULL DEFAULT 'web',  -- Requerido por Spatie
    descripcion         VARCHAR(255),
    creado_en           TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    actualizado_en      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE permisos (
    id                  BIGSERIAL PRIMARY KEY,
    nombre              VARCHAR(100) NOT NULL UNIQUE,   -- 'crear_denuncia','ver_dashboard', etc.
    guard_name          VARCHAR(50) NOT NULL DEFAULT 'web',
    descripcion         VARCHAR(255),
    creado_en           TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    actualizado_en      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Tabla pivote: usuario <-> rol
CREATE TABLE rol_usuario (
    usuario_id          BIGINT NOT NULL,
    rol_id              BIGINT NOT NULL,
    model_type          VARCHAR(255) NOT NULL DEFAULT 'App\\Models\\Usuario',
    asignado_en         TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    PRIMARY KEY (usuario_id, rol_id),
    CONSTRAINT fk_rol_usuario_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_rol_usuario_rol FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- Tabla pivote: rol <-> permiso
CREATE TABLE rol_permiso (
    rol_id              BIGINT NOT NULL,
    permiso_id          BIGINT NOT NULL,
    PRIMARY KEY (rol_id, permiso_id),
    CONSTRAINT fk_rol_permiso_rol FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_rol_permiso_permiso FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
);

-- Tabla pivote: usuario <-> permiso (permisos directos)
CREATE TABLE permiso_usuario (
    usuario_id          BIGINT NOT NULL,
    permiso_id          BIGINT NOT NULL,
    model_type          VARCHAR(255) NOT NULL DEFAULT 'App\\Models\\Usuario',
    PRIMARY KEY (usuario_id, permiso_id),
    CONSTRAINT fk_permiso_usuario_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_permiso_usuario_permiso FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
);

-- =====================================================================
-- 6. SESIONES (Para manejo de sesiones en BD - RS-004)
-- =====================================================================

CREATE TABLE sessions (
    id                  VARCHAR(255) PRIMARY KEY,
    user_id             BIGINT,
    ip_address          VARCHAR(45),
    user_agent          TEXT,
    payload             TEXT NOT NULL,
    last_activity       INTEGER NOT NULL,
    CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE INDEX idx_sessions_user ON sessions (user_id);
CREATE INDEX idx_sessions_activity ON sessions (last_activity);

-- =====================================================================
-- 7. TOKENS 2FA TEMPORALES (OTP por SMS/Email como alternativa)
-- =====================================================================

CREATE TABLE tokens_doble_factor (
    id                  BIGSERIAL PRIMARY KEY,
    usuario_id          BIGINT NOT NULL,
    token               VARCHAR(10) NOT NULL,
    canal               canal_2fa NOT NULL,             -- ENUM: 'sms','email','app'
    expira_en           TIMESTAMPTZ NOT NULL,
    usado_en            TIMESTAMPTZ,
    intentos            SMALLINT NOT NULL DEFAULT 0,
    creado_en           TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT fk_tokens_2fa_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT chk_intentos_token CHECK (intentos >= 0 AND intentos <= 5)
);

CREATE INDEX idx_tokens_2fa_usuario ON tokens_doble_factor (usuario_id);
CREATE INDEX idx_tokens_2fa_expira ON tokens_doble_factor (expira_en);

-- =====================================================================
-- 8. AUDITORÍA Y SEGURIDAD (RS-005)
-- =====================================================================

-- Logs de auditoría inmutables
CREATE TABLE registros_auditoria (
    id                  BIGSERIAL PRIMARY KEY,
    usuario_id          BIGINT,
    accion              VARCHAR(100) NOT NULL,
    tipo_entidad        VARCHAR(100),
    id_entidad          BIGINT,
    valores_anteriores  JSONB,
    valores_nuevos      JSONB,
    ip_origen           VARCHAR(45),
    user_agent          VARCHAR(500),
    creado_en           TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT fk_auditoria_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE INDEX idx_auditoria_usuario ON registros_auditoria (usuario_id);
CREATE INDEX idx_auditoria_accion ON registros_auditoria (accion);
CREATE INDEX idx_auditoria_entidad ON registros_auditoria (tipo_entidad, id_entidad);
CREATE INDEX idx_auditoria_fecha ON registros_auditoria (creado_en);

-- Eventos de seguridad (WAF, intentos de ataque)
CREATE TABLE eventos_seguridad (
    id                  BIGSERIAL PRIMARY KEY,
    tipo_evento         VARCHAR(100) NOT NULL,
    severidad           severidad_evento NOT NULL,      -- ENUM: 'BAJA','MEDIA','ALTA','CRITICA'
    ip_origen           VARCHAR(45),
    usuario_id          BIGINT,
    ruta_solicitud      VARCHAR(500),
    metodo_http         VARCHAR(10),
    payload_muestra     TEXT,
    headers             JSONB,
    bloqueado           BOOLEAN NOT NULL DEFAULT FALSE,
    creado_en           TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT fk_eventos_seguridad_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE INDEX idx_eventos_tipo ON eventos_seguridad (tipo_evento);
CREATE INDEX idx_eventos_severidad ON eventos_seguridad (severidad);
CREATE INDEX idx_eventos_ip ON eventos_seguridad (ip_origen);
CREATE INDEX idx_eventos_fecha ON eventos_seguridad (creado_en);

-- =====================================================================
-- 9. CATÁLOGOS
-- =====================================================================

CREATE TABLE categorias_denuncia (
    id                  BIGSERIAL PRIMARY KEY,
    nombre              VARCHAR(100) NOT NULL UNIQUE,
    descripcion         VARCHAR(255),
    icono               VARCHAR(50),
    color               VARCHAR(7),
    area_default_id     BIGINT,                         -- Área que atiende por defecto
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    orden               SMALLINT NOT NULL DEFAULT 0,
    creado_en           TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    actualizado_en      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT fk_categoria_area FOREIGN KEY (area_default_id) REFERENCES areas(id) ON DELETE SET NULL,
    CONSTRAINT chk_color_hex CHECK (color IS NULL OR color ~ '^#[0-9A-Fa-f]{6}$')
);

CREATE INDEX idx_categorias_activo ON categorias_denuncia (activo, orden) WHERE activo = TRUE;

CREATE TABLE estados_denuncia (
    id                  BIGSERIAL PRIMARY KEY,
    nombre              VARCHAR(50) NOT NULL UNIQUE,
    codigo              VARCHAR(20) UNIQUE,             -- Código corto: 'REG','REV','PRO','ATE','REC'
    descripcion         VARCHAR(255),
    color               VARCHAR(7),
    es_inicial          BOOLEAN NOT NULL DEFAULT FALSE,
    es_final            BOOLEAN NOT NULL DEFAULT FALSE,
    orden               SMALLINT NOT NULL DEFAULT 0,
    creado_en           TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    actualizado_en      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_color_estado CHECK (color IS NULL OR color ~ '^#[0-9A-Fa-f]{6}$'),
    CONSTRAINT chk_no_inicial_final CHECK (NOT (es_inicial = TRUE AND es_final = TRUE))
);

-- Tabla de transiciones válidas entre estados
CREATE TABLE transiciones_estado (
    id                  BIGSERIAL PRIMARY KEY,
    estado_origen_id    BIGINT NOT NULL,
    estado_destino_id   BIGINT NOT NULL,
    nombre              VARCHAR(100),                   -- 'Aprobar','Rechazar','Asignar', etc.
    requiere_motivo     BOOLEAN NOT NULL DEFAULT FALSE,
    requiere_asignacion BOOLEAN NOT NULL DEFAULT FALSE, -- Requiere asignar funcionario
    solo_admin          BOOLEAN NOT NULL DEFAULT FALSE, -- Solo admin puede hacer esta transición
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    creado_en           TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    actualizado_en      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT fk_trans_origen FOREIGN KEY (estado_origen_id) REFERENCES estados_denuncia(id) ON DELETE CASCADE,
    CONSTRAINT fk_trans_destino FOREIGN KEY (estado_destino_id) REFERENCES estados_denuncia(id) ON DELETE CASCADE,
    CONSTRAINT uk_transicion UNIQUE (estado_origen_id, estado_destino_id)
);

CREATE INDEX idx_transiciones_origen ON transiciones_estado (estado_origen_id) WHERE activo = TRUE;

CREATE TABLE prioridades_denuncia (
    id                  BIGSERIAL PRIMARY KEY,
    nombre              VARCHAR(50) NOT NULL UNIQUE,
    codigo              VARCHAR(10) UNIQUE,             -- 'LOW','MED','HIGH','CRIT'
    descripcion         VARCHAR(255),
    color               VARCHAR(7),
    sla_horas           INTEGER NOT NULL DEFAULT 72,
    orden               SMALLINT NOT NULL DEFAULT 0,
    creado_en           TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    actualizado_en      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_sla_positivo CHECK (sla_horas > 0),
    CONSTRAINT chk_color_prioridad CHECK (color IS NULL OR color ~ '^#[0-9A-Fa-f]{6}$')
);

CREATE TABLE distritos (
    id                  BIGSERIAL PRIMARY KEY,
    nombre              VARCHAR(100) NOT NULL UNIQUE,
    codigo              VARCHAR(10),
    provincia           VARCHAR(100) DEFAULT 'Lima',
    departamento        VARCHAR(100) DEFAULT 'Lima',
    activo              BOOLEAN NOT NULL DEFAULT TRUE,
    creado_en           TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    actualizado_en      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_distritos_activo ON distritos (activo) WHERE activo = TRUE;

-- =====================================================================
-- 10. DENUNCIAS (NÚCLEO DEL SISTEMA)
-- =====================================================================

CREATE TABLE denuncias (
    id                  BIGSERIAL PRIMARY KEY,
    codigo              VARCHAR(50) NOT NULL UNIQUE,    -- 'DEN-2025-000123'

    -- Relaciones
    ciudadano_id        BIGINT NOT NULL,
    asignado_a_id       BIGINT,
    area_id             BIGINT,                         -- Área responsable actual
    categoria_id        BIGINT NOT NULL,
    prioridad_id        BIGINT NOT NULL,
    estado_id           BIGINT NOT NULL,
    distrito_id         BIGINT,

    -- Datos de la denuncia
    titulo              VARCHAR(200) NOT NULL,
    descripcion         TEXT NOT NULL,                  -- CIFRADO con AES-256 via Laravel

    -- Ubicación
    direccion           VARCHAR(255),
    referencia          VARCHAR(255),
    latitud             NUMERIC(10,7),
    longitud            NUMERIC(10,7),

    -- Metadata
    es_anonima          BOOLEAN NOT NULL DEFAULT FALSE,
    ip_origen           VARCHAR(45),
    user_agent          VARCHAR(500),

    -- Fechas
    registrada_en       TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    fecha_limite_sla    TIMESTAMPTZ,
    cerrada_en          TIMESTAMPTZ,
    eliminado_en        TIMESTAMPTZ,
    creado_en           TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    actualizado_en      TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    -- Constraints
    CONSTRAINT fk_denuncia_ciudadano FOREIGN KEY (ciudadano_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    CONSTRAINT fk_denuncia_asignado FOREIGN KEY (asignado_a_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT fk_denuncia_area FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE SET NULL,
    CONSTRAINT fk_denuncia_categoria FOREIGN KEY (categoria_id) REFERENCES categorias_denuncia(id) ON DELETE RESTRICT,
    CONSTRAINT fk_denuncia_prioridad FOREIGN KEY (prioridad_id) REFERENCES prioridades_denuncia(id) ON DELETE RESTRICT,
    CONSTRAINT fk_denuncia_estado FOREIGN KEY (estado_id) REFERENCES estados_denuncia(id) ON DELETE RESTRICT,
    CONSTRAINT fk_denuncia_distrito FOREIGN KEY (distrito_id) REFERENCES distritos(id) ON DELETE SET NULL,
    CONSTRAINT chk_coordenadas CHECK (
        (latitud IS NULL AND longitud IS NULL) OR
        (latitud BETWEEN -90 AND 90 AND longitud BETWEEN -180 AND 180)
    )
);

-- Índices para rendimiento del Dashboard (RF-005)
CREATE INDEX idx_denuncias_codigo ON denuncias (codigo);
CREATE INDEX idx_denuncias_estado ON denuncias (estado_id);
CREATE INDEX idx_denuncias_categoria ON denuncias (categoria_id);
CREATE INDEX idx_denuncias_prioridad ON denuncias (prioridad_id);
CREATE INDEX idx_denuncias_distrito ON denuncias (distrito_id);
CREATE INDEX idx_denuncias_area ON denuncias (area_id);
CREATE INDEX idx_denuncias_ciudadano ON denuncias (ciudadano_id);
CREATE INDEX idx_denuncias_asignado ON denuncias (asignado_a_id) WHERE asignado_a_id IS NOT NULL;
CREATE INDEX idx_denuncias_fecha ON denuncias (registrada_en);
CREATE INDEX idx_denuncias_sla ON denuncias (fecha_limite_sla) WHERE cerrada_en IS NULL;
CREATE INDEX idx_denuncias_activas ON denuncias (estado_id, registrada_en) WHERE eliminado_en IS NULL;

-- =====================================================================
-- 11. HISTORIAL DE ESTADOS (RF-002, RF-004)
-- =====================================================================

CREATE TABLE historial_estados_denuncia (
    id                  BIGSERIAL PRIMARY KEY,
    denuncia_id         BIGINT NOT NULL,
    estado_anterior_id  BIGINT,
    estado_nuevo_id     BIGINT NOT NULL,
    cambiado_por_id     BIGINT NOT NULL,
    motivo_cambio       TEXT,
    tiempo_en_estado    INTERVAL,                       -- Tiempo que estuvo en estado anterior
    creado_en           TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    CONSTRAINT fk_historial_denuncia FOREIGN KEY (denuncia_id) REFERENCES denuncias(id) ON DELETE CASCADE,
    CONSTRAINT fk_historial_estado_anterior FOREIGN KEY (estado_anterior_id) REFERENCES estados_denuncia(id) ON DELETE RESTRICT,
    CONSTRAINT fk_historial_estado_nuevo FOREIGN KEY (estado_nuevo_id) REFERENCES estados_denuncia(id) ON DELETE RESTRICT,
    CONSTRAINT fk_historial_cambiado_por FOREIGN KEY (cambiado_por_id) REFERENCES usuarios(id) ON DELETE RESTRICT
);

CREATE INDEX idx_historial_denuncia ON historial_estados_denuncia (denuncia_id);
CREATE INDEX idx_historial_fecha ON historial_estados_denuncia (creado_en);

-- =====================================================================
-- 12. HISTORIAL DE ASIGNACIONES
-- =====================================================================

CREATE TABLE historial_asignaciones (
    id                  BIGSERIAL PRIMARY KEY,
    denuncia_id         BIGINT NOT NULL,
    asignado_de_id      BIGINT,                         -- NULL si es primera asignación
    asignado_a_id       BIGINT NOT NULL,
    area_anterior_id    BIGINT,
    area_nueva_id       BIGINT,
    asignado_por_id     BIGINT NOT NULL,
    motivo              TEXT,
    creado_en           TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    CONSTRAINT fk_hist_asig_denuncia FOREIGN KEY (denuncia_id) REFERENCES denuncias(id) ON DELETE CASCADE,
    CONSTRAINT fk_hist_asig_de FOREIGN KEY (asignado_de_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT fk_hist_asig_a FOREIGN KEY (asignado_a_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    CONSTRAINT fk_hist_asig_area_ant FOREIGN KEY (area_anterior_id) REFERENCES areas(id) ON DELETE SET NULL,
    CONSTRAINT fk_hist_asig_area_nueva FOREIGN KEY (area_nueva_id) REFERENCES areas(id) ON DELETE SET NULL,
    CONSTRAINT fk_hist_asig_por FOREIGN KEY (asignado_por_id) REFERENCES usuarios(id) ON DELETE RESTRICT
);

CREATE INDEX idx_hist_asig_denuncia ON historial_asignaciones (denuncia_id);
CREATE INDEX idx_hist_asig_fecha ON historial_asignaciones (creado_en);

-- =====================================================================
-- 13. ADJUNTOS / EVIDENCIAS (RS-002: Cifrado)
-- =====================================================================

CREATE TABLE adjuntos (
    id                  BIGSERIAL PRIMARY KEY,
    denuncia_id         BIGINT NOT NULL,
    subido_por_id       BIGINT NOT NULL,

    nombre_original     VARCHAR(255) NOT NULL,
    nombre_almacenado   VARCHAR(255) NOT NULL,
    ruta_almacenamiento VARCHAR(500) NOT NULL,
    tipo_mime           VARCHAR(100) NOT NULL,
    tamano_bytes        BIGINT NOT NULL,

    cifrado             BOOLEAN NOT NULL DEFAULT TRUE,
    hash_archivo        VARCHAR(64),                    -- SHA-256 para integridad

    eliminado_en        TIMESTAMPTZ,
    creado_en           TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    actualizado_en      TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    CONSTRAINT fk_adjuntos_denuncia FOREIGN KEY (denuncia_id) REFERENCES denuncias(id) ON DELETE CASCADE,
    CONSTRAINT fk_adjuntos_usuario FOREIGN KEY (subido_por_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    CONSTRAINT chk_tamano_positivo CHECK (tamano_bytes > 0)
);

CREATE INDEX idx_adjuntos_denuncia ON adjuntos (denuncia_id);

-- =====================================================================
-- 14. COMENTARIOS
-- =====================================================================

CREATE TABLE comentarios (
    id                  BIGSERIAL PRIMARY KEY,
    denuncia_id         BIGINT NOT NULL,
    usuario_id          BIGINT NOT NULL,
    comentario_padre_id BIGINT,                         -- Para respuestas/hilos
    es_interno          BOOLEAN NOT NULL DEFAULT FALSE,
    comentario          TEXT NOT NULL,
    eliminado_en        TIMESTAMPTZ,
    creado_en           TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    actualizado_en      TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    CONSTRAINT fk_comentarios_denuncia FOREIGN KEY (denuncia_id) REFERENCES denuncias(id) ON DELETE CASCADE,
    CONSTRAINT fk_comentarios_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    CONSTRAINT fk_comentarios_padre FOREIGN KEY (comentario_padre_id) REFERENCES comentarios(id) ON DELETE SET NULL
);

CREATE INDEX idx_comentarios_denuncia ON comentarios (denuncia_id);
CREATE INDEX idx_comentarios_padre ON comentarios (comentario_padre_id) WHERE comentario_padre_id IS NOT NULL;

-- =====================================================================
-- 15. NOTIFICACIONES (RF-003)
-- =====================================================================

CREATE TABLE notificaciones (
    id                  BIGSERIAL PRIMARY KEY,
    usuario_id          BIGINT NOT NULL,
    denuncia_id         BIGINT,

    tipo                VARCHAR(50) NOT NULL,
    canal               canal_notificacion NOT NULL,    -- ENUM
    asunto              VARCHAR(200) NOT NULL,
    mensaje             TEXT NOT NULL,
    datos_extra         JSONB,

    estado              estado_notificacion NOT NULL DEFAULT 'PENDIENTE',  -- ENUM
    intentos            SMALLINT NOT NULL DEFAULT 0,
    max_intentos        SMALLINT NOT NULL DEFAULT 3,
    enviada_en          TIMESTAMPTZ,
    leida_en            TIMESTAMPTZ,
    error_mensaje       TEXT,

    creado_en           TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    actualizado_en      TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    CONSTRAINT fk_notif_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_notif_denuncia FOREIGN KEY (denuncia_id) REFERENCES denuncias(id) ON DELETE SET NULL,
    CONSTRAINT chk_intentos_max CHECK (intentos <= max_intentos)
);

CREATE INDEX idx_notificaciones_usuario ON notificaciones (usuario_id);
CREATE INDEX idx_notificaciones_denuncia ON notificaciones (denuncia_id);
CREATE INDEX idx_notificaciones_estado ON notificaciones (estado);
CREATE INDEX idx_notificaciones_pendientes ON notificaciones (estado, creado_en) WHERE estado = 'PENDIENTE';

-- =====================================================================
-- 16. CONFIGURACIÓN DEL SISTEMA
-- =====================================================================

CREATE TABLE configuracion_sistema (
    clave               VARCHAR(100) PRIMARY KEY,
    valor               TEXT NOT NULL,
    tipo                VARCHAR(20) NOT NULL DEFAULT 'string',
    categoria           VARCHAR(50) DEFAULT 'general',  -- 'general','seguridad','notificaciones','sla'
    descripcion         VARCHAR(255),
    es_sensible         BOOLEAN NOT NULL DEFAULT FALSE, -- TRUE: valor cifrado
    actualizado_por_id  BIGINT,
    creado_en           TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    actualizado_en      TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    CONSTRAINT fk_config_usuario FOREIGN KEY (actualizado_por_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT chk_tipo_config CHECK (tipo IN ('string','integer','boolean','json','encrypted'))
);

-- =====================================================================
-- 17. DATOS INICIALES
-- =====================================================================

-- Áreas municipales
INSERT INTO areas (nombre, codigo, descripcion) VALUES
('Limpieza Pública', 'LIMP', 'Área encargada de la recolección de residuos y limpieza de calles'),
('Alumbrado Público', 'ALUM', 'Área encargada del mantenimiento del alumbrado público'),
('Seguridad Ciudadana', 'SEGU', 'Serenazgo y seguridad ciudadana'),
('Obras Públicas', 'OBRA', 'Mantenimiento de pistas, veredas e infraestructura'),
('Parques y Jardines', 'PARQ', 'Mantenimiento de áreas verdes'),
('Fiscalización', 'FISC', 'Control de ruidos y fiscalización municipal'),
('Atención al Ciudadano', 'ATEN', 'Mesa de partes y atención general');

-- Roles base
INSERT INTO roles (nombre, guard_name, descripcion) VALUES
('ciudadano', 'web', 'Usuario ciudadano que puede crear y dar seguimiento a denuncias'),
('funcionario', 'web', 'Personal municipal que gestiona las denuncias'),
('supervisor', 'web', 'Supervisor de área que puede reasignar y ver reportes'),
('admin', 'web', 'Administrador del sistema con acceso total');

-- Permisos base
INSERT INTO permisos (nombre, guard_name, descripcion) VALUES
-- Permisos de ciudadano
('crear_denuncia', 'web', 'Puede crear nuevas denuncias'),
('ver_mis_denuncias', 'web', 'Puede ver sus propias denuncias'),
('agregar_comentario_publico', 'web', 'Puede agregar comentarios públicos'),
('editar_mi_denuncia', 'web', 'Puede editar su denuncia si está en estado inicial'),
-- Permisos de funcionario
('ver_denuncias_area', 'web', 'Puede ver denuncias de su área'),
('atender_denuncia', 'web', 'Puede atender y cambiar estado de denuncias asignadas'),
('agregar_comentario_interno', 'web', 'Puede agregar comentarios internos'),
-- Permisos de supervisor
('ver_todas_denuncias', 'web', 'Puede ver todas las denuncias del sistema'),
('asignar_denuncia', 'web', 'Puede asignar denuncias a funcionarios'),
('reasignar_denuncia', 'web', 'Puede reasignar denuncias entre áreas'),
('cambiar_prioridad', 'web', 'Puede cambiar la prioridad de una denuncia'),
('ver_dashboard', 'web', 'Puede ver el tablero de control'),
('ver_reportes', 'web', 'Puede ver reportes y estadísticas'),
-- Permisos de admin
('gestionar_usuarios', 'web', 'Puede crear, editar y desactivar usuarios'),
('gestionar_roles', 'web', 'Puede gestionar roles y permisos'),
('gestionar_catalogos', 'web', 'Puede gestionar categorías, estados, prioridades'),
('gestionar_areas', 'web', 'Puede gestionar áreas y responsables'),
('ver_auditoria', 'web', 'Puede ver los logs de auditoría'),
('ver_eventos_seguridad', 'web', 'Puede ver eventos de seguridad'),
('configurar_sistema', 'web', 'Puede modificar configuración del sistema'),
('eliminar_denuncia', 'web', 'Puede eliminar denuncias del sistema');

-- Asignar permisos a roles
INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id FROM roles r, permisos p
WHERE r.nombre = 'ciudadano' AND p.nombre IN ('crear_denuncia', 'ver_mis_denuncias', 'agregar_comentario_publico', 'editar_mi_denuncia');

INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id FROM roles r, permisos p
WHERE r.nombre = 'funcionario' AND p.nombre IN (
    'ver_denuncias_area', 'atender_denuncia', 'agregar_comentario_interno', 'agregar_comentario_publico'
);

INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id FROM roles r, permisos p
WHERE r.nombre = 'supervisor' AND p.nombre IN (
    'ver_todas_denuncias', 'asignar_denuncia', 'reasignar_denuncia', 'cambiar_prioridad',
    'ver_dashboard', 'ver_reportes', 'ver_denuncias_area', 'atender_denuncia',
    'agregar_comentario_interno', 'agregar_comentario_publico'
);

INSERT INTO rol_permiso (rol_id, permiso_id)
SELECT r.id, p.id FROM roles r, permisos p
WHERE r.nombre = 'admin';

-- Estados de denuncia
INSERT INTO estados_denuncia (nombre, codigo, descripcion, color, es_inicial, es_final, orden) VALUES
('REGISTRADA', 'REG', 'Denuncia recién registrada, pendiente de revisión', '#3B82F6', TRUE, FALSE, 1),
('EN_REVISION', 'REV', 'Denuncia en proceso de revisión inicial', '#F59E0B', FALSE, FALSE, 2),
('EN_PROCESO', 'PRO', 'Denuncia asignada y en proceso de atención', '#8B5CF6', FALSE, FALSE, 3),
('ATENDIDA', 'ATE', 'Denuncia resuelta satisfactoriamente', '#10B981', FALSE, TRUE, 4),
('RECHAZADA', 'REC', 'Denuncia rechazada por no cumplir requisitos', '#EF4444', FALSE, TRUE, 5),
('ARCHIVADA', 'ARC', 'Denuncia archivada sin resolución', '#6B7280', FALSE, TRUE, 6);

-- Transiciones de estado válidas
INSERT INTO transiciones_estado (estado_origen_id, estado_destino_id, nombre, requiere_motivo, requiere_asignacion) VALUES
-- Desde REGISTRADA
((SELECT id FROM estados_denuncia WHERE codigo='REG'), (SELECT id FROM estados_denuncia WHERE codigo='REV'), 'Iniciar revisión', FALSE, FALSE),
((SELECT id FROM estados_denuncia WHERE codigo='REG'), (SELECT id FROM estados_denuncia WHERE codigo='REC'), 'Rechazar', TRUE, FALSE),
-- Desde EN_REVISION
((SELECT id FROM estados_denuncia WHERE codigo='REV'), (SELECT id FROM estados_denuncia WHERE codigo='PRO'), 'Asignar para atención', FALSE, TRUE),
((SELECT id FROM estados_denuncia WHERE codigo='REV'), (SELECT id FROM estados_denuncia WHERE codigo='REC'), 'Rechazar', TRUE, FALSE),
((SELECT id FROM estados_denuncia WHERE codigo='REV'), (SELECT id FROM estados_denuncia WHERE codigo='REG'), 'Devolver a registro', TRUE, FALSE),
-- Desde EN_PROCESO
((SELECT id FROM estados_denuncia WHERE codigo='PRO'), (SELECT id FROM estados_denuncia WHERE codigo='ATE'), 'Marcar como atendida', TRUE, FALSE),
((SELECT id FROM estados_denuncia WHERE codigo='PRO'), (SELECT id FROM estados_denuncia WHERE codigo='REV'), 'Devolver a revisión', TRUE, FALSE),
((SELECT id FROM estados_denuncia WHERE codigo='PRO'), (SELECT id FROM estados_denuncia WHERE codigo='ARC'), 'Archivar', TRUE, FALSE);

-- Categorías de denuncia con área por defecto
INSERT INTO categorias_denuncia (nombre, descripcion, icono, color, area_default_id, orden) VALUES
('Basura', 'Acumulación de residuos sólidos en vía pública', 'trash', '#84CC16', (SELECT id FROM areas WHERE codigo='LIMP'), 1),
('Alumbrado', 'Problemas con el alumbrado público', 'lightbulb', '#FBBF24', (SELECT id FROM areas WHERE codigo='ALUM'), 2),
('Inseguridad', 'Situaciones de inseguridad ciudadana', 'shield-alert', '#EF4444', (SELECT id FROM areas WHERE codigo='SEGU'), 3),
('Baches', 'Huecos o deterioro en pistas y veredas', 'construction', '#F97316', (SELECT id FROM areas WHERE codigo='OBRA'), 4),
('Parques', 'Mantenimiento de áreas verdes y parques', 'trees', '#22C55E', (SELECT id FROM areas WHERE codigo='PARQ'), 5),
('Ruido', 'Contaminación sonora excesiva', 'volume-x', '#A855F7', (SELECT id FROM areas WHERE codigo='FISC'), 6),
('Otros', 'Otros problemas no categorizados', 'help-circle', '#6B7280', (SELECT id FROM areas WHERE codigo='ATEN'), 99);

-- Prioridades
INSERT INTO prioridades_denuncia (nombre, codigo, descripcion, color, sla_horas, orden) VALUES
('BAJA', 'LOW', 'Atención en horario regular', '#6B7280', 168, 1),
('MEDIA', 'MED', 'Atención prioritaria', '#F59E0B', 72, 2),
('ALTA', 'HIGH', 'Atención urgente', '#F97316', 24, 3),
('CRITICA', 'CRIT', 'Atención inmediata', '#EF4444', 4, 4);

-- Distritos de Lima
INSERT INTO distritos (nombre, codigo, provincia, departamento) VALUES
('Lima Cercado', 'LIM01', 'Lima', 'Lima'),
('Miraflores', 'LIM02', 'Lima', 'Lima'),
('San Isidro', 'LIM03', 'Lima', 'Lima'),
('Santiago de Surco', 'LIM04', 'Lima', 'Lima'),
('La Molina', 'LIM05', 'Lima', 'Lima'),
('San Borja', 'LIM06', 'Lima', 'Lima'),
('Barranco', 'LIM07', 'Lima', 'Lima'),
('Chorrillos', 'LIM08', 'Lima', 'Lima'),
('San Juan de Lurigancho', 'LIM09', 'Lima', 'Lima'),
('Ate', 'LIM10', 'Lima', 'Lima'),
('San Juan de Miraflores', 'LIM11', 'Lima', 'Lima'),
('Villa El Salvador', 'LIM12', 'Lima', 'Lima'),
('Comas', 'LIM13', 'Lima', 'Lima'),
('Los Olivos', 'LIM14', 'Lima', 'Lima'),
('San Martín de Porres', 'LIM15', 'Lima', 'Lima');

-- Configuración inicial del sistema
INSERT INTO configuracion_sistema (clave, valor, tipo, categoria, descripcion) VALUES
-- General
('app_nombre', 'Alerta Lima - Sistema de Gestión de Denuncias Ciudadanas', 'string', 'general', 'Nombre de la aplicación'),
('app_version', '2.0.0', 'string', 'general', 'Versión del sistema'),
('municipalidad_nombre', 'Municipalidad Metropolitana de Lima', 'string', 'general', 'Nombre de la municipalidad'),
-- Seguridad
('session_timeout_minutos', '30', 'integer', 'seguridad', 'Tiempo de inactividad para cerrar sesión'),
('max_intentos_login', '5', 'integer', 'seguridad', 'Máximo de intentos de login antes de bloquear'),
('bloqueo_minutos', '15', 'integer', 'seguridad', 'Minutos de bloqueo después de exceder intentos'),
('2fa_obligatorio_funcionarios', 'true', 'boolean', 'seguridad', '2FA obligatorio para funcionarios'),
('2fa_obligatorio_admin', 'true', 'boolean', 'seguridad', '2FA obligatorio para administradores'),
-- Archivos
('max_tamano_adjunto_mb', '10', 'integer', 'archivos', 'Tamaño máximo de archivos adjuntos en MB'),
('max_adjuntos_denuncia', '5', 'integer', 'archivos', 'Máximo de archivos por denuncia'),
('tipos_adjunto_permitidos', '["image/jpeg","image/png","image/gif","application/pdf","video/mp4"]', 'json', 'archivos', 'Tipos MIME permitidos para adjuntos'),
-- Notificaciones
('notif_email_habilitado', 'true', 'boolean', 'notificaciones', 'Habilitar notificaciones por email'),
('notif_sms_habilitado', 'false', 'boolean', 'notificaciones', 'Habilitar notificaciones por SMS'),
('notif_max_reintentos', '3', 'integer', 'notificaciones', 'Máximo de reintentos para notificaciones fallidas'),
-- SLA
('sla_alerta_porcentaje', '75', 'integer', 'sla', 'Porcentaje de SLA para generar alerta'),
('sla_escalamiento_habilitado', 'true', 'boolean', 'sla', 'Habilitar escalamiento automático por SLA vencido');

-- =====================================================================
-- 18. FUNCIONES Y TRIGGERS
-- =====================================================================

-- Función para actualizar timestamp
CREATE OR REPLACE FUNCTION actualizar_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.actualizado_en = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Aplicar triggers de timestamp
CREATE TRIGGER tr_usuarios_timestamp BEFORE UPDATE ON usuarios FOR EACH ROW EXECUTE FUNCTION actualizar_timestamp();
CREATE TRIGGER tr_areas_timestamp BEFORE UPDATE ON areas FOR EACH ROW EXECUTE FUNCTION actualizar_timestamp();
CREATE TRIGGER tr_denuncias_timestamp BEFORE UPDATE ON denuncias FOR EACH ROW EXECUTE FUNCTION actualizar_timestamp();
CREATE TRIGGER tr_adjuntos_timestamp BEFORE UPDATE ON adjuntos FOR EACH ROW EXECUTE FUNCTION actualizar_timestamp();
CREATE TRIGGER tr_comentarios_timestamp BEFORE UPDATE ON comentarios FOR EACH ROW EXECUTE FUNCTION actualizar_timestamp();
CREATE TRIGGER tr_notificaciones_timestamp BEFORE UPDATE ON notificaciones FOR EACH ROW EXECUTE FUNCTION actualizar_timestamp();
CREATE TRIGGER tr_config_timestamp BEFORE UPDATE ON configuracion_sistema FOR EACH ROW EXECUTE FUNCTION actualizar_timestamp();

-- Función para generar código de denuncia
CREATE OR REPLACE FUNCTION generar_codigo_denuncia()
RETURNS TRIGGER AS $$
DECLARE
    anio TEXT;
    secuencia BIGINT;
BEGIN
    anio := TO_CHAR(NOW(), 'YYYY');
    SELECT COALESCE(MAX(CAST(SUBSTRING(codigo FROM 10) AS BIGINT)), 0) + 1
    INTO secuencia
    FROM denuncias
    WHERE codigo LIKE 'DEN-' || anio || '-%';

    NEW.codigo := 'DEN-' || anio || '-' || LPAD(secuencia::TEXT, 6, '0');
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER tr_denuncia_codigo BEFORE INSERT ON denuncias
FOR EACH ROW WHEN (NEW.codigo IS NULL)
EXECUTE FUNCTION generar_codigo_denuncia();

-- Función para calcular fecha límite SLA
CREATE OR REPLACE FUNCTION calcular_fecha_sla()
RETURNS TRIGGER AS $$
DECLARE
    horas_sla INTEGER;
BEGIN
    SELECT sla_horas INTO horas_sla
    FROM prioridades_denuncia
    WHERE id = NEW.prioridad_id;

    NEW.fecha_limite_sla := NEW.registrada_en + (horas_sla || ' hours')::INTERVAL;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER tr_denuncia_sla BEFORE INSERT ON denuncias
FOR EACH ROW EXECUTE FUNCTION calcular_fecha_sla();

-- Función para asignar área por defecto según categoría
CREATE OR REPLACE FUNCTION asignar_area_default()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.area_id IS NULL THEN
        SELECT area_default_id INTO NEW.area_id
        FROM categorias_denuncia
        WHERE id = NEW.categoria_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER tr_denuncia_area_default BEFORE INSERT ON denuncias
FOR EACH ROW WHEN (NEW.area_id IS NULL)
EXECUTE FUNCTION asignar_area_default();

-- Función para validar transición de estado
CREATE OR REPLACE FUNCTION validar_transicion_estado()
RETURNS TRIGGER AS $$
DECLARE
    transicion_valida BOOLEAN;
BEGIN
    -- Solo validar si el estado cambió
    IF OLD.estado_id = NEW.estado_id THEN
        RETURN NEW;
    END IF;

    -- Verificar si la transición es válida
    SELECT EXISTS(
        SELECT 1 FROM transiciones_estado
        WHERE estado_origen_id = OLD.estado_id
        AND estado_destino_id = NEW.estado_id
        AND activo = TRUE
    ) INTO transicion_valida;

    IF NOT transicion_valida THEN
        RAISE EXCEPTION 'Transición de estado no permitida: % -> %', OLD.estado_id, NEW.estado_id;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER tr_validar_transicion BEFORE UPDATE ON denuncias
FOR EACH ROW EXECUTE FUNCTION validar_transicion_estado();

-- Función para actualizar fecha de cierre
CREATE OR REPLACE FUNCTION actualizar_fecha_cierre()
RETURNS TRIGGER AS $$
DECLARE
    estado_es_final BOOLEAN;
BEGIN
    SELECT es_final INTO estado_es_final
    FROM estados_denuncia
    WHERE id = NEW.estado_id;

    IF estado_es_final AND OLD.cerrada_en IS NULL THEN
        NEW.cerrada_en = NOW();
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER tr_fecha_cierre BEFORE UPDATE ON denuncias
FOR EACH ROW EXECUTE FUNCTION actualizar_fecha_cierre();

-- =====================================================================
-- 19. VISTAS ÚTILES
-- =====================================================================

-- Vista para dashboard de denuncias
CREATE OR REPLACE VIEW v_dashboard_denuncias AS
SELECT
    d.id,
    d.codigo,
    d.titulo,
    d.registrada_en,
    d.fecha_limite_sla,
    d.cerrada_en,
    CASE
        WHEN d.cerrada_en IS NOT NULL THEN 'CERRADA'
        WHEN d.fecha_limite_sla < NOW() THEN 'SLA_VENCIDO'
        WHEN d.fecha_limite_sla < NOW() + INTERVAL '1 day' THEN 'SLA_PROXIMO'
        ELSE 'EN_TIEMPO'
    END as estado_sla,
    e.nombre as estado,
    e.color as estado_color,
    c.nombre as categoria,
    c.color as categoria_color,
    p.nombre as prioridad,
    p.color as prioridad_color,
    dis.nombre as distrito,
    a.nombre as area,
    u_ciudadano.nombre as ciudadano_nombre,
    u_asignado.nombre as asignado_nombre
FROM denuncias d
JOIN estados_denuncia e ON d.estado_id = e.id
JOIN categorias_denuncia c ON d.categoria_id = c.id
JOIN prioridades_denuncia p ON d.prioridad_id = p.id
LEFT JOIN distritos dis ON d.distrito_id = dis.id
LEFT JOIN areas a ON d.area_id = a.id
LEFT JOIN usuarios u_ciudadano ON d.ciudadano_id = u_ciudadano.id
LEFT JOIN usuarios u_asignado ON d.asignado_a_id = u_asignado.id
WHERE d.eliminado_en IS NULL;

-- Vista para estadísticas por área
CREATE OR REPLACE VIEW v_estadisticas_area AS
SELECT
    a.id as area_id,
    a.nombre as area,
    COUNT(d.id) as total_denuncias,
    COUNT(CASE WHEN e.es_final = FALSE THEN 1 END) as denuncias_abiertas,
    COUNT(CASE WHEN e.nombre = 'ATENDIDA' THEN 1 END) as denuncias_atendidas,
    COUNT(CASE WHEN d.fecha_limite_sla < NOW() AND d.cerrada_en IS NULL THEN 1 END) as sla_vencido,
    ROUND(AVG(EXTRACT(EPOCH FROM (COALESCE(d.cerrada_en, NOW()) - d.registrada_en))/3600)::numeric, 2) as promedio_horas_atencion
FROM areas a
LEFT JOIN denuncias d ON a.id = d.area_id AND d.eliminado_en IS NULL
LEFT JOIN estados_denuncia e ON d.estado_id = e.id
GROUP BY a.id, a.nombre;

-- =====================================================================
-- FIN DEL SCHEMA v2.0
-- =====================================================================
