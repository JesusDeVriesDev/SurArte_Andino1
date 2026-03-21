<?php
$pageTitle = 'Producto';
$pageId    = 'tienda';
require_once '../_layout/head.php';
require_once '../../config/db.php';

$prodId = $_GET['id'] ?? null;
if (!$prodId) { header('Location: ' . $base . '/src/tienda/tienda.php'); exit; }

try {
    $stmt = db()->prepare(
        "SELECT p.*, a.nombre AS artista_nombre, a.municipio AS artista_municipio,
                a.id AS artista_id, a.foto_url AS artista_foto
         FROM productos p
         JOIN artistas a ON p.artista_id = a.id
         WHERE p.id = ?::uuid AND p.activo = TRUE AND a.verificado = TRUE"
    );
    $stmt->execute([$prodId]);
    $p = $stmt->fetch();
    if (!$p) { header('Location: ' . $base . '/src/tienda/tienda.php'); exit; }

    $comentarios = db()->prepare(
        "SELECT pc.id, pc.texto, pc.creado_en, pc.editado_en, pc.usuario_id,
                u.nombre AS usuario_nombre, u.rol AS usuario_rol
         FROM producto_comentarios pc
         JOIN usuarios u ON pc.usuario_id = u.id
         WHERE pc.producto_id = ?::uuid
         ORDER BY pc.creado_en DESC"
    );
    $comentarios->execute([$prodId]);
    $comentarios = $comentarios->fetchAll();

    $pageTitle = htmlspecialchars($p['nombre']) . ' — SurArte Andino';

} catch (PDOException $e) {
    $dbError = $e->getMessage();
    $p = null; $comentarios = [];
}

