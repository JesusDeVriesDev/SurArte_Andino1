(function () {
  'use strict';

  // Referencias a los campos del formulario y sus elementos de error asociados
  const form        = document.getElementById('registerForm');
  const nombreInp   = document.getElementById('nombre');
  const emailInp    = document.getElementById('email');
  const passInp     = document.getElementById('password');
  const confirmInp  = document.getElementById('confirm');
  const termsInp    = document.getElementById('terms');

  const nombreErr   = document.getElementById('nombreErr');
  const emailErr    = document.getElementById('emailErr');
  const passErr     = document.getElementById('passErr');
  const confirmErr  = document.getElementById('confirmErr');
  const termsErr    = document.getElementById('termsErr');

  const registerBtn   = document.getElementById('registerBtn');
  const strengthFill  = document.getElementById('strengthFill');   // Barra visual de fortaleza
  const strengthLabel = document.getElementById('strengthLabel');  // Etiqueta textual de fortaleza

  // SVG del ojo abierto (contraseña oculta) y del ojo tachado (contraseña visible)
  const EYE_OPEN   = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
  const EYE_CLOSED = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>`;

  // ─── Toggle visibilidad de contraseña ───────────────────────────────────────
  // Se aplica a ambos campos (contraseña y confirmación) usando data-target para saber cuál controlar
  document.querySelectorAll('.pass-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const targetId = btn.dataset.target;
      const inp = document.getElementById(targetId);
      const isHidden = inp.type === 'password';
      inp.type = isHidden ? 'text' : 'password';
      // Actualiza el ícono según el nuevo estado del campo
      btn.querySelector('svg').innerHTML = isHidden ? EYE_CLOSED : EYE_OPEN;
    });
  });

  // ─── Cálculo de fortaleza de contraseña ─────────────────────────────────────
  // Evalúa la contraseña en base a varios criterios y devuelve un nivel del 1 al 4
  function getStrength(val) {
    let score = 0;
    if (val.length >= 8)  score++;                              // Longitud mínima
    if (val.length >= 12) score++;                              // Longitud óptima
    if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;       // Mezcla de mayúsculas y minúsculas
    if (/[0-9]/.test(val)) score++;                             // Al menos un número
    if (/[^A-Za-z0-9]/.test(val)) score++;                     // Al menos un carácter especial

    // Agrupa el score en 4 niveles para simplificar el feedback visual
    if (score <= 1) return 1;
    if (score === 2) return 2;
    if (score === 3) return 3;
    return 4;
  }

  // Textos descriptivos que se muestran bajo la barra de fortaleza
  const strengthTexts = ['', 'Muy débil', 'Débil', 'Buena', 'Fuerte'];

  // Actualiza la barra y la etiqueta de fortaleza cada vez que cambia el valor de la contraseña
  function updateStrength(val) {
    if (!val) {
      // Si el campo está vacío, resetea la barra completamente
      strengthFill.className = 'strength-fill';
      strengthFill.style.width = '0';
      strengthLabel.textContent = '';
      strengthLabel.className = 'strength-label';
      return;
    }
    const s = getStrength(val);
    // Las clases s1–s4 controlan el color y el ancho de la barra en CSS
    strengthFill.className = `strength-fill s${s}`;
    strengthLabel.textContent = strengthTexts[s];
    strengthLabel.className = `strength-label s${s}`;
  }

  // ─── Helpers de errores inline ──────────────────────────────────────────────

  // Muestra el mensaje de error bajo el campo y marca el input con borde rojo
  function showErr(el, inp, msg) {
    el.textContent = msg;
    inp?.classList.add('input--error');
  }

  // Limpia el mensaje y quita el estado de error visual del campo
  function clearErr(el, inp) {
    el.textContent = '';
    inp?.classList.remove('input--error');
  }

  // ─── Reglas de validación por campo ─────────────────────────────────────────
  // Cada función retorna un string con el error, o '' si el valor es válido
  const rules = {
    nombre(v)  { return v.trim().length >= 2 ? '' : 'Ingresa tu nombre completo.'; },
    email(v)   { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) ? '' : 'Correo electrónico inválido.'; },
    password(v){ return v.length >= 8 ? '' : 'Mínimo 8 caracteres.'; },
    // La confirmación se compara contra el valor actual del campo de contraseña
    confirm(v) { return v === passInp.value ? '' : 'Las contraseñas no coinciden.'; },
    terms()    { return termsInp.checked ? '' : 'Debes aceptar que usarás la página.'; },
  };

  // ─── Validación en tiempo real ──────────────────────────────────────────────

  nombreInp?.addEventListener('input',  () => {
    const e = rules.nombre(nombreInp.value);
    e ? showErr(nombreErr, nombreInp, e) : clearErr(nombreErr, nombreInp);
  });

  emailInp?.addEventListener('input',   () => {
    const e = rules.email(emailInp.value);
    e ? showErr(emailErr, emailInp, e) : clearErr(emailErr, emailInp);
  });

  passInp?.addEventListener('input',    () => {
    // Actualiza la barra de fortaleza con cada tecla
    updateStrength(passInp.value);
    const e = rules.password(passInp.value);
    e ? showErr(passErr, passInp, e) : clearErr(passErr, passInp);

    // Si el usuario ya escribió algo en confirmación, re-valida en tiempo real para que
    // el error de "no coinciden" desaparezca en cuanto las contraseñas vuelvan a coincidir
    if (confirmInp.value) {
      const ce = rules.confirm(confirmInp.value);
      ce ? showErr(confirmErr, confirmInp, ce) : clearErr(confirmErr, confirmInp);
    }
  });

  confirmInp?.addEventListener('input', () => {
    const e = rules.confirm(confirmInp.value);
    e ? showErr(confirmErr, confirmInp, e) : clearErr(confirmErr, confirmInp);
  });

  // El checkbox se valida en 'change' (no en 'input') porque solo tiene dos estados
  termsInp?.addEventListener('change',  () => {
    const e = rules.terms();
    e ? showErr(termsErr, null, e) : clearErr(termsErr, null);
  });

  // ─── Submit del formulario ──────────────────────────────────────────────────
  form?.addEventListener('submit', (e) => {
    // Valida todos los campos en el momento del envío, independientemente del estado previo
    const checks = [
      [rules.nombre(nombreInp.value),   nombreErr,  nombreInp],
      [rules.email(emailInp.value),     emailErr,   emailInp],
      [rules.password(passInp.value),   passErr,    passInp],
      [rules.confirm(confirmInp.value), confirmErr, confirmInp],
      [rules.terms(),                   termsErr,   null],
    ];

    let hasError = false;
    checks.forEach(([msg, errEl, inp]) => {
      if (msg) { showErr(errEl, inp, msg); hasError = true; }
      else      { clearErr(errEl, inp); }
    });

    // Si algún campo tiene error, cancela el envío y deja al usuario corregir
    if (hasError) { e.preventDefault(); return; }

    // Todo válido: deshabilita el botón para prevenir doble envío y muestra estado de carga
    registerBtn.disabled = true;
    registerBtn.textContent = 'Creando cuenta…';
  });

  // ─── Auto-desvanecimiento del error de servidor ──────────────────────────────
  // El error que viene de PHP (vía sesión) se oculta automáticamente a los 5 segundos
  const serverErr = document.getElementById('serverError');
  if (serverErr) {
    setTimeout(() => {
      serverErr.style.transition = 'opacity .5s';
      serverErr.style.opacity = '0';
      setTimeout(() => serverErr.remove(), 500);
    }, 5000);
  }

  // ─── Toast fallback ─────────────────────────────────────────────────────────
  // Si head.php no está presente en esta página (register es standalone), se define
  // window.Toast localmente para que cualquier llamada a Toast.show() no falle
  if (typeof window.Toast === 'undefined') {
    window.Toast = {
      show(msg, type = 'info', duration = 3000) {
        const toast = document.getElementById('toast');
        const toastMsg = toast?.querySelector('.toast-msg');
        if (!toast || !toastMsg) return;
        toastMsg.textContent = msg;
        toast.className = `toast toast--${type} toast--visible`;
        setTimeout(() => { toast.className = 'toast'; }, duration);
      }
    };
  }

})();
