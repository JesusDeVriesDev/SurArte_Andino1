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

CREATE INDEX IF NOT EXISTS idx_eventos_fecha      ON eventos(fecha_inicio);
CREATE INDEX IF NOT EXISTS idx_eventos_categoria  ON eventos(categoria);
CREATE INDEX IF NOT EXISTS idx_productos_artista  ON productos(artista_id);
CREATE INDEX IF NOT EXISTS idx_artistas_usuario   ON artistas(usuario_id);
CREATE INDEX IF NOT EXISTS idx_artistas_verif     ON artistas(verificado);
CREATE INDEX IF NOT EXISTS idx_carrito_usuario    ON carrito_items(usuario_id);
CREATE INDEX IF NOT EXISTS idx_pedidos_usuario    ON pedidos(usuario_id);

-- admin@localhost.com 123456%xdA
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Admin Local', 'admin@localhost.com', '$2y$10$HaawU8DOHk/34SUWbh7WIu2xhzFhYEiHDhGg9p4S8c9gNP1/O2ddW', 'admin')
ON CONFLICT (email) DO NOTHING;

INSERT INTO artistas (nombre, disciplina, bio, municipio, verificado) VALUES
('María Elena Criollo',   'Barniz de Pasto',        'Maestra en mopa-mopa, 20+ años de experiencia.', 'Pasto',    TRUE),
('Carlos Andrés Pantoja', 'Cerámica Contemporánea', 'Escultor con presencia en bienales nacionales.', 'Pasto',    TRUE),
('Rosa Inés Muñoz',       'Música Andina',           'Directora del grupo Voz del Galeras.',           'Pasto',    TRUE)
ON CONFLICT DO NOTHING;

INSERT INTO eventos (titulo, categoria, lugar, municipio, latitud, longitud, fecha_inicio, precio) VALUES
('Festival de Música Andina del Sur', 'musica',    'Teatro Guillermo León Valencia', 'Pasto',    1.2136, -77.2811, NOW() + INTERVAL '8 days',  0),
('Exposición: Barniz de Pasto',       'arte',      'Museo Juan Lorenzo Lucero',      'Pasto',    1.2144, -77.2793, NOW() + INTERVAL '15 days', 0),
('Taller de Tejeduría Camëntsá',      'artesania', 'Casa de la Cultura',             'Sibundoy', 1.1981, -76.9203, NOW() + INTERVAL '29 days', 15000)
ON CONFLICT DO NOTHING;

INSERT INTO productos (artista_id, nombre, descripcion, categoria, precio, stock, imagen_url) VALUES
((SELECT id FROM artistas WHERE nombre='María Elena Criollo' LIMIT 1),   'Caja decorativa en Barniz de Pasto',     'Caja artesanal con técnica mopa-mopa.',          'artesania', 85000,  5, NULL),
((SELECT id FROM artistas WHERE nombre='María Elena Criollo' LIMIT 1),   'Plato decorativo Barniz de Pasto',       'Plato ornamental con diseños nariñenses.',       'artesania', 65000,  3, NULL),
((SELECT id FROM artistas WHERE nombre='Carlos Andrés Pantoja' LIMIT 1), 'Escultura cerámica volcánica',           'Escultura inspirada en el volcán Galeras.',     'arte',      180000, 2, NULL),
((SELECT id FROM artistas WHERE nombre='Carlos Andrés Pantoja' LIMIT 1), 'Vasija artística contemporánea',         'Pieza única de cerámica con acabado natural.',   'arte',      95000,  4, NULL),
((SELECT id FROM artistas WHERE nombre='Rosa Inés Muñoz' LIMIT 1),       'Disco Música Andina - Voces del Galeras','Álbum con música tradicional andina de Nariño.','musica',    30000,  10, NULL),
((SELECT id FROM artistas WHERE nombre='Rosa Inés Muñoz' LIMIT 1),       'Quena Andina artesanal',                 'Quena elaborada a mano por artesanos locales.',  'musica',    45000,  7, NULL)
ON CONFLICT DO NOTHING;
