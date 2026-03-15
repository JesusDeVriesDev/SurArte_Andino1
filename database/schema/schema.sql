-- ============================================================
-- SurArte Andino — Esquema completo de base de datos
-- Motor: PostgreSQL (Supabase)
-- ============================================================

-- Extensión para UUIDs
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- ─────────────────────────────────────────
-- ENUMS
-- ─────────────────────────────────────────
CREATE TYPE rol_usuario    AS ENUM ('visitante','artista','organizador','admin');
CREATE TYPE estado_evento  AS ENUM ('borrador','publicado','cancelado','finalizado');
CREATE TYPE estado_producto AS ENUM ('activo','inactivo','agotado');
CREATE TYPE estado_ticket  AS ENUM ('pendiente','pagado','usado','cancelado');
CREATE TYPE tipo_disciplina AS ENUM ('pintura','escultura','musica','danza','artesania','fotografia','literatura','teatro','otro');

-- ─────────────────────────────────────────
-- TABLA: usuarios (extiende auth.users de Supabase)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS usuarios (
    id          UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    auth_id     UUID UNIQUE REFERENCES auth.users(id) ON DELETE CASCADE,
    nombre      VARCHAR(120) NOT NULL,
    apellido    VARCHAR(120),
    email       VARCHAR(255) UNIQUE NOT NULL,
    telefono    VARCHAR(20),
    rol         rol_usuario DEFAULT 'visitante',
    avatar_url  TEXT,
    bio         TEXT,
    activo      BOOLEAN DEFAULT true,
    creado_en   TIMESTAMPTZ DEFAULT NOW(),
    actualizado_en TIMESTAMPTZ DEFAULT NOW()
);

-- ─────────────────────────────────────────
-- TABLA: artistas
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS artistas (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    usuario_id      UUID NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    nombre_artistico VARCHAR(150),
    disciplina      tipo_disciplina NOT NULL,
    disciplinas_extra tipo_disciplina[],
    municipio       VARCHAR(100) DEFAULT 'Pasto',
    departamento    VARCHAR(100) DEFAULT 'Nariño',
    pais            VARCHAR(80)  DEFAULT 'Colombia',
    website         TEXT,
    instagram       VARCHAR(120),
    facebook        VARCHAR(120),
    youtube         VARCHAR(120),
    portafolio_url  TEXT,
    verificado      BOOLEAN DEFAULT false,
    destacado       BOOLEAN DEFAULT false,
    visitas         INT DEFAULT 0,
    creado_en       TIMESTAMPTZ DEFAULT NOW(),
    actualizado_en  TIMESTAMPTZ DEFAULT NOW()
);

-- ─────────────────────────────────────────
-- TABLA: obras (portafolio del artista)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS obras (
    id          UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    artista_id  UUID NOT NULL REFERENCES artistas(id) ON DELETE CASCADE,
    titulo      VARCHAR(200) NOT NULL,
    descripcion TEXT,
    tecnica     VARCHAR(120),
    anio        SMALLINT,
    imagen_url  TEXT NOT NULL,
    en_venta    BOOLEAN DEFAULT false,
    precio      DECIMAL(12,2),
    creado_en   TIMESTAMPTZ DEFAULT NOW()
);

-- ─────────────────────────────────────────
-- TABLA: eventos
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS eventos (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    organizador_id  UUID NOT NULL REFERENCES usuarios(id),
    titulo          VARCHAR(200) NOT NULL,
    descripcion     TEXT,
    tipo            VARCHAR(80),
    imagen_url      TEXT,
    fecha_inicio    TIMESTAMPTZ NOT NULL,
    fecha_fin       TIMESTAMPTZ,
    lugar           VARCHAR(200),
    municipio       VARCHAR(100) DEFAULT 'Pasto',
    direccion       TEXT,
    latitud         DECIMAL(10,8),
    longitud        DECIMAL(11,8),
    capacidad       INT,
    precio_entrada  DECIMAL(10,2) DEFAULT 0,
    es_gratuito     BOOLEAN DEFAULT true,
    estado          estado_evento DEFAULT 'borrador',
    tags            TEXT[],
    creado_en       TIMESTAMPTZ DEFAULT NOW(),
    actualizado_en  TIMESTAMPTZ DEFAULT NOW()
);

-- ─────────────────────────────────────────
-- TABLA: tickets
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tickets (
    id           UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    evento_id    UUID NOT NULL REFERENCES eventos(id) ON DELETE CASCADE,
    usuario_id   UUID NOT NULL REFERENCES usuarios(id),
    codigo_qr    VARCHAR(64) UNIQUE NOT NULL DEFAULT encode(gen_random_bytes(16),'hex'),
    estado       estado_ticket DEFAULT 'pendiente',
    pago_ref     VARCHAR(120),
    precio_pagado DECIMAL(10,2),
    usado_en     TIMESTAMPTZ,
    creado_en    TIMESTAMPTZ DEFAULT NOW()
);