$catIcons  = ['musica'=>'🎵','arte'=>'🎨','artesania'=>'🧵','danza'=>'💃','literatura'=>'📖','otro'=>'✨'];
$catLabels = ['musica'=>'Música','arte'=>'Arte','artesania'=>'Artesanía','danza'=>'Danza','literatura'=>'Literatura','otro'=>'Otro'];
$rolBadge  = ['admin'=>'badge-clay','artista'=>'badge-sky','organizador'=>'badge-gold','usuario'=>'badge-muted','visitante'=>'badge-muted'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="<?= $base ?>/src/tienda/tienda.css"/>
  <style>
    .prod-layout{display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:start;padding-top:44px;margin-bottom:60px}
    .prod-img-wrap{border-radius:var(--r-lg);overflow:hidden;aspect-ratio:1/1;background:var(--cream-dk);display:flex;align-items:center;justify-content:center;font-size:5rem}
    .prod-img-wrap img{width:100%;height:100%;object-fit:cover}
    .prod-eyebrow{font-family:var(--ff-m);font-size:.78rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#5a2d0c;margin-bottom:12px;display:flex;align-items:center;gap:8px}
    .prod-name{font-family:var(--ff-d);font-size:clamp(1.8rem,3.5vw,2.8rem);font-weight:900;color:#0d0902;line-height:1;letter-spacing:-.02em;margin-bottom:16px}
    .prod-price{font-family:var(--ff-d);font-size:2rem;font-weight:900;color:var(--clay);margin-bottom:20px}
    .prod-desc{font-size:1.05rem;font-weight:400;color:#1A1208;line-height:1.82;margin-bottom:24px}
    .prod-meta-row{display:flex;align-items:center;gap:10px;margin-bottom:12px;font-family:var(--ff-m);font-size:.78rem;font-weight:500;color:#3d2b10}
    .prod-artista{display:flex;align-items:center;gap:12px;background:#fff;border:1px solid var(--cream-dk);border-radius:var(--r-lg);padding:14px 18px;margin-bottom:24px;text-decoration:none;color:inherit;transition:border-color .22s,box-shadow .22s}
    .prod-artista:hover{border-color:var(--gold);box-shadow:0 4px 16px rgba(201,146,42,.12)}
    .prod-artista-avatar{width:44px;height:44px;border-radius:50%;background:var(--cream-dk);display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;overflow:hidden}
    .prod-artista-avatar img{width:100%;height:100%;object-fit:cover}
    .prod-artista-name{font-family:var(--ff-d);font-size:1.1rem;font-weight:700;color:#0d0902}
    .prod-artista-loc{font-family:var(--ff-m);font-size:.8rem;font-weight:500;color:#3d2b10;margin-top:2px}
    /* Comentarios */
    .comments-section{margin-top:60px;border-top:1px solid var(--cream-dk);padding-top:40px}
    .comments-title{font-family:var(--ff-d);font-size:clamp(1.4rem,2.5vw,2rem);font-weight:900;color:#0d0902;margin-bottom:8px}
    .comments-count{font-family:var(--ff-m);font-size:1.1rem;font-weight:500;color:#3d2b10;margin-bottom:28px}
    .comment-form{background:#fff;border:1px solid var(--cream-dk);border-radius:var(--r-lg);padding:20px 24px;margin-bottom:32px}
    .comment-form textarea{width:100%;border:1.5px solid #EDE4D0;border-radius:var(--r);padding:11px 14px;font-family:var(--ff-b);font-size:1rem;font-weight:400;color:#0d0902;resize:vertical;min-height:90px;transition:border-color .22s,box-shadow .22s;background:#FFFEF9}
    .comment-form textarea:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(201,146,42,.1)}
    .comment-form textarea::placeholder{color:rgba(26,18,8,.45)}
    .comment-form-actions{display:flex;justify-content:flex-end;margin-top:10px}
    .comment-card{background:#fff;border:1px solid var(--cream-dk);border-radius:var(--r-lg);padding:18px 22px;margin-bottom:14px;transition:border-color .22s}
    .comment-card:hover{border-color:rgba(201,146,42,.2)}
    .comment-header{display:flex;align-items:center;gap:10px;margin-bottom:10px}
    .comment-avatar{width:36px;height:36px;border-radius:50%;background:rgba(29,78,107,.12);color:var(--sky);display:flex;align-items:center;justify-content:center;font-family:var(--ff-d);font-size:.9rem;font-weight:900;flex-shrink:0}
    .comment-author{font-family:var(--ff-d);font-size:.95rem;font-weight:700;color:#0d0902}
    .comment-meta{font-family:var(--ff-m);font-size:.62rem;font-weight:500;color:#3d2b10;margin-top:2px}
    .comment-text{font-family:var(--ff-b);font-size:.98rem;font-weight:400;color:#1A1208;line-height:1.75;white-space:pre-wrap}
    .comment-text-edit{width:100%;border:1.5px solid var(--gold);border-radius:var(--r);padding:9px 12px;font-family:var(--ff-b);font-size:.98rem;color:#0d0902;resize:vertical;min-height:70px;background:#FFFEF9}
    .comment-actions{display:flex;gap:8px;margin-top:10px;justify-content:flex-end}
    .comment-btn{font-family:var(--ff-m);font-size:.62rem;font-weight:600;letter-spacing:.06em;text-transform:uppercase;padding:5px 12px;border-radius:var(--r);border:none;cursor:pointer;transition:all .2s}
    .comment-btn-edit{background:rgba(29,78,107,.08);color:var(--sky)}.comment-btn-edit:hover{background:var(--sky);color:#fff}
    .comment-btn-del{background:rgba(239,68,68,.08);color:#ef4444}.comment-btn-del:hover{background:#ef4444;color:#fff}
    .comment-btn-save{background:var(--gold);color:var(--ink)}.comment-btn-save:hover{background:var(--gold-lt)}
    .comment-btn-cancel{background:rgba(26,18,8,.06);color:rgba(26,18,8,.6)}.comment-btn-cancel:hover{background:rgba(26,18,8,.12)}
    .back-link{font-family:var(--ff-m);font-size:.78rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#3d2b10;text-decoration:none;display:inline-flex;align-items:center;gap:6px;padding-top:32px;margin-bottom:0}
    .back-link:hover{color:var(--gold)}
    @media(max-width:860px){.prod-layout{grid-template-columns:1fr}}
  </style>
</head>
<main>

  <a href="<?= $base ?>/src/tienda/tienda.php" class="back-link">← Volver a la tienda</a>

  <?php if (isset($dbError)): ?>
    <div class="alert alert-err" style="margin-top:20px">⚠️ <?= htmlspecialchars($dbError) ?></div>
  <?php endif; ?>

  <?php if ($p): ?>
  <div class="prod-layout">
    <!-- Imagen -->
    <div class="prod-img-wrap">
      <?php if (!empty($p['imagen_url']) && str_starts_with($p['imagen_url'],'http') && !str_contains($p['imagen_url'],'example.com')): ?>
        <img src="<?= htmlspecialchars($p['imagen_url']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>"/>
      <?php else: ?>
        <?= $catIcons[$p['categoria']] ?? '🛍️' ?>
      <?php endif; ?>
    </div>

    <!-- Info -->
    <div>
      <div class="prod-eyebrow">
        <?= $catIcons[$p['categoria']] ?? '✨' ?>
        <?= htmlspecialchars($catLabels[$p['categoria']] ?? ucfirst($p['categoria'])) ?>
      </div>
      <h1 class="prod-name"><?= htmlspecialchars($p['nombre']) ?></h1>
      <div class="prod-price">
        <?= $p['precio'] > 0 ? '$'.number_format((float)$p['precio'],0,',','.') : 'Gratis' ?>
      </div>

      <?php if ($p['descripcion']): ?>
        <p class="prod-desc" style="font-size:1.3rem"><?= nl2br(htmlspecialchars($p['descripcion'])) ?></p>
      <?php endif; ?>

      <div class="prod-meta-row">
        <?php
        $stock = (int)$p['stock'];
        if ($stock > 3): ?>
          <span class="badge badge-green" style="font-size:.85rem">✓ En stock (<?= $stock ?>)</span>
        <?php elseif ($stock > 0): ?>
          <span class="badge badge-gold" style="font-size:.85rem">⚠ Últimas <?= $stock ?> unidades</span>
        <?php else: ?>
          <span class="badge badge-clay" style="font-size:.85rem">Agotado</span>
        <?php endif; ?>
        <span style="color:rgba(26,18,8,.3);font-size:.9rem">·</span>
        <span style="font-size:.95rem"> <?= count($comentarios) ?> comentario<?= count($comentarios) !== 1 ? 's' : '' ?></span>
      </div>

      <!-- Artista -->
      <a href="<?= $base ?>/src/artistas/perfil/index.php?id=<?= urlencode($p['artista_id']) ?>" class="prod-artista">
        <div class="prod-artista-avatar">
          <?php if (!empty($p['artista_foto'])): ?>
            <img src="<?= htmlspecialchars($p['artista_foto']) ?>" alt=""/>
          <?php else: ?>
            <?= $catIcons[$p['categoria']] ?? '🎨' ?>
          <?php endif; ?>
        </div>
        <div>
          <div class="prod-artista-name">🎨 <?= htmlspecialchars($p['artista_nombre']) ?></div>
          <?php if ($p['artista_municipio']): ?>
            <div class="prod-artista-loc">📍 <?= htmlspecialchars($p['artista_municipio']) ?></div>
          <?php endif; ?>
        </div>
        <span style="margin-left:auto;font-family:var(--ff-m);font-size:.8rem;color:var(--sky)">Ver perfil →</span>
      </a>

      <!-- Botón agregar al carrito -->
      <?php if ($user): ?>
        <?php if ($stock > 0): ?>
          <button class="btn btn-gold" style="width:100%;justify-content:center;font-size:.88rem"
            id="btnAgregar"
            data-id="<?= htmlspecialchars($p['id']) ?>"
            data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
            data-precio="<?= $p['precio'] ?>">
            🛒 Agregar al carrito
          </button>
        <?php else: ?>
          <button class="btn" style="width:100%;justify-content:center;font-size:.90rem;background:var(--cream-dk);color:#000000;cursor:not-allowed" disabled>
            Producto agotado
          </button>
        <?php endif; ?>
      <?php else: ?>
        <a href="<?= $base ?>/src/auth/login/index.php" class="btn btn-gold" style="width:100%;justify-content:center;font-size:.95rem">
          Inicia sesión para comprar
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── Comentarios ── -->
  <div class="comments-section">
    <h2 class="comments-title">Comentarios</h2>
    <div class="comments-count"><?= count($comentarios) ?> comentario<?= count($comentarios) !== 1 ? 's' : '' ?> sobre este producto</div>

    <?php if ($user): ?>
    <div class="comment-form">
      <textarea id="nuevoComentario" placeholder="Escribe tu comentario sobre este producto…"></textarea>
      <div class="comment-form-actions">
        <button class="btn btn-gold" style="font-size:.82rem" onclick="enviarComentario()">Publicar comentario</button>
      </div>
    </div>
    <?php else: ?>
    <div style="background:#fff;border:1px solid var(--cream-dk);border-radius:var(--r-lg);padding:20px 24px;margin-bottom:32px;text-align:center">
      <p style="font-family:var(--ff-b);font-size:1.1rem;color:#000000;margin-bottom:12px">Inicia sesión para dejar un comentario</p>
      <a href="<?= $base ?>/src/auth/login/index.php" class="btn btn-gold" style="font-size:.82rem">Iniciar sesión</a>
    </div>
    <?php endif; ?>

    <div id="comentariosList">
      <?php if (!empty($comentarios)): ?>
        <?php foreach ($comentarios as $c): ?>
        <div class="comment-card" id="comment-<?= $c['id'] ?>">
          <div class="comment-header">
            <div class="comment-avatar"><?= mb_strtoupper(mb_substr($c['usuario_nombre'], 0, 1)) ?></div>
            <div>
              <div class="comment-author"><?= htmlspecialchars($c['usuario_nombre']) ?>
                <span class="badge <?= $rolBadge[$c['usuario_rol']] ?? 'badge-muted' ?>" style="font-size:.55rem;margin-left:6px;vertical-align:middle"><?= $c['usuario_rol'] ?></span>
              </div>
              <div class="comment-meta">
                <?= date('d/m/Y H:i', strtotime($c['creado_en'])) ?>
                <?php if ($c['editado_en']): ?> · <em>editado</em><?php endif; ?>
              </div>
            </div>
            <?php if ($user && $user['id'] === $c['usuario_id']): ?>
            <div class="comment-actions" style="margin-top:0;margin-left:auto">
              <button class="comment-btn comment-btn-edit" onclick="editarComentario('<?= $c['id'] ?>')">Editar</button>
              <button class="comment-btn comment-btn-del" onclick="eliminarComentario('<?= $c['id'] ?>')">Eliminar</button>
            </div>
            <?php endif; ?>
          </div>
          <div class="comment-text" id="texto-<?= $c['id'] ?>"><?= htmlspecialchars($c['texto']) ?></div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty" id="emptyComments">
          <div class="empty-icon">💬</div>
          <p style='font-size:1.5rem;font-weight:500;color:#000000'>Sé el primero en comentar este producto.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

</main>
<script>
const PROD_ID    = '<?= htmlspecialchars($prodId) ?>';
const BASE       = window.APP_BASE || '';
const USER_ID    = '<?= $user ? htmlspecialchars($_SESSION['user_id']) : '' ?>';

// ── Agregar al carrito ──
const btnAgregar = document.getElementById('btnAgregar');
if (btnAgregar) {
  btnAgregar.addEventListener('click', async () => {
    const id     = btnAgregar.dataset.id;
    const nombre = btnAgregar.dataset.nombre;
    btnAgregar.disabled = true; btnAgregar.textContent = '⏳ Agregando…';
    try {
      const r = await fetch(BASE + '/api/carrito/add.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ producto_id: id, cantidad: 1 })
      });
      const d = await r.json();
      if (d.success) {
        toast('"' + nombre + '" agregado al carrito', 'ok');
        btnAgregar.textContent = '✓ En el carrito';
        if (typeof updateNavCartBadge === 'function') updateNavCartBadge(d.data.total_items);
        setTimeout(() => { btnAgregar.textContent = '🛒 Agregar al carrito'; btnAgregar.disabled = false; }, 2000);
      } else {
        toast(d.message || 'Error al agregar', 'err');
        btnAgregar.disabled = false; btnAgregar.textContent = '🛒 Agregar al carrito';
      }
    } catch(e) {
      toast('Error de conexión', 'err');
      btnAgregar.disabled = false; btnAgregar.textContent = '🛒 Agregar al carrito';
    }
  });
}

// ── Comentarios ──
async function enviarComentario() {
  const ta = document.getElementById('nuevoComentario');
  const texto = ta.value.trim();
  if (!texto) { toast('Escribe un comentario primero', 'warn'); return; }

  try {
    const r = await fetch(BASE + '/api/carrito/comentarios.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ accion:'crear', producto_id: PROD_ID, texto })
    });
    const d = await r.json();
    if (d.success) {
      ta.value = '';
      agregarComentarioDOM(d.data);
      toast('Comentario publicado', 'ok');
    } else { toast(d.message || 'Error', 'err'); }
  } catch(e) { toast('Error de conexión', 'err'); }
}

function agregarComentarioDOM(c) {
  const empty = document.getElementById('emptyComments');
  if (empty) empty.remove();

  const esMio = c.usuario_id === USER_ID;
  const html = `
    <div class="comment-card" id="comment-${c.id}">
      <div class="comment-header">
        <div class="comment-avatar">${c.nombre.charAt(0).toUpperCase()}</div>
        <div>
          <div class="comment-author">${c.nombre}
            <span class="badge badge-muted" style="font-size:.55rem;margin-left:6px;vertical-align:middle">${c.rol}</span>
          </div>
          <div class="comment-meta">Ahora mismo</div>
        </div>
        ${esMio ? `<div class="comment-actions" style="margin-top:0;margin-left:auto">
          <button class="comment-btn comment-btn-edit" onclick="editarComentario('${c.id}')">Editar</button>
          <button class="comment-btn comment-btn-del" onclick="eliminarComentario('${c.id}')">Eliminar</button>
        </div>` : ''}
      </div>
      <div class="comment-text" id="texto-${c.id}">${c.texto}</div>
    </div>`;
  document.getElementById('comentariosList').insertAdjacentHTML('afterbegin', html);
}

function editarComentario(id) {
  const textoEl = document.getElementById('texto-' + id);
  const textoActual = textoEl.textContent;
  textoEl.innerHTML = `
    <textarea class="comment-text-edit" id="edit-${id}">${textoActual}</textarea>
    <div class="comment-actions">
      <button class="comment-btn comment-btn-cancel" onclick="cancelarEdicion('${id}', \`${textoActual.replace(/`/g,"'")}\`)">Cancelar</button>
      <button class="comment-btn comment-btn-save" onclick="guardarEdicion('${id}')">Guardar</button>
    </div>`;
}

function cancelarEdicion(id, textoOriginal) {
  const textoEl = document.getElementById('texto-' + id);
  textoEl.innerHTML = textoOriginal;
}

async function guardarEdicion(id) {
  const ta = document.getElementById('edit-' + id);
  const texto = ta.value.trim();
  if (!texto) { toast('El comentario no puede estar vacío', 'warn'); return; }

  try {
    const r = await fetch(BASE + '/api/carrito/comentarios.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ accion:'editar', id, texto })
    });
    const d = await r.json();
    if (d.success) {
      const textoEl = document.getElementById('texto-' + id);
      textoEl.innerHTML = d.data.texto;
      toast('Comentario actualizado', 'ok');
    } else { toast(d.message || 'Error', 'err'); }
  } catch(e) { toast('Error de conexión', 'err'); }
}

async function eliminarComentario(id) {
  if (!confirm('¿Eliminar este comentario?')) return;
  try {
    const r = await fetch(BASE + '/api/carrito/comentarios.php', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ accion:'eliminar', id })
    });
    const d = await r.json();
    if (d.success) {
      document.getElementById('comment-' + id)?.remove();
      toast('Comentario eliminado', 'ok');
    } else { toast(d.message || 'Error', 'err'); }
  } catch(e) { toast('Error de conexión', 'err'); }
}
</script>
<script src="<?= $base ?>/src/tienda/tienda.js"></script>
</body>
</html>