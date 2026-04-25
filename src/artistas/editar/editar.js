// Maneja las pestañas del formulario de edición del artista (ej. "Perfil" / "Cuenta").
// Al hacer clic en una pestaña, marca esa como activa visualmente y muestra
// solo el panel de contenido correspondiente, ocultando los demás.
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach(b => {
      b.style.borderBottomColor = 'transparent';
      b.style.color = 'rgba(26,18,8,.38)';
    });
    btn.style.borderBottomColor = 'var(--gold)';
    btn.style.color = 'var(--gold)';
    document.querySelectorAll('[id^="tab-"]').forEach(t => t.style.display = 'none');
    document.getElementById('tab-' + btn.dataset.tab).style.display = 'block';
  });
});

// Módulo de validación de contraseña en el formulario de cuenta del artista.
// Se envuelve en IIFE para no contaminar el scope global con nombres genéricos.
(function () {
  'use strict';

  // Referencias a los campos y elementos de UI del bloque de contraseña
  const passInp       = document.getElementById('artista-password');
  const confirmInp    = document.getElementById('artista-confirm');
  const passErr       = document.getElementById('artista-passErr');
  const confirmErr    = document.getElementById('artista-confirmErr');
  const strengthFill  = document.getElementById('artista-strengthFill');
  const strengthLabel = document.getElementById('artista-strengthLabel');
  const saveBtn       = document.getElementById('artista-saveBtn');
  const form          = document.getElementById('artistaCuentaForm');

  // Si el campo de contraseña no existe en el DOM, este formulario no aplica
  if (!passInp) return;

  // Calcula la fortaleza de la contraseña en una escala del 1 al 4.
  // Revisa longitud, mezcla de mayúsculas/minúsculas, dígitos y caracteres especiales.
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

  // Actualiza la barra visual y la etiqueta de texto según el nivel calculado.
  // Si el campo está vacío, resetea todo a estado neutro.
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

  // Muestra un mensaje de error bajo el campo y añade la clase visual de error
  function showErr(el, inp, msg) {
    el.textContent = msg;
    inp?.classList.add('input--error');
  }

  // Limpia el error de un campo cuando el usuario corrige el valor
  function clearErr(el, inp) {
    el.textContent = '';
    inp?.classList.remove('input--error');
  }

  // Reglas de validación: la contraseña es opcional al editar (si está vacía
  // no se cambia), pero si se escribe debe tener mínimo 8 caracteres.
  const rules = {
    password(v) { return (!v || v.length >= 8) ? '' : 'Mínimo 8 caracteres.'; },
    confirm(v)  { return (!passInp.value && !v) ? '' : (v === passInp.value ? '' : 'Las contraseñas no coinciden.'); },
  };

  // Valida la contraseña y actualiza la barra de fortaleza mientras el usuario escribe
  passInp.addEventListener('input', () => {
    updateStrength(passInp.value);
    const e = rules.password(passInp.value);
    e ? showErr(passErr, passInp, e) : clearErr(passErr, passInp);
    // Si ya escribió algo en confirmación, revalida también ese campo
    if (confirmInp.value) {
      const ce = rules.confirm(confirmInp.value);
      ce ? showErr(confirmErr, confirmInp, ce) : clearErr(confirmErr, confirmInp);
    }
  });

  // Valida la confirmación cada vez que cambia, comparando con la contraseña principal
  confirmInp.addEventListener('input', () => {
    const e = rules.confirm(confirmInp.value);
    e ? showErr(confirmErr, confirmInp, e) : clearErr(confirmErr, confirmInp);
  });

  // Antes de enviar el formulario, corre todas las validaciones. Si alguna falla
  // cancela el envío y deja el foco visual en el error para que el usuario lo vea.
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
    // Deshabilita el botón para evitar envíos dobles mientras el servidor responde
    saveBtn.disabled = true;
    saveBtn.textContent = 'Guardando…';
  });
})();