-- ─────────────────────────────────────────
-- TABLA: productos (tienda)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS productos (
    id           UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    artista_id   UUID NOT NULL REFERENCES artistas(id) ON DELETE CASCADE,
    nombre       VARCHAR(200) NOT NULL,
    descripcion  TEXT,
    categoria    VARCHAR(80),
    precio       DECIMAL(12,2) NOT NULL,
    stock        INT DEFAULT 1,
    imagenes     TEXT[],
    peso_gramos  INT,
    estado       estado_producto DEFAULT 'activo',
    ventas       INT DEFAULT 0,
    creado_en    TIMESTAMPTZ DEFAULT NOW(),
    actualizado_en TIMESTAMPTZ DEFAULT NOW()
);

-- ─────────────────────────────────────────
-- TABLA: pedidos
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS pedidos (
    id              UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    usuario_id      UUID NOT NULL REFERENCES usuarios(id),
    total           DECIMAL(12,2) NOT NULL,
    estado          VARCHAR(40) DEFAULT 'pendiente',
    pago_ref        VARCHAR(120),
    direccion_envio JSONB,
    creado_en       TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS pedido_items (
    id          UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    pedido_id   UUID NOT NULL REFERENCES pedidos(id) ON DELETE CASCADE,
    producto_id UUID NOT NULL REFERENCES productos(id),
    cantidad    INT NOT NULL DEFAULT 1,
    precio_unit DECIMAL(12,2) NOT NULL
);

-- ─────────────────────────────────────────
-- TABLA: comunidad (posts del foro)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS posts (
    id          UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    autor_id    UUID NOT NULL REFERENCES usuarios(id),
    titulo      VARCHAR(300),
    contenido   TEXT NOT NULL,
    categoria   VARCHAR(80),
    tags        TEXT[],
    likes       INT DEFAULT 0,
    pinned      BOOLEAN DEFAULT false,
    creado_en   TIMESTAMPTZ DEFAULT NOW(),
    actualizado_en TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS comentarios (
    id        UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    post_id   UUID NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    autor_id  UUID NOT NULL REFERENCES usuarios(id),
    contenido TEXT NOT NULL,
    creado_en TIMESTAMPTZ DEFAULT NOW()
);

-- ─────────────────────────────────────────
-- TABLA: suscriptores (comunidad fundadora)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS suscriptores (
    id        UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    email     VARCHAR(255) UNIQUE NOT NULL,
    creado_en TIMESTAMPTZ DEFAULT NOW()
);

-- ─────────────────────────────────────────
-- ÍNDICES DE RENDIMIENTO
-- ─────────────────────────────────────────
CREATE INDEX IF NOT EXISTS idx_artistas_usuario    ON artistas(usuario_id);
CREATE INDEX IF NOT EXISTS idx_artistas_disciplina ON artistas(disciplina);
CREATE INDEX IF NOT EXISTS idx_artistas_municipio  ON artistas(municipio);
CREATE INDEX IF NOT EXISTS idx_eventos_fecha       ON eventos(fecha_inicio);
CREATE INDEX IF NOT EXISTS idx_eventos_estado      ON eventos(estado);
CREATE INDEX IF NOT EXISTS idx_eventos_municipio   ON eventos(municipio);
CREATE INDEX IF NOT EXISTS idx_productos_artista   ON productos(artista_id);
CREATE INDEX IF NOT EXISTS idx_productos_estado    ON productos(estado);
CREATE INDEX IF NOT EXISTS idx_tickets_usuario     ON tickets(usuario_id);
CREATE INDEX IF NOT EXISTS idx_tickets_evento      ON tickets(evento_id);
CREATE INDEX IF NOT EXISTS idx_posts_autor         ON posts(autor_id);

-- ─────────────────────────────────────────
-- TRIGGERS: actualizado_en automático
-- ─────────────────────────────────────────
CREATE OR REPLACE FUNCTION set_updated_at()
RETURNS TRIGGER AS $$
BEGIN NEW.actualizado_en = NOW(); RETURN NEW; END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_usuarios_updated   BEFORE UPDATE ON usuarios   FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_artistas_updated   BEFORE UPDATE ON artistas   FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_eventos_updated    BEFORE UPDATE ON eventos    FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_productos_updated  BEFORE UPDATE ON productos  FOR EACH ROW EXECUTE FUNCTION set_updated_at();
CREATE TRIGGER trg_posts_updated      BEFORE UPDATE ON posts      FOR EACH ROW EXECUTE FUNCTION set_updated_at();
