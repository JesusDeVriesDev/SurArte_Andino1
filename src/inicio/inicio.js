/* =====================================================
   SurArte Andino — Accesibilidad: Lector de pantalla
   ===================================================== */

(function () {
  'use strict';

  const synth = window.speechSynthesis;
  let activo  = false;
  let vozES   = null;

  /* ── 1. Cargar mejor voz en español ─────────────── */
  function cargarVoz() {
    const voces = synth.getVoices();
    vozES =
      voces.find(v => v.lang === 'es-CO')  ||
      voces.find(v => v.lang === 'es-419') ||
      voces.find(v => v.lang.startsWith('es-')) ||
      voces.find(v => v.lang.startsWith('es'))  ||
      null;
  }
  if (synth.onvoiceschanged !== undefined) synth.onvoiceschanged = cargarVoz;
  cargarVoz();

  /* ── 2. Función de lectura robusta (fix Chrome) ── */
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

  /* ── 3. Limpiar emojis del texto ─────────────────
     Elimina emojis y símbolos especiales para que
     no sean leídos como "pictograma de montaña nevada" etc.
  ─────────────────────────────────────────────────── */
  function limpiarEmojis(texto) {
    return texto
      // Elimina emojis y símbolos Unicode de rangos comunes
      .replace(/[\u{1F300}-\u{1FAFF}]/gu, '')   // emojis modernos
      .replace(/[\u{2600}-\u{27BF}]/gu, '')      // símbolos misceláneos
      .replace(/[\u{FE00}-\u{FEFF}]/gu, '')      // variación de forma
      .replace(/\s+/g, ' ')
      .trim();
  }

  /* ── 4. Subir al ancestro significativo ──────────
     Si el elemento actual es un <span>, <i>, <em>,
     <svg> o similar sin semántica propia, subir al
     padre hasta encontrar algo con rol navegable.
  ─────────────────────────────────────────────────── */
  const TAGS_SIN_SEMANTICA = new Set(['span','i','em','b','strong','svg','path','circle','small','sup','sub']);
  const TAGS_RAIZ = new Set(['a','button','label','input','select','textarea','h1','h2','h3','h4','h5','h6','li','p','div','section','article','main','header','nav']);

  function elementoSignificativo(el) {
    let actual = el;
    // Subir si el elemento actual no tiene semántica propia
    while (actual && TAGS_SIN_SEMANTICA.has(actual.tagName.toLowerCase())) {
      actual = actual.parentElement;
    }
    return actual || el;
  }

  /* ── 5. Extraer texto legible ────────────────────── */
  function textoDeElemento(el) {
    // Ignorar elementos del propio widget de accesibilidad
    if (el.closest('#btn-accesibilidad, #acc-toast')) return '';
    if (el === document.body || el === document.documentElement) return '';

    // Subir al elemento con semántica real
    const target = elementoSignificativo(el);

    // Obtener texto y limpiar emojis
    const raw = (
      target.getAttribute('aria-label') ||
      target.getAttribute('title')       ||
      target.getAttribute('placeholder') ||
      target.getAttribute('alt')         ||
      (target.tagName === 'INPUT' ? '' : target.innerText) ||
      ''
    );

    const limpio = limpiarEmojis(raw);

    // Si después de limpiar queda vacío o solo tiene caracteres especiales, ignorar
    if (!limpio || limpio.length < 2) return '';

    // Limitar longitud
    return limpio.length > 150 ? limpio.slice(0, 150) + '...' : limpio;
  }

  /* ── 6. Prefijo de contexto ─────────────────────── */
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

  /* ── 7. Debounce + deduplicación ────────────────── */
  let debounceTimer = null;
  let ultimoTexto   = '';

  function leerConDebounce(el) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      const texto = textoDeElemento(el);
      if (!texto) return;
      // No releer el mismo texto si el cursor se mueve dentro del mismo elemento
      const completo = prefijo(el) + texto;
      if (completo === ultimoTexto) return;
      ultimoTexto = completo;
      leer(completo);
    }, 130);
  }

  /* ── 8. Listeners ───────────────────────────────── */
  function onMouseEnter(e) { if (activo) leerConDebounce(e.target); }
  function onFocusIn(e)    {
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

  /* ── 9. Toast ───────────────────────────────────── */
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

  /* ── 10. Botón flotante ─────────────────────────── */
  function crearBoton() {
    const btn = document.createElement('button');
    btn.id = 'btn-accesibilidad';
    btn.setAttribute('aria-label', 'Activar lector de pantalla');
    btn.setAttribute('title', 'Accesibilidad: lector de voz');
    btn.innerHTML = '<span class="acc-icon">🔊</span><span class="acc-label">Lector</span>';
    document.body.appendChild(btn);

    btn.addEventListener('click', () => {
      cargarVoz(); // Chrome a veces solo las tiene listas al primer click
      activo = !activo;
      btn.classList.toggle('activo', activo);
      btn.setAttribute('aria-label', activo ? 'Desactivar lector' : 'Activar lector de pantalla');

      if (activo) {
        activarListeners();
        setTimeout(() => {
          const u = new SpeechSynthesisUtterance('Lector activado. Pase el cursor por la página para escuchar los elementos.');
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

  /* ── Init ───────────────────────────────────────── */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', crearBoton);
  } else {
    crearBoton();
  }
})();