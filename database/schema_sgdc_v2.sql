-- =====================================================================
-- SISTEMA DE GESTIÓN DE DENUNCIAS CIUDADANAS (SGDC)
-- Base de Datos: PostgreSQL 16
-- Compatible con: Laravel 11 + Fortify + Spatie Permission
-- Versión: 2.0 (Estructura Limpia para uso con Seeders)
-- =====================================================================

-- =====================================================================
-- 1. EXTENSIONES REQUERIDAS
-- =====================================================================
CREATE EXTENSION IF NOT EXISTS pgcrypto;  -- Para cifrado adicional si es necesario

-- =====================================================================
-- 2. TIPOS ENUMERADOS (Para integridad de datos)
-- =====================================================================

-- Eliminamos tipos si existen para evitar errores al re-importar
DROP TYPE IF EXISTS estado_notificacion CASCADE;
DROP TYPE IF EXISTS canal_notificacion CASCADE;
DROP TYPE IF EXISTS severidad_evento CASCADE;
DROP TYPE IF EXISTS canal_2fa CASCADE;

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
-- 17. DATOS INICIALES (REMOVIDOS PARA USAR LARAVEL SEEDERS)
-- =====================================================================
-- Los datos de Áreas, Roles, Permisos, Estados, Categorías,
-- Prioridades y Distritos se han trasladado a:
-- 1. database/seeders/CatalogSeeder.php
-- 2. database/seeders/RoleSeeder.php
--
-- Ejecutar: php artisan db:seed

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