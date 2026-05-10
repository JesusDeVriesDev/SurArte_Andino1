-- ============================================================
-- SurArte Andino — Row Level Security (RLS) Policies
-- Supabase: seguridad a nivel de fila por rol
-- ============================================================

-- Activar RLS en todas las tablas
ALTER TABLE usuarios   ENABLE ROW LEVEL SECURITY;
ALTER TABLE artistas   ENABLE ROW LEVEL SECURITY;
ALTER TABLE eventos    ENABLE ROW LEVEL SECURITY;
ALTER TABLE productos  ENABLE ROW LEVEL SECURITY;
ALTER TABLE pedidos    ENABLE ROW LEVEL SECURITY;
ALTER TABLE carrito_items ENABLE ROW LEVEL SECURITY;
ALTER TABLE pedido_items ENABLE ROW LEVEL SECURITY;
ALTER TABLE producto_comentarios ENABLE ROW LEVEL SECURITY;

-- ─────────────────────────────────────────
-- USUARIOS: lectura pública, escritura solo propio
-- ─────────────────────────────────────────
CREATE POLICY "usuarios_select_public"
    ON usuarios FOR SELECT USING (true);

CREATE POLICY "usuarios_update_own"
    ON usuarios FOR UPDATE
    USING (auth.uid() = auth_id);

-- ─────────────────────────────────────────
-- ARTISTAS: lectura pública, escritura solo propio
-- ─────────────────────────────────────────
CREATE POLICY "artistas_select_public"
    ON artistas FOR SELECT USING (true);

CREATE POLICY "artistas_insert_own"
    ON artistas FOR INSERT
    WITH CHECK (usuario_id = (SELECT id FROM usuarios WHERE auth_id = auth.uid()));

CREATE POLICY "artistas_update_own"
    ON artistas FOR UPDATE
    USING (usuario_id = (SELECT id FROM usuarios WHERE auth_id = auth.uid()));

-- ─────────────────────────────────────────
-- EVENTOS: lectura pública, escritura organizador/admin
-- ─────────────────────────────────────────
CREATE POLICY "eventos_select_public"
    ON eventos FOR SELECT USING (estado = 'publicado');

CREATE POLICY "eventos_select_own"
    ON eventos FOR SELECT
    USING (organizador_id = (SELECT id FROM usuarios WHERE auth_id = auth.uid()));

CREATE POLICY "eventos_insert_auth"
    ON eventos FOR INSERT
    WITH CHECK (
        (SELECT rol FROM usuarios WHERE auth_id = auth.uid())
        IN ('organizador', 'admin')
    );

CREATE POLICY "eventos_update_own"
    ON eventos FOR UPDATE
    USING (organizador_id = (SELECT id FROM usuarios WHERE auth_id = auth.uid()));

-- ─────────────────────────────────────────
-- PRODUCTOS: lectura pública activos, escritura artistas
-- ─────────────────────────────────────────
CREATE POLICY "productos_select_active"
    ON productos FOR SELECT USING (estado = 'activo');

CREATE POLICY "productos_manage_own"
    ON productos FOR ALL
    USING (
        artista_id IN (
            SELECT id FROM artistas
            WHERE usuario_id = (SELECT id FROM usuarios WHERE auth_id = auth.uid())
        )
    );

-- ─────────────────────────────────────────
-- COMENTARIOS: lectura pública, escritura autenticados
-- ─────────────────────────────────────────
CREATE POLICY "comentarios_select_public"
    ON producto_comentarios FOR SELECT USING (true);

CREATE POLICY "comentarios_insert_auth"
    ON producto_comentarios FOR INSERT
    WITH CHECK (auth.uid() IS NOT NULL);

CREATE POLICY "comentarios_update_own"
    ON producto_comentarios FOR UPDATE
    USING (usuario_id = (SELECT id FROM usuarios WHERE auth_id = auth.uid()));

