(function () {
  'use strict';

  // Referencias a los elementos del DOM que se usan a lo largo del módulo
  const form      = document.getElementById('loginForm');
  const emailInp  = document.getElementById('email');
  const passInp   = document.getElementById('password');
  const emailErr  = document.getElementById('emailErr');
  const passErr   = document.getElementById('passErr');
  const loginBtn  = document.getElementById('loginBtn');
  const toggleBtn = document.getElementById('togglePass');
  const eyeIcon   = document.getElementById('eyeIcon');

  // ─── Toggle visibilidad de contraseña ───────────────────────────────────────
  // Alterna entre type="password" y type="text", y cambia el ícono del ojo
  // para dar retroalimentación visual clara de si la contraseña es visible o no
  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      const isHidden = passInp.type === 'password';
      passInp.type = isHidden ? 'text' : 'password';

      // Ícono de ojo tachado cuando la contraseña es visible, ojo abierto cuando está oculta
      eyeIcon.innerHTML = isHidden
        ? `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
           <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
           <line x1="1" y1="1" x2="23" y2="23"/>`
        : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
           <circle cx="12" cy="12" r="3"/>`;
    });
  }

  // ─── Helpers de errores inline ──────────────────────────────────────────────

  // Muestra el mensaje de error bajo el campo y le agrega la clase de borde rojo
  function showErr(el, msg) {
    el.textContent = msg;
    el.closest('.input-group')?.querySelector('.input')?.classList.add('input--error');
  }

  // Limpia el mensaje de error y restaura el borde normal del campo
  function clearErr(el) {
    el.textContent = '';
    el.closest('.input-group')?.querySelector('.input')?.classList.remove('input--error');
  }

  // ─── Reglas de validación ───────────────────────────────────────────────────

  // Valida el formato del correo con una expresión regular simple pero suficiente para el frontend
  function validateEmail(val) {
    if (!val) return 'El correo es obligatorio.';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) return 'Ingresa un correo válido.';
    return '';
  }

  // Valida que la contraseña exista y tenga al menos 6 caracteres
  // (el mínimo real en el backend es 8, pero aquí solo se da feedback rápido al usuario)
  function validatePass(val) {
    if (!val) return 'La contraseña es obligatoria.';
    if (val.length < 6) return 'Mínimo 6 caracteres.';
    return '';
  }

  // ─── Validación en tiempo real ──────────────────────────────────────────────
  // Se valida en el evento 'input' para dar feedback inmediato mientras el usuario escribe

  emailInp?.addEventListener('input', () => {
    const e = validateEmail(emailInp.value.trim());
    e ? showErr(emailErr, e) : clearErr(emailErr);
  });

  passInp?.addEventListener('input', () => {
    const e = validatePass(passInp.value);
    e ? showErr(passErr, e) : clearErr(passErr);
  });

  // ─── Validación y envío del formulario ──────────────────────────────────────
  form?.addEventListener('submit', (e) => {
    const eErr = validateEmail(emailInp.value.trim());
    const pErr = validatePass(passInp.value);

    // Muestra o limpia errores según el estado de cada campo
    if (eErr) showErr(emailErr, eErr); else clearErr(emailErr);
    if (pErr) showErr(passErr, pErr); else clearErr(passErr);

    // Si hay algún error, cancela el submit y deja al usuario corregir
    if (eErr || pErr) { e.preventDefault(); return; }

    // Si todo está bien, deshabilita el botón para evitar doble envío y muestra estado de carga
    loginBtn.disabled = true;
    loginBtn.textContent = 'Iniciando…';
  });

  // ─── Sistema de Toast ───────────────────────────────────────────────────────
  // Expone window.Toast para que cualquier parte de la página pueda mostrar notificaciones
  // sin depender de alert() ni de otros mecanismos globales
  window.Toast = {
    show(msg, type = 'info', duration = 3500) {
      const toast     = document.getElementById('toast');
      const toastMsg  = toast?.querySelector('.toast-msg');
      const toastIcon = toast?.querySelector('.toast-icon');
      if (!toast || !toastMsg) return;

      // Mapea el tipo de notificación a su ícono correspondiente
      const icons = { info: 'ℹ️', success: '✓', error: '✕', warning: '⚠️' };
      if (toastIcon) toastIcon.textContent = icons[type] ?? 'ℹ️';
      toastMsg.textContent = msg;

      // Agrega la clase .show para activar la animación de entrada definida en CSS
      toast.classList.add('show');

      // Remueve .show después del tiempo definido para que el toast desaparezca
      setTimeout(() => toast.classList.remove('show'), duration);
    }
  };

  // ─── Auto-desvanecimiento del error de servidor ──────────────────────────────
  // El error que viene de PHP (sesión) desaparece solo después de 5 segundos
  // para no saturar la pantalla si el usuario ya leyó el mensaje
  const serverErr = document.getElementById('serverError');
  if (serverErr) {
    setTimeout(() => {
      serverErr.style.transition = 'opacity .5s';
      serverErr.style.opacity = '0';
      // Elimina el nodo del DOM una vez que la transición de opacidad terminó
      setTimeout(() => serverErr.remove(), 500);
    }, 5000);
  }

})();
