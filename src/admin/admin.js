// Verifica el perfil de un artista: le pide confirmación al admin, deshabilita
// el botón mientras procesa y actualiza visualmente la fila sin recargar la página.
// Si la respuesta llega bien, además descuenta el contador de pendientes.
async function verificarArtista(id, btn) {
  if (!confirm('¿Verificar este perfil de artista?')) return;
  btn.disabled = true;
  btn.textContent = '…';
  try {
    const r = await fetch((window.APP_BASE || '') + '/api/admin/artistas.php', {
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

      // Cambia el badge de "Pendiente" a "Verificado" en la misma fila
      const row = btn.closest('tr') || btn.closest('.user-row');
      if (row) {
        const badgeEl = row.querySelector('.badge-clay');
        if (badgeEl) {
          badgeEl.className = 'badge badge-green';
          badgeEl.textContent = '✓ Verificado';
        }
      }

      // Actualiza el contador de artistas pendientes en el panel lateral
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

// Elimina el perfil de artista de forma permanente. Doble confirmación
// implícita porque el texto del confirm es bastante explícito al decir
// que no hay vuelta atrás. Remueve la fila del DOM si el servidor confirma.
async function eliminarArtista(id, btn) {
  if (!confirm('¿Eliminar este perfil de artista? Esta acción no se puede deshacer.')) return;
  btn.disabled = true;
  try {
    const r = await fetch((window.APP_BASE || '') + '/api/admin/artistas.php', {
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

// Cambia el rol de un usuario desde el select de la tabla. Si el servidor
// responde bien, actualiza el badge visual en la misma fila para reflejar
// el nuevo rol sin necesidad de recargar la lista completa.
async function cambiarRol(id, nuevoRol, selectEl) {
  try {
    const r = await fetch((window.APP_BASE || '') + '/api/admin/usuarios.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'cambiarRol', id, rol: nuevoRol })
    });
    const d = await r.json();
    if (d.success) {
      toast('Rol actualizado a "' + nuevoRol + '"', 'ok');
      const row = selectEl?.closest('tr') || selectEl?.closest('.user-row');
      if (row) {
        const badgeMap = {
          admin: 'badge-clay',
          artista: 'badge-sky',
          organizador: 'badge-gold',
          visitante: 'badge-muted',
          usuario: 'badge-muted'
        };
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

// Elimina un usuario del sistema. Misma lógica que eliminarArtista pero
// apunta al endpoint de usuarios. La fila desaparece del DOM si todo va bien.
async function eliminarUsuario(id, btn) {
  if (!confirm('¿Eliminar este usuario? Esta acción no se puede deshacer.')) return;
  btn.disabled = true;
  try {
    const r = await fetch((window.APP_BASE || '') + '/api/admin/usuarios.php', {
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

// Referencias a los controles de filtrado de la tabla de usuarios
const searchInput = document.getElementById('adminSearch');
const filterPills = document.querySelectorAll('.filter-pill');
const tableRows   = document.querySelectorAll('.admin-table tbody tr, .user-row');

// Aplica simultáneamente el filtro de texto y el de rol sobre todas las filas.
// Una fila se muestra solo si cumple ambos criterios a la vez.
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

// Escucha cada pulsación de tecla en el buscador para filtrar en tiempo real
searchInput?.addEventListener('input', filterTable);

// Al hacer clic en una pastilla de rol, desactiva las demás y vuelve a filtrar
filterPills.forEach(pill => {
  pill.addEventListener('click', () => {
    filterPills.forEach(p => p.classList.remove('active'));
    pill.classList.add('active');
    filterTable();
  });
});

// Animación de entrada para las tarjetas de estadísticas del dashboard.
// Cada tarjeta aparece con un pequeño retardo escalonado para que la carga
// se sienta más dinámica y no todo aparezca de golpe.
document.querySelectorAll('.stat-card').forEach((card, i) => {
  card.style.opacity = '0';
  card.style.transform = 'translateY(14px)';
  setTimeout(() => {
    card.style.transition = 'opacity .38s ease, transform .38s ease';
    card.style.opacity = '1';
    card.style.transform = 'translateY(0)';
  }, 60 + i * 55);
});
