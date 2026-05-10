-- admin@localhost.com 123456%xdA
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Admin Local', 'admin@localhost.com', '$2y$10$HaawU8DOHk/34SUWbh7WIu2xhzFhYEiHDhGg9p4S8c9gNP1/O2ddW', 'admin')
ON CONFLICT (email) DO NOTHING;