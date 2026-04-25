// Módulo de accesibilidad: lector de pantalla por voz para usuarios con dificultades visuales.
// Se activa con el botón flotante que se inyecta en el DOM al cargar la página.
// Usa la API SpeechSynthesis del navegador, priorizando siempre una voz en español colombiano.
(function () {
  'use strict';

  const synth = window.speechSynthesis;
  let activo = false;
  let vozES  = null;

  // Carga la mejor voz disponible en español. Primero busca es-CO (colombiano),
  // luego es-419 (latinoamericano), luego cualquier variante de es-.
  // Esto es necesario porque Chrome no tiene las voces listas al cargar la página.
  function cargarVoz() {
    const voces = synth.getVoices();
    vozES =
      voces.find(v => v.lang === 'es-CO')          ||
      voces.find(v => v.lang === 'es-419')         ||
      voces.find(v => v.lang.startsWith('es-'))    ||
      voces.find(v => v.lang.startsWith('es'))     ||
      null;
  }

  // Chrome carga las voces de forma asíncrona; este evento garantiza que
  // cargarVoz se ejecute cuando ya estén disponibles.
  if (synth.onvoiceschanged !== undefined) synth.onvoiceschanged = cargarVoz;
  cargarVoz();

  // Cancela cualquier utterance en curso y lanza la nueva con un pequeño retardo.
  // El setTimeout de 50ms existe para solucionar un bug conocido de Chrome donde
  // speak() no funciona si se llama inmediatamente después de cancel().
  function leer(texto) {
    if (!texto || !activo) return;
    synth.cancel();
    setTimeout(() => {
      const u = new SpeechSynthesisUtterance(texto.trim());
      if (vozES) u.voice = vozES;
      u.lang   = vozES ? vozES.lang : 'es';
      u.rate   = 0.92;
      u.pitch  = 1;
      u.volume = 1;
      u.onerror = () => {};
      synth.speak(u);
    }, 50);
  }

  // Elimina emojis y caracteres Unicode especiales del texto antes de leerlo.
  // Sin esto, la voz leería cosas como "pictograma de montaña nevada" o "cara sonriente".
  function limpiarEmojis(texto) {
    return texto
      .replace(/[\u{1F300}-\u{1FAFF}]/gu, '')
      .replace(/[\u{2600}-\u{27BF}]/gu, '')
      .replace(/[\u{FE00}-\u{FEFF}]/gu, '')
      .replace(/\s+/g, ' ')
      .trim();
  }

  // Tags que no tienen semántica propia (ícono, texto decorativo, etc.).
  // Si el cursor cae sobre uno de estos, se sube al padre hasta encontrar
  // un elemento con significado real para el lector.
  const TAGS_SIN_SEMANTICA = new Set(['span','i','em','b','strong','svg','path','circle','small','sup','sub']);
  const TAGS_RAIZ = new Set(['a','button','label','input','select','textarea','h1','h2','h3','h4','h5','h6','li','p','div','section','article','main','header','nav']);

  // Sube por el árbol DOM hasta encontrar el primer ancestro con semántica.
  // Evita que el lector anuncie solo el texto del ícono dentro de un botón.
  function elementoSignificativo(el) {
    let actual = el;
    while (actual && TAGS_SIN_SEMANTICA.has(actual.tagName.toLowerCase())) {
      actual = actual.parentElement;
    }
    return actual || el;
  }

  // Extrae el texto más útil de un elemento, en orden de preferencia:
  // aria-label > title > placeholder > alt > innerText.
  // Si no queda nada legible después de limpiar emojis, devuelve cadena vacía.
  function textoDeElemento(el) {
    if (el.closest('#btn-accesibilidad, #acc-toast')) return '';
    if (el === document.body || el === document.documentElement) return '';

    const target = elementoSignificativo(el);
    const raw = (
      target.getAttribute('aria-label') ||
      target.getAttribute('title')       ||
      target.getAttribute('placeholder') ||
      target.getAttribute('alt')         ||
      (target.tagName === 'INPUT' ? '' : target.innerText) ||
      ''
    );

    const limpio = limpiarEmojis(raw);
    if (!limpio || limpio.length < 2) return '';
    return limpio.length > 150 ? limpio.slice(0, 150) + '...' : limpio;
  }

  // Antepone un contexto verbal según el tipo de elemento para ayudar al usuario
  // a entender qué tipo de interacción tiene disponible: "Botón:", "Enlace:", etc.
  function prefijo(el) {
    const target = elementoSignificativo(el);
    const tag = target.tagName.toLowerCase();
    if (tag === 'a')           return 'Enlace: ';
    if (tag === 'button')      return 'Botón: ';
    if (tag === 'input')       return 'Campo: ';
    if (tag === 'select')      return 'Lista: ';
    if (tag === 'textarea')    return 'Área de texto: ';
    if (tag === 'label')       return 'Etiqueta: ';
    if (/^h[1-6]$/.test(tag)) return 'Título: ';
    if (tag === 'img')         return 'Imagen: ';
    return '';
  }

  // Evita que el lector se dispare múltiples veces por movimientos rápidos del cursor.
  // El retardo de 130ms filtra el ruido de mouseover sin introducir latencia perceptible.
  // También deduplica: si el texto ya se leyó en el ciclo anterior, lo ignora.
  let debounceTimer = null;
  let ultimoTexto   = '';

  function leerConDebounce(el) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      const texto = textoDeElemento(el);
      if (!texto) return;
      const completo = prefijo(el) + texto;
      if (completo === ultimoTexto) return;
      ultimoTexto = completo;
      leer(completo);
    }, 130);
  }

  // Manejadores de eventos que se registran solo cuando el lector está activo.
  // mouseenter usa captura (true) para interceptar antes que cualquier handler de la página.
  function onMouseEnter(e) { if (activo) leerConDebounce(e.target); }
  function onFocusIn(e) {
    if (!activo) return;
    const texto = textoDeElemento(e.target);
    if (texto) leer(prefijo(e.target) + texto);
  }

  function activarListeners() {
    document.addEventListener('mouseenter', onMouseEnter, true);
    document.addEventListener('focusin',    onFocusIn,    true);
  }

  function desactivarListeners() {
    document.removeEventListener('mouseenter', onMouseEnter, true);
    document.removeEventListener('focusin',    onFocusIn,    true);
    clearTimeout(debounceTimer);
    ultimoTexto = '';
  }

  // Toast de feedback visual que aparece en la esquina inferior para confirmar
  // al usuario si el lector se activó o desactivó. Se crea al vuelo si no existe.
  function mostrarToast(msg) {
    let t = document.getElementById('acc-toast');
    if (!t) {
      t = document.createElement('div');
      t.id = 'acc-toast';
      document.body.appendChild(t);
    }
    t.textContent = msg;
    t.classList.add('visible');
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.classList.remove('visible'), 3200);
  }

  // Crea e inyecta el botón flotante del lector de pantalla.
  // Al hacer clic: si estaba inactivo, activa los listeners y anuncia el inicio.
  // Si estaba activo, cancela la voz y elimina los listeners.
  function crearBoton() {
    const btn = document.createElement('button');
    btn.id = 'btn-accesibilidad';
    btn.setAttribute('aria-label', 'Activar lector de pantalla');
    btn.setAttribute('title', 'Accesibilidad: lector de voz');
    btn.innerHTML = '<span class="acc-icon">🔊</span><span class="acc-label">Lector</span>';
    document.body.appendChild(btn);

    btn.addEventListener('click', () => {
      cargarVoz();
      activo = !activo;
      btn.classList.toggle('activo', activo);
      btn.setAttribute('aria-label', activo ? 'Desactivar lector' : 'Activar lector de pantalla');

      if (activo) {
        activarListeners();
        // Pequeño retardo para que el mensaje de activación no colisione con el click
        setTimeout(() => {
          const u = new SpeechSynthesisUtterance(
            'Lector activado. Pase el cursor por la página para escuchar los elementos.'
          );
          if (vozES) u.voice = vozES;
          u.lang = vozES ? vozES.lang : 'es';
          u.rate = 0.92;
          synth.speak(u);
        }, 100);
        mostrarToast('🔊 Lector activado — mueve el cursor por la página');
      } else {
        desactivarListeners();
        synth.cancel();
        mostrarToast('🔇 Lector desactivado');
      }
    });
  }

  // Espera a que el DOM esté listo antes de inyectar el botón,
  // ya que document.body puede no existir si el script carga en el <head>
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', crearBoton);
  } else {
    crearBoton();
  }
})();
