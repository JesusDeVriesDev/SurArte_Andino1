// Referencias al modal de creación/edición, al overlay oscuro y al formulario interno.
// Se declaran aquí para que todas las funciones del módulo los compartan.
const evModal   = document.getElementById('evModal');
const evOverlay = document.getElementById('evOverlay');
const evForm    = document.getElementById('evForm');

// Abre el modal en modo "Crear": limpia el formulario, resetea el ID oculto
// y cambia los textos del título y botón para indicar que es un evento nuevo.
function abrirModal() {
  evForm.reset();
  document.getElementById('evId').value = '';
  document.getElementById('evModalTitle').textContent = 'Crear evento';
  document.getElementById('evSubmitBtn').textContent  = 'Crear evento';
  evModal.classList.add('open');
  evOverlay.classList.add('open');
  document.body.style.overflow = 'hidden';
}

// Cierra el modal y devuelve el scroll al cuerpo de la página.
// También limpia el overlay para que el fondo deje de oscurecerse.
function cerrarModal() {
  evModal.classList.remove('open');
  evOverlay.classList.remove('open');
  document.body.style.overflow = '';
}

// Abre el modal en modo "Editar" pre-cargando todos los campos con los datos
// del evento recibido como objeto. Las fechas se convierten de ISO a datetime-local
// para que el input de tipo datetime-local las entienda correctamente.
function editarEvento(ev) {
  document.getElementById('evId').value          = ev.id          ?? '';
  document.getElementById('evTitulo').value      = ev.titulo      ?? '';
  document.getElementById('evCategoria').value   = ev.categoria   ?? '';
  document.getElementById('evMunicipio').value   = ev.municipio   ?? '';
  document.getElementById('evLugar').value       = ev.lugar       ?? '';
  document.getElementById('evPrecio').value      = ev.precio      ?? '0';
  document.getElementById('evAforo').value       = ev.aforo       ?? '';
  document.getElementById('evImagen').value      = ev.imagen_url  ?? '';
  document.getElementById('evDescripcion').value = ev.descripcion ?? '';

  if (ev.fecha_inicio) {
    const d = new Date(ev.fecha_inicio);
    document.getElementById('evFechaInicio').value = d.toISOString().slice(0, 16);
  }
  if (ev.fecha_fin) {
    const d = new Date(ev.fecha_fin);
    document.getElementById('evFechaFin').value = d.toISOString().slice(0, 16);
  } else {
    document.getElementById('evFechaFin').value = '';
  }

  document.getElementById('evModalTitle').textContent = 'Editar evento';
  document.getElementById('evSubmitBtn').textContent  = 'Guardar cambios';
  evModal.classList.add('open');
  evOverlay.classList.add('open');
  document.body.style.overflow = 'hidden';
}

// Alterna el estado activo/inactivo de un evento. El nombre de la función es
// un poco engañoso —también activa eventos—, así que detecta el estado actual
// leyendo el texto del botón antes de decidir qué acción enviar al servidor.
async function eliminarEvento(id, btn) {
  const activo  = btn.textContent.trim() === 'Desactivar';
  const accion  = activo ? 'desactivar' : 'activar';
  const mensaje = activo ? '¿Desactivar este evento?' : '¿Volver a activar este evento?';
  if (!confirm(mensaje)) return;
  btn.disabled = true;
  btn.textContent = '…';
  try {
    const r = await fetch((window.APP_BASE || '') + '/api/admin/eventos.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion, id })
    });
    const d = await r.json();
    if (d.success) {
      toast(activo ? 'Evento desactivado' : 'Evento activado', activo ? 'warn' : 'ok');
      // Pequeño retardo antes de recargar para que el toast sea visible
      setTimeout(() => location.reload(), 900);
    } else {
      toast(d.message || 'Error', 'err');
      btn.disabled = false;
      btn.textContent = activo ? 'Desactivar' : 'Activar';
    }
  } catch {
    toast('Error de conexión', 'err');
    btn.disabled = false;
    btn.textContent = activo ? 'Desactivar' : 'Activar';
  }
}

// Filtro de texto en tiempo real: oculta las tarjetas de eventos cuyo contenido
// no incluya lo que el admin está escribiendo en el campo de búsqueda.
document.getElementById('evSearch')?.addEventListener('input', function () {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.ev-card').forEach(card => {
    card.style.display = card.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});

// Filtro por categoría: al activar una pastilla, desactiva las demás y filtra
// las tarjetas por el atributo data-cat. "all" muestra todas sin excepción.
document.querySelectorAll('.filter-pill').forEach(pill => {
  pill.addEventListener('click', () => {
    document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
    pill.classList.add('active');
    const cat = pill.dataset.cat;
    document.querySelectorAll('.ev-card').forEach(card => {
      card.style.display = (cat === 'all' || card.dataset.cat === cat) ? '' : 'none';
    });
  });
});

// Cierra el modal con la tecla Escape para no forzar al admin a buscar el botón X
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') cerrarModal();
});
