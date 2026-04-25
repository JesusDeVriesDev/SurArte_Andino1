// Base de URLs construida desde la variable global que inyecta PHP.
// Se usa para componer las rutas de la API sin hardcodear el dominio.
const BASE = '<?= $base ?>';

// Agrega al carrito el producto seleccionado desde el perfil del artista.
// Deshabilita el botón mientras espera la respuesta para evitar clics duplicados
// y restaura el ícono "+" después de 1.8 segundos si la operación tuvo éxito.
document.querySelectorAll('.btn-add[data-id]').forEach(btn => {
  btn.addEventListener('click', async () => {
    const id     = btn.dataset.id;
    const nombre = btn.dataset.nombre;
    btn.disabled = true;
    btn.textContent = '…';
    try {
      const r = await fetch((window.APP_BASE || '') + '/api/carrito/add.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ producto_id: id, cantidad: 1 })
      });
      const d = await r.json();
      if (d.success) {
        toast('"' + nombre + '" agregado al carrito', 'ok');
        btn.textContent = '✓';
        setTimeout(() => {
          btn.textContent = '+';
          btn.disabled = false;
        }, 1800);
      } else {
        toast(d.message || 'Error al agregar', 'err');
        btn.disabled = false;
        btn.textContent = '+';
      }
    } catch {
      toast('Error de conexión', 'err');
      btn.disabled = false;
      btn.textContent = '+';
    }
  });
});
