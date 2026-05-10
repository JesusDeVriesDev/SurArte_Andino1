INSERT INTO artistas (nombre, disciplina, bio, municipio, verificado) VALUES
('María Elena Criollo',   'Barniz de Pasto',        'Maestra en mopa-mopa, 20+ años de experiencia.', 'Pasto',    TRUE),
('Carlos Andrés Pantoja', 'Cerámica Contemporánea', 'Escultor con presencia en bienales nacionales.', 'Pasto',    TRUE),
('Rosa Inés Muñoz',       'Música Andina',           'Directora del grupo Voz del Galeras.',           'Pasto',    TRUE)
ON CONFLICT DO NOTHING;