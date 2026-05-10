CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "unaccent";

CREATE TABLE IF NOT EXISTS usuarios (
    id         UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    nombre     VARCHAR(120) NOT NULL,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   TEXT         NOT NULL,
    rol        VARCHAR(20)  NOT NULL DEFAULT 'visitante'
                            CHECK (rol IN ('visitante','artista','organizador','admin')),
    activo     BOOLEAN      NOT NULL DEFAULT TRUE,
    avatar_url TEXT,
    bio        TEXT,
    telefono   VARCHAR(30),
    creado_en  TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_en TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS artistas (
    id           UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id   UUID         REFERENCES usuarios(id) ON DELETE CASCADE,
    nombre       VARCHAR(180) NOT NULL,
    disciplina   VARCHAR(120),
    bio          TEXT,
    municipio    VARCHAR(100),
    foto_url     TEXT,
    instagram    VARCHAR(120),
    facebook     VARCHAR(120),
    website      TEXT,
    verificado   BOOLEAN      NOT NULL DEFAULT FALSE,
    creado_en    TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS eventos (
    id              UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    organizador_id  UUID         REFERENCES usuarios(id) ON DELETE SET NULL,
    titulo          VARCHAR(200) NOT NULL,
    descripcion     TEXT,
    categoria       VARCHAR(50)  CHECK (categoria IN ('musica','arte','artesania','danza','literatura','otro')),
    lugar           VARCHAR(200),
    municipio       VARCHAR(100),
    latitud         NUMERIC(9,6),
    longitud        NUMERIC(9,6),
    fecha_inicio    TIMESTAMPTZ  NOT NULL,
    fecha_fin       TIMESTAMPTZ,
    precio          NUMERIC(12,2) NOT NULL DEFAULT 0,
    aforo           INT,
    imagen_url      TEXT,
    activo          BOOLEAN      NOT NULL DEFAULT TRUE,
    creado_en       TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS productos (
    id          UUID          PRIMARY KEY DEFAULT gen_random_uuid(),
    artista_id  UUID          REFERENCES artistas(id) ON DELETE CASCADE,
    nombre      VARCHAR(200)  NOT NULL,
    descripcion TEXT,
    categoria   VARCHAR(80),
    precio      NUMERIC(12,2) NOT NULL,
    stock       INT           NOT NULL DEFAULT 1,
    imagen_url  TEXT,
    activo      BOOLEAN       NOT NULL DEFAULT TRUE,
    creado_en   TIMESTAMPTZ   NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS carrito_items (
    id          UUID          PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id  UUID          NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    producto_id UUID          NOT NULL REFERENCES productos(id) ON DELETE CASCADE,
    cantidad    INT           NOT NULL DEFAULT 1,
    creado_en   TIMESTAMPTZ   NOT NULL DEFAULT NOW(),
    UNIQUE (usuario_id, producto_id)
);

CREATE TABLE IF NOT EXISTS pedidos (
    id              UUID          PRIMARY KEY DEFAULT gen_random_uuid(),
    usuario_id      UUID          NOT NULL REFERENCES usuarios(id),
    total           NUMERIC(12,2) NOT NULL,
    estado          VARCHAR(40)   NOT NULL DEFAULT 'pagado'
                                  CHECK (estado IN ('pagado','enviado','entregado','cancelado')),
    direccion_envio TEXT,
    creado_en       TIMESTAMPTZ   NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS pedido_items (
    id          UUID          PRIMARY KEY DEFAULT gen_random_uuid(),
    pedido_id   UUID          NOT NULL REFERENCES pedidos(id) ON DELETE CASCADE,
    producto_id UUID          NOT NULL REFERENCES productos(id),
    nombre_snap VARCHAR(200)  NOT NULL,
    precio_snap NUMERIC(12,2) NOT NULL,
    cantidad    INT           NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS producto_comentarios (
    id          UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    producto_id UUID         NOT NULL REFERENCES productos(id) ON DELETE CASCADE,
    usuario_id  UUID         NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    texto       TEXT         NOT NULL CHECK (char_length(texto) BETWEEN 1 AND 1000),
    creado_en   TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    editado_en  TIMESTAMPTZ
);

CREATE INDEX IF NOT EXISTS idx_eventos_fecha      ON eventos(fecha_inicio);
CREATE INDEX IF NOT EXISTS idx_eventos_categoria  ON eventos(categoria);
CREATE INDEX IF NOT EXISTS idx_productos_artista  ON productos(artista_id);
CREATE INDEX IF NOT EXISTS idx_artistas_usuario   ON artistas(usuario_id);
CREATE INDEX IF NOT EXISTS idx_artistas_verif     ON artistas(verificado);
CREATE INDEX IF NOT EXISTS idx_carrito_usuario    ON carrito_items(usuario_id);
CREATE INDEX IF NOT EXISTS idx_pedidos_usuario    ON pedidos(usuario_id);
 
CREATE INDEX IF NOT EXISTS idx_comentarios_producto ON producto_comentarios(producto_id);
CREATE INDEX IF NOT EXISTS idx_comentarios_usuario  ON producto_comentarios(usuario_id);