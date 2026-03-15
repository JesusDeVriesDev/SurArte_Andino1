/* =========================================================
   register.js — Validación cliente para el registro
   ========================================================= */

(function () {
  'use strict';

  /* ── Elementos ── */
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
  const strengthFill  = document.getElementById('strengthFill');
  const strengthLabel = document.getElementById('strengthLabel');

  /* ── Toggle mostrar/ocultar contraseña ── */
  document.querySelectorAll('.pass-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const targetId = btn.dataset.target;
      const inp = document.getElementById(targetId);
      const isHidden = inp.type === 'password';
      inp.type = isHidden ? 'text' : 'password';
      btn.querySelector('svg').style.opacity = isHidden ? '.5' : '1';
    });
  });

  /* ── Fortaleza de contraseña ── */
  function getStrength(val) {
    let score = 0;
    if (val.length >= 8)  score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    // Normalizar a 1-4
    if (score <= 1) return 1;
    if (score === 2) return 2;
    if (score === 3) return 3;
    return 4;
  }

  const strengthTexts = ['', 'Muy débil', 'Débil', 'Buena', 'Fuerte'];

  function updateStrength(val) {
    if (!val) {
      strengthFill.className = 'strength-fill';
      strengthFill.style.width = '0';
      strengthLabel.textContent = '';
      strengthLabel.className = 'strength-label';
      return;
    }
    const s = getStrength(val);
    strengthFill.className = `strength-fill s${s}`;
    strengthLabel.textContent = strengthTexts[s];
    strengthLabel.className = `strength-label s${s}`;
  }

  /* ── Helpers ── */
  function showErr(el, inp, msg) {
    el.textContent = msg;
    inp?.classList.add('input--error');
  }
  function clearErr(el, inp) {
    el.textContent = '';
    inp?.classList.remove('input--error');
  }

  /* ── Reglas de validación ── */
  const rules = {
    nombre(v)  { return v.trim().length >= 2 ? '' : 'Ingresa tu nombre completo.'; },
    email(v)   { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) ? '' : 'Correo electrónico inválido.'; },
    password(v){ return v.length >= 8 ? '' : 'Mínimo 8 caracteres.'; },
    confirm(v) { return v === passInp.value ? '' : 'Las contraseñas no coinciden.'; },
    terms()    { return termsInp.checked ? '' : 'Debes aceptar que usarás la página.'; },
  };

  /* ── Validación en tiempo real ── */
  nombreInp?.addEventListener('input',  () => { const e = rules.nombre(nombreInp.value);   e ? showErr(nombreErr, nombreInp, e)  : clearErr(nombreErr, nombreInp); });
  emailInp?.addEventListener('input',   () => { const e = rules.email(emailInp.value);     e ? showErr(emailErr, emailInp, e)    : clearErr(emailErr, emailInp); });
  passInp?.addEventListener('input',    () => {
    updateStrength(passInp.value);
    const e = rules.password(passInp.value);
    e ? showErr(passErr, passInp, e) : clearErr(passErr, passInp);
    // Revalidar confirmación si ya tiene algo
    if (confirmInp.value) {
      const ce = rules.confirm(confirmInp.value);
      ce ? showErr(confirmErr, confirmInp, ce) : clearErr(confirmErr, confirmInp);
    }
  });
  confirmInp?.addEventListener('input', () => { const e = rules.confirm(confirmInp.value); e ? showErr(confirmErr, confirmInp, e): clearErr(confirmErr, confirmInp); });
  termsInp?.addEventListener('change',  () => { const e = rules.terms();                   e ? showErr(termsErr, null, e)        : clearErr(termsErr, null); });

  /* ── Submit ── */
  form?.addEventListener('submit', (e) => {
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

    if (hasError) { e.preventDefault(); return; }

    registerBtn.disabled = true;
    registerBtn.textContent = 'Creando cuenta…';
  });

  /* ── Auto-ocultar error del servidor ── */
  const serverErr = document.getElementById('serverError');
  if (serverErr) {
    setTimeout(() => {
      serverErr.style.transition = 'opacity .5s';
      serverErr.style.opacity = '0';
      setTimeout(() => serverErr.remove(), 500);
    }, 5000);
  }

  /* ── Toast fallback ── */
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