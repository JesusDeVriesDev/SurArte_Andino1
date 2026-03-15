
document.querySelectorAll('.p-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.p-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    ['compras','cuenta'].forEach(id => {
      document.getElementById('tab-'+id).style.display = tab.dataset.tab === id ? 'block' : 'none';
    });
  });
});

function togglePedido(id) {
  const body = document.getElementById('body-'+id);
  const chev = document.getElementById('chevron-'+id);
  const open = body.style.display === 'block';
  body.style.display = open ? 'none' : 'block';
  chev.textContent = open ? '▼' : '▲';
}

(function () {
  'use strict';
  const passInp      = document.getElementById('perfil-password');
  const confirmInp   = document.getElementById('perfil-confirm');
  const passErr      = document.getElementById('perfil-passErr');
  const confirmErr   = document.getElementById('perfil-confirmErr');
  const strengthFill = document.getElementById('perfil-strengthFill');
  const strengthLabel= document.getElementById('perfil-strengthLabel');
  const saveBtn      = document.getElementById('perfil-saveBtn');
  const form         = document.getElementById('perfilForm');
  if (!passInp) return;

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
  const strengthTexts = ['','Muy débil','Débil','Buena','Fuerte'];

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

  function showErr(el, inp, msg) { el.textContent = msg; inp?.classList.add('input--error'); }
  function clearErr(el, inp)     { el.textContent = '';  inp?.classList.remove('input--error'); }

  const rules = {
    password(v) { return (!v || v.length >= 8) ? '' : 'Mínimo 8 caracteres.'; },
    confirm(v)  { return (!passInp.value && !v) ? '' : (v === passInp.value ? '' : 'Las contraseñas no coinciden.'); },
  };

  passInp.addEventListener('input', () => {
    updateStrength(passInp.value);
    const e = rules.password(passInp.value);
    e ? showErr(passErr, passInp, e) : clearErr(passErr, passInp);
    if (confirmInp.value) {
      const ce = rules.confirm(confirmInp.value);
      ce ? showErr(confirmErr, confirmInp, ce) : clearErr(confirmErr, confirmInp);
    }
  });

  confirmInp.addEventListener('input', () => {
    const e = rules.confirm(confirmInp.value);
    e ? showErr(confirmErr, confirmInp, e) : clearErr(confirmErr, confirmInp);
  });

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
