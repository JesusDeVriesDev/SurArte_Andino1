const BASE = window.APP_BASE || '';

function toggleCarrito() {
  const panel = document.getElementById('carritoPanel');
  if (!panel) return;
  panel.classList.toggle('carrito-open');
}

function actualizarTotalUI(totalPrecio, totalItems) {
  const el = document.getElementById('carritoTotalEl');
  if (el) el.textContent = '$' + new Intl.NumberFormat('es-CO').format(totalPrecio);
  const badge = document.getElementById('cartCount');
  if (badge) badge.textContent = totalItems;
  const h3span = document.querySelector('.carrito-title span');
  if (h3span) h3span.textContent = '(' + totalItems + ' items)';
  const footer = document.getElementById('carritoFooter');
  if (footer) footer.style.display = totalItems > 0 ? '' : 'none';
  const empty = document.getElementById('carritoEmpty');
  if (empty) empty.style.display = totalItems === 0 ? 'flex' : 'none';
}

document.querySelectorAll('.btn-add[data-id]').forEach(btn => {
  btn.addEventListener('click', async () => {
    const id     = btn.dataset.id;
    const nombre = btn.dataset.nombre;
    const precio = parseFloat(btn.dataset.precio);
    btn.disabled = true; btn.textContent = '…';
    try {
      const r = await fetch(BASE + '/api/carrito/add.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ producto_id: id, cantidad: 1 })
      });
      const d = await r.json();
      if (d.success) {
        toast('"' + nombre + '" agregado al carrito', 'ok');
        actualizarTotalUI(d.data.total_precio, d.data.total_items);
        if(typeof updateNavCartBadge==="function") updateNavCartBadge(d.data.total_items);
        document.getElementById('carritoPanel')?.classList.add('carrito-open');
        agregarItemDOM(id, nombre, precio, btn.dataset.categoria || '');
        btn.textContent = '✓';
        setTimeout(() => { btn.textContent = '+'; btn.disabled = false; }, 1800);
      } else {
        toast(d.message || 'Error al agregar', 'err');
        btn.textContent = '+'; btn.disabled = false;
      }
    } catch(e) {
      toast('Error de conexión', 'err');
      btn.textContent = '+'; btn.disabled = false;
    }
  });
});

function agregarItemDOM(prodId, nombre, precio, categoria) {
  const existing = document.getElementById('ci-' + prodId);
  if (existing) {
    const qtyEl = document.getElementById('qty-' + prodId);
    if (qtyEl) qtyEl.textContent = parseInt(qtyEl.textContent) + 1;
    return;
  }
  const iconMap = {musica:'🎵',arte:'🎨',artesania:'🧵',danza:'💃',literatura:'📖',otro:'✨'};
  const ic = iconMap[categoria] || '🛍️';
  const html = `
    <div class="carrito-item" id="ci-${prodId}">
      <div class="ci-img">${ic}</div>
      <div class="ci-info">
        <div class="ci-nombre">${nombre}</div>
        <div class="ci-precio">$${new Intl.NumberFormat('es-CO').format(precio)}</div>
        <div class="ci-qty">
          <button onclick="cambiarCantidad('${prodId}',-1)" class="qty-btn">−</button>
          <span id="qty-${prodId}">1</span>
          <button onclick="cambiarCantidad('${prodId}',1)" class="qty-btn">+</button>
        </div>
      </div>
      <button onclick="quitarDelCarrito('${prodId}')" class="ci-del" title="Quitar">✕</button>
    </div>`;
  const container = document.getElementById('carritoItems');
  container.insertAdjacentHTML('afterbegin', html);
}

async function cambiarCantidad(prodId, delta) {
  const qtyEl = document.getElementById('qty-' + prodId);
  const actual = parseInt(qtyEl?.textContent || '1');
  const nueva  = actual + delta;
  if (nueva < 1) { quitarDelCarrito(prodId); return; }
  try {
    const r = await fetch(BASE + '/api/carrito/update.php', {
      method: 'POST', headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ producto_id: prodId, cantidad: nueva })
    });
    const d = await r.json();
    if (d.success) {
      if (qtyEl) qtyEl.textContent = nueva;
      actualizarTotalUI(d.data.total_precio, d.data.total_items);
        if(typeof updateNavCartBadge==="function") updateNavCartBadge(d.data.total_items);
    } else {
      toast(d.message || 'Error', 'err');
    }
  } catch { toast('Error de conexión','err'); }
}

async function quitarDelCarrito(prodId) {
  try {
    const r = await fetch(BASE + '/api/carrito/remove.php', {
      method: 'POST', headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ producto_id: prodId })
    });
    const d = await r.json();
    if (d.success) {
      document.getElementById('ci-' + prodId)?.remove();
      actualizarTotalUI(d.data.total_precio, d.data.total_items);
        if(typeof updateNavCartBadge==="function") updateNavCartBadge(d.data.total_items);
    } else {
      toast(d.message || 'Error', 'err');
    }
  } catch { toast('Error de conexión','err'); }
}

async function pagar() {
  if (!confirm('¿Confirmar el pago? El stock se actualizará automáticamente.')) return;
  const btn = document.getElementById('btnPagar');
  btn.disabled = true; btn.textContent = '⏳ Procesando…';
  try {
    const r = await fetch(BASE + '/api/carrito/checkout.php', {
      method: 'POST', headers: {'Content-Type':'application/json'},
      body: JSON.stringify({})
    });
    const d = await r.json();
    if (d.success) {
      toast('¡Pago exitoso! Pedido #' + d.data.pedido_id.substring(0,8).toUpperCase(), 'ok');
      document.getElementById('carritoItems').innerHTML =
        '<div class="carrito-empty" style="display:flex;flex-direction:column"><div style="font-size:2.2rem">✅</div><p>¡Pedido confirmado!</p><a href="' + BASE + '/src/perfil/index.php" style="font-family:var(--ff-m);font-size:.58rem;letter-spacing:.1em;text-transform:uppercase;color:var(--sky);text-decoration:none;margin-top:8px">Ver mis compras →</a></div>';
      document.getElementById('carritoFooter').style.display = 'none';
      document.getElementById('cartCount').textContent = '0';
      const h3span = document.querySelector('.carrito-title span');
      if (h3span) h3span.textContent = '(0 items)';
      setTimeout(() => location.reload(), 2800);
    } else {
      toast(d.message || 'Error al procesar el pago', 'err');
      btn.disabled = false; btn.textContent = '💳 Pagar ahora';
    }
  } catch(e) {
    toast('Error de conexión', 'err');
    btn.disabled = false; btn.textContent = '💳 Pagar ahora';
  }
}