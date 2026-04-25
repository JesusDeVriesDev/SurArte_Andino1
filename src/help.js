// ─────────────────────────────────────────────
// CONFIGURACIÓN DE TOURS POR PÁGINA
// ─────────────────────────────────────────────

const TOURS = {
  registro: {
    storageKey: "tour_registro_visto",
    steps: [
      { element: '.auth-title', intro: 'Bienvenido. Crea tu cuenta en SurArte Andino' },
      { element: '#nombre', intro: 'Ingresa tu nombre completo' },
      { element: '#email', intro: 'Escribe un correo válido' },
      { element: '#password', intro: 'Crea una contraseña segura' },
      { element: '#confirm', intro: 'Confirma tu contraseña' },
      { element: '#terms', intro: 'Debes aceptar los términos' },
      { element: '#registerBtn', intro: 'Haz click aquí para registrarte' },
      { element: '#lg-link', intro: '¿Ya tienes cuenta? Inicia sesión' }
    ]
  },

  login: {
    storageKey: "tour_login_visto",
    steps: [
      { element: '.auth-title', intro: 'Aquí puedes iniciar sesión' },
      { element: '#email', intro: 'Ingresa tu correo' },
      { element: '#password', intro: 'Escribe tu contraseña' },
      { element: '#remember', intro: 'Recordar sesión' },
      { element: '#loginBtn', intro: 'Haz click para ingresar' },
      { element: '#lg-link', intro: 'Regístrate si no tienes cuenta' }
    ]
  },

  nav: {
    storageKey: "tour_nav_visto",
    steps: [
      { element: '#inicio', intro: 'Página principal' },
      { element: '#artistas', intro: 'Artistas destacados' },
      { element: '#eventos', intro: 'Eventos culturales' },
      { element: '#tienda', intro: 'Productos artesanales' },
      { element: '#comunidad', intro: 'Comunidad' }
    ]
  }
};



// ─────────────────────────────────────────────
// DETECTAR QUÉ TOUR USAR SEGÚN LA PÁGINA
// ─────────────────────────────────────────────

function detectarTour() {
  if (document.querySelector('#registerBtn')) return TOURS.registro;
  if (document.querySelector('#loginBtn')) return TOURS.login;
  if (document.querySelector('#inicio')) return TOURS.nav;

  return null;
}

// ─────────────────────────────────────────────
// INICIAR TOUR
// ─────────────────────────────────────────────

function iniciarTour(config) {
  if (!config) return;

  const stepsValidos = config.steps
    .map(step => {
      const el = document.querySelector(step.element);
      return el ? { element: el, intro: step.intro } : null;
    })
    .filter(Boolean);

  if (!stepsValidos.length) return;

  introJs().setOptions({
    nextLabel: 'Siguiente',
    prevLabel: 'Atrás',
    doneLabel: 'Listo',
    skipLabel: 'X',
    showProgress: true,
    steps: stepsValidos
  }).start();

  localStorage.setItem(config.storageKey, "true");
}



// ─────────────────────────────────────────────
// BOTÓN GLOBAL DE AYUDA
// ─────────────────────────────────────────────

function crearBotonAyuda(config) {
  if (!config) return;
  if (document.getElementById('btn-guia-global')) return;

  const btn = document.createElement('button');
  btn.id = 'btn-guia-global';
  btn.setAttribute('aria-label', 'Ver guía de ayuda');
  btn.setAttribute('title', 'Ver guía de ayuda');

  btn.innerHTML = `
    <span style="font-size:1.1rem;line-height:1">🤝</span>
    <span style="line-height:1">Ayuda</span>
  `;

  btn.style.cssText = `
    position: fixed !important;
    bottom: 28px !important;
    left: 28px !important;
    z-index: 99999 !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    background: #ffffff !important;
    color: #000000 !important;
    border: 2px solid transparent !important;
    border-radius: 999px !important;
    padding: 12px 20px !important;
    font-family: 'Inter', sans-serif !important;
    font-size: 0.88rem !important;
    font-weight: 700 !important;
    letter-spacing: 0.08em !important;
    text-transform: uppercase !important;
    cursor: pointer !important;
    box-shadow: 0 4px 20px rgba(0,0,0,0.25) !important;
    transition: background 0.22s, border-color 0.22s, transform 0.18s, box-shadow 0.22s !important;
    user-select: none !important;
  `;

  // Hover pro
  btn.addEventListener('mouseenter', () => {
    btn.style.background = '#dfdfdf88';
    btn.style.transform = 'translateY(-2px)';
    btn.style.boxShadow = '0 8px 28px rgba(0,0,0,0.3)';
  });

  btn.addEventListener('mouseleave', () => {
    btn.style.background = '#ffffff';
    btn.style.transform = 'translateY(0)';
    btn.style.boxShadow = '0 4px 20px rgba(0,0,0,0.25)';
  });

  btn.onclick = () => {
    if (window.speechSynthesis) {
      const u = new SpeechSynthesisUtterance('Iniciando guía');
      speechSynthesis.speak(u);
    }

    iniciarTour(config);
  };

  document.body.appendChild(btn);
}



// ─────────────────────────────────────────────
// INIT GLOBAL
// ─────────────────────────────────────────────

document.addEventListener("DOMContentLoaded", () => {
  const config = detectarTour();

  if (!config) return;

  crearBotonAyuda(config);
});