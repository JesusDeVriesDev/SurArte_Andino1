INSERT INTO eventos (titulo, categoria, lugar, municipio, latitud, longitud, fecha_inicio, precio) VALUES
('Festival de Música Andina del Sur', 'musica',    'Teatro Guillermo León Valencia', 'Pasto',    1.2136, -77.2811, NOW() + INTERVAL '8 days',  0),
('Exposición: Barniz de Pasto',       'arte',      'Museo Juan Lorenzo Lucero',      'Pasto',    1.2144, -77.2793, NOW() + INTERVAL '15 days', 0),
('Taller de Tejeduría Camëntsá',      'artesania', 'Casa de la Cultura',             'Sibundoy', 1.1981, -76.9203, NOW() + INTERVAL '29 days', 15000)
ON CONFLICT DO NOTHING;