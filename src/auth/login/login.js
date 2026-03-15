
(function () {
  'use strict';

  const form      = document.getElementById('loginForm');
  const emailInp  = document.getElementById('email');
  const passInp   = document.getElementById('password');
  const emailErr  = document.getElementById('emailErr');
  const passErr   = document.getElementById('passErr');
  const loginBtn  = document.getElementById('loginBtn');
  const toggleBtn = document.getElementById('togglePass');
  const eyeIcon   = document.getElementById('eyeIcon');


  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      const isHidden = passInp.type === 'password';
      passInp.type = isHidden ? 'text' : 'password';
      eyeIcon.innerHTML = isHidden
        ? `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
           <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
           <line x1="1" y1="1" x2="23" y2="23"/>`
        : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
           <circle cx="12" cy="12" r="3"/>`;
    });
  }

  function showErr(el, msg) {
    el.textContent = msg;
    el.closest('.input-group')?.querySelector('.input')?.classList.add('input--error');
  }
  function clearErr(el) {
    el.textContent = '';
    el.closest('.input-group')?.querySelector('.input')?.classList.remove('input--error');
  }
  function validateEmail(val) {
    if (!val) return 'El correo es obligatorio.';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) return 'Ingresa un correo válido.';
    return '';
  }
  function validatePass(val) {
    if (!val) return 'La contraseña es obligatoria.';
    if (val.length < 6) return 'Mínimo 6 caracteres.';
    return '';
  }

  emailInp?.addEventListener('input', () => {
    const e = validateEmail(emailInp.value.trim());
    e ? showErr(emailErr, e) : clearErr(emailErr);
  });
  passInp?.addEventListener('input', () => {
    const e = validatePass(passInp.value);
    e ? showErr(passErr, e) : clearErr(passErr);
  });

  form?.addEventListener('submit', (e) => {
    const eErr = validateEmail(emailInp.value.trim());
    const pErr = validatePass(passInp.value);
    if (eErr) showErr(emailErr, eErr); else clearErr(emailErr);
    if (pErr) showErr(passErr, pErr); else clearErr(passErr);
    if (eErr || pErr) { e.preventDefault(); return; }
    loginBtn.disabled = true;
    loginBtn.textContent = 'Iniciando…';
  });

  window.Toast = {
    show(msg, type = 'info', duration = 3500) {
      const toast    = document.getElementById('toast');
      const toastMsg = toast?.querySelector('.toast-msg');
      const toastIcon = toast?.querySelector('.toast-icon');
      if (!toast || !toastMsg) return;

      const icons = { info: 'ℹ️', success: '✓', error: '✕', warning: '⚠️' };
      if (toastIcon) toastIcon.textContent = icons[type] ?? 'ℹ️';
      toastMsg.textContent = msg;

      toast.classList.add('show');
      setTimeout(() => toast.classList.remove('show'), duration);
    }
  };

  const serverErr = document.getElementById('serverError');
  if (serverErr) {
    setTimeout(() => {
      serverErr.style.transition = 'opacity .5s';
      serverErr.style.opacity = '0';
      setTimeout(() => serverErr.remove(), 500);
    }, 5000);
  }

})();