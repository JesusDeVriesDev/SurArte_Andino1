INSERT INTO productos (artista_id, nombre, descripcion, categoria, precio, stock, imagen_url) VALUES
((SELECT id FROM artistas WHERE nombre='María Elena Criollo' LIMIT 1),   'Caja decorativa en Barniz de Pasto',     'Caja artesanal con técnica mopa-mopa.',          'artesania', 85000,  5, NULL),
((SELECT id FROM artistas WHERE nombre='María Elena Criollo' LIMIT 1),   'Plato decorativo Barniz de Pasto',       'Plato ornamental con diseños nariñenses.',       'artesania', 65000,  3, NULL),
((SELECT id FROM artistas WHERE nombre='Carlos Andrés Pantoja' LIMIT 1), 'Escultura cerámica volcánica',           'Escultura inspirada en el volcán Galeras.',     'arte',      180000, 2, NULL),
((SELECT id FROM artistas WHERE nombre='Carlos Andrés Pantoja' LIMIT 1), 'Vasija artística contemporánea',         'Pieza única de cerámica con acabado natural.',   'arte',      95000,  4, NULL),
((SELECT id FROM artistas WHERE nombre='Rosa Inés Muñoz' LIMIT 1),       'Disco Música Andina - Voces del Galeras','Álbum con música tradicional andina de Nariño.','musica',    30000,  10, NULL),
((SELECT id FROM artistas WHERE nombre='Rosa Inés Muñoz' LIMIT 1),       'Quena Andina artesanal',                 'Quena elaborada a mano por artesanos locales.',  'musica',    45000,  7, NULL)
ON CONFLICT DO NOTHING;