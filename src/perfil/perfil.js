// Gestiona las pestañas del perfil de usuario ("Mis compras" / "Mi cuenta").
// Al hacer clic en una pestaña, la activa y muestra solo su sección correspondiente.
document.querySelectorAll('.p-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.p-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    ['compras', 'cuenta'].forEach(id => {
      document.getElementById('tab-' + id).style.display =
        tab.dataset.tab === id ? 'block' : 'none';
    });
  });
});

// ─────────────────────────────────────────────
// ACTIVAR TAB DESDE URL (?tab=cuenta)
// ─────────────────────────────────────────────
(function () {
  const params = new URLSearchParams(window.location.search);
  const tab = params.get('tab');

  if (!tab) return;

  // Quitar active de todos
  document.querySelectorAll('.p-tab').forEach(t => t.classList.remove('active'));

  // Activar botón correcto
  const btn = document.querySelector(`.p-tab[data-tab="${tab}"]`);
  if (btn) btn.classList.add('active');

  // Mostrar contenido correcto
  ['compras', 'cuenta'].forEach(id => {
    document.getElementById('tab-' + id).style.display =
      id === tab ? 'block' : 'none';
  });

  // (Opcional PRO) limpiar URL
  window.history.replaceState({}, document.title, window.location.pathname);
})();

// Colapsa o expande el detalle de un pedido al hacer clic en su cabecera.
// El chevron rota para indicar visualmente si está abierto o cerrado.
function togglePedido(id) {
  const body = document.getElementById('body-' + id);
  const chev = document.getElementById('chevron-' + id);
  const open = body.style.display === 'block';
  body.style.display = open ? 'none' : 'block';
  chev.textContent   = open ? '▼' : '▲';
}

// Módulo de cambio de contraseña en el perfil del usuario.
// IIFE para no contaminar el scope global.
(function () {
  'use strict';

  // Referencias a los campos del bloque de cambio de contraseña
  const passInp       = document.getElementById('perfil-password');
  const confirmInp    = document.getElementById('perfil-confirm');
  const passErr       = document.getElementById('perfil-passErr');
  const confirmErr    = document.getElementById('perfil-confirmErr');
  const strengthFill  = document.getElementById('perfil-strengthFill');
  const strengthLabel = document.getElementById('perfil-strengthLabel');
  const saveBtn       = document.getElementById('perfil-saveBtn');
  const form          = document.getElementById('perfilForm');

  // Si el bloque de contraseña no está en el DOM, salimos sin hacer nada
  if (!passInp) return;

  // Calcula el nivel de seguridad de la contraseña (1 = muy débil, 4 = fuerte).
  // Evalúa longitud, mezcla de caracteres, dígitos y símbolos especiales.
  function getStrength(val) {
    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    if (score <= 1) return 1;
    if (score === 2) return 2;
    if (score === 3) return 3;
    return 4;
  }

  const strengthTexts = ['', 'Muy débil', 'Débil', 'Buena', 'Fuerte'];

  // Refresca la barra de colores y el texto indicador de fortaleza.
  // Si el campo está vacío, vuelve al estado neutral sin indicadores.
  function updateStrength(val) {
    if (!val) {
      strengthFill.className = 'strength-fill';
      strengthLabel.className = 'strength-label';
      strengthLabel.textContent = '';
      return;
    }
    const s = getStrength(val);
    strengthFill.className  = 'strength-fill s' + s;
    strengthLabel.className = 'strength-label s' + s;
    strengthLabel.textContent = strengthTexts[s];
  }

  // Marca un campo con error y muestra el mensaje descriptivo debajo
  function showErr(el, inp, msg) {
    el.textContent = msg;
    inp?.classList.add('input--error');
  }

  // Limpia el estado de error de un campo cuando el valor es válido
  function clearErr(el, inp) {
    el.textContent = '';
    inp?.classList.remove('input--error');
  }

  // La contraseña es opcional en el perfil (vacío = no cambiar).
  // Si se escribe algo, debe tener al menos 8 caracteres.
  const rules = {
    password(v) { return (!v || v.length >= 8) ? '' : 'Mínimo 8 caracteres.'; },
    confirm(v)  { return (!passInp.value && !v) ? '' : (v === passInp.value ? '' : 'Las contraseñas no coinciden.'); },
  };

  // Valida y actualiza la barra de fortaleza mientras el usuario escribe.
  // Si la confirmación ya tiene texto, la revalida también para mantener coherencia.
  passInp.addEventListener('input', () => {
    updateStrength(passInp.value);
    const e = rules.password(passInp.value);
    e ? showErr(passErr, passInp, e) : clearErr(passErr, passInp);
    if (confirmInp.value) {
      const ce = rules.confirm(confirmInp.value);
      ce ? showErr(confirmErr, confirmInp, ce) : clearErr(confirmErr, confirmInp);
    }
  });

  // Compara la confirmación con la contraseña principal en tiempo real
  confirmInp.addEventListener('input', () => {
    const e = rules.confirm(confirmInp.value);
    e ? showErr(confirmErr, confirmInp, e) : clearErr(confirmErr, confirmInp);
  });

  // Validación final antes del envío. Bloquea el botón si todo está bien
  // para prevenir envíos dobles mientras el servidor guarda los cambios.
  form.addEventListener('submit', (ev) => {
    const checks = [
      [rules.password(passInp.value),   passErr,    passInp],
      [rules.confirm(confirmInp.value), confirmErr, confirmInp],
    ];
    let hasError = false;
    checks.forEach(([msg, errEl, inp]) => {
      if (msg) { showErr(errEl, inp, msg); hasError = true; }
      else      { clearErr(errEl, inp); }
    });
    if (hasError) { ev.preventDefault(); return; }
    saveBtn.disabled = true;
    saveBtn.textContent = 'Guardando…';
  });
})();
