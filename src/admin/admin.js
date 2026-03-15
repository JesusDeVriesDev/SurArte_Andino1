async function verificarArtista(id, btn) {
  if (!confirm('¿Verificar este perfil de artista?')) return;
  btn.disabled = true;
  btn.textContent = '…';
  try {
    const r = await fetch((window.APP_BASE||'') + '/api/admin/artistas.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'verificar', id })
    });
    const d = await r.json();
    if (d.success) {
      toast('Artista verificado correctamente', 'ok');
      btn.textContent = '✓ Verificado';
      btn.disabled = true;
      btn.style.borderColor = '#22c55e';
      btn.style.color = '#16a34a';
      const row = btn.closest('tr') || btn.closest('.user-row');
      if (row) {
        const badgeEl = row.querySelector('.badge-clay');
        if (badgeEl) {
          badgeEl.className = 'badge badge-green';
          badgeEl.textContent = '✓ Verificado';
        }
      }
      const pend = document.querySelector('[data-rol="pendiente"]');
      if (pend) {
        const m = pend.textContent.match(/\d+/);
        if (m) {
          const n = Math.max(0, parseInt(m[0]) - 1);
          pend.textContent = pend.textContent.replace(/\d+/, n);
        }
      }
    } else {
      toast(d.message || 'Error al verificar', 'err');
      btn.disabled = false;
      btn.textContent = 'Verificar';
    }
  } catch (e) {
    toast('Error de conexión: ' + e.message, 'err');
    btn.disabled = false;
    btn.textContent = 'Verificar';
  }
}

async function eliminarArtista(id, btn) {
  if (!confirm('¿Eliminar este perfil de artista? Esta acción no se puede deshacer.')) return;
  btn.disabled = true;
  try {
    const r = await fetch((window.APP_BASE||'') + '/api/admin/artistas.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'eliminar', id })
    });
    const d = await r.json();
    if (d.success) {
      toast('Artista eliminado', 'warn');
      const row = btn.closest('tr') || btn.closest('.user-row');
      if (row) row.remove();
    } else {
      toast(d.message || 'Error al eliminar', 'err');
      btn.disabled = false;
    }
  } catch (e) {
    toast('Error de conexión: ' + e.message, 'err');
    btn.disabled = false;
  }
}

async function cambiarRol(id, nuevoRol, selectEl) {
  try {
    const r = await fetch((window.APP_BASE||'') + '/api/admin/usuarios.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'cambiarRol', id, rol: nuevoRol })
    });
    const d = await r.json();
    if (d.success) {
      toast('Rol actualizado a "' + nuevoRol + '"', 'ok');
      const row = selectEl?.closest('tr') || selectEl?.closest('.user-row');
      if (row) {
        const badgeMap = {admin:'badge-clay',artista:'badge-sky',organizador:'badge-gold',visitante:'badge-muted',usuario:'badge-muted'};
        const badgeEl = row.querySelector('.badge');
        if (badgeEl) {
          badgeEl.className = 'badge ' + (badgeMap[nuevoRol] || 'badge-muted');
          badgeEl.textContent = nuevoRol;
        }
      }
    } else {
      toast(d.message || 'Error al cambiar rol', 'err');
    }
  } catch (e) {
    toast('Error de conexión: ' + e.message, 'err');
  }
}

async function eliminarUsuario(id, btn) {
  if (!confirm('¿Eliminar este usuario? Esta acción no se puede deshacer.')) return;
  btn.disabled = true;
  try {
    const r = await fetch((window.APP_BASE||'') + '/api/admin/usuarios.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'eliminar', id })
    });
    const d = await r.json();
    if (d.success) {
      toast('Usuario eliminado', 'warn');
      const row = btn.closest('tr') || btn.closest('.user-row');
      if (row) row.remove();
    } else {
      toast(d.message || 'Error al eliminar', 'err');
      btn.disabled = false;
    }
  } catch (e) {
    toast('Error de conexión: ' + e.message, 'err');
    btn.disabled = false;
  }
}

const searchInput = document.getElementById('adminSearch');
const filterPills = document.querySelectorAll('.filter-pill');
const tableRows   = document.querySelectorAll('.admin-table tbody tr, .user-row');

function filterTable() {
  const q   = (searchInput?.value || '').toLowerCase();
  const rol = document.querySelector('.filter-pill.active')?.dataset.rol || 'all';
  tableRows.forEach(row => {
    const txt    = row.textContent.toLowerCase();
    const rowRol = row.dataset.rol || '';
    const matchQ = !q || txt.includes(q);
    const matchR = rol === 'all' || rowRol === rol;
    row.style.display = (matchQ && matchR) ? '' : 'none';
  });
}

searchInput?.addEventListener('input', filterTable);

filterPills.forEach(pill => {
  pill.addEventListener('click', () => {
    filterPills.forEach(p => p.classList.remove('active'));
    pill.classList.add('active');
    filterTable();
  });
});

document.querySelectorAll('.stat-card').forEach((card, i) => {
  card.style.opacity = '0';
  card.style.transform = 'translateY(14px)';
  setTimeout(() => {
    card.style.transition = 'opacity .38s ease, transform .38s ease';
    card.style.opacity = '1';
    card.style.transform = 'translateY(0)';
  }, 60 + i * 55);
});
