/* ClinAgenda · el poco JavaScript que hay.
   El sistema funciona sin él. Esto agrega cuatro cosas:
   1. ver la contraseña al escribirla,
   2. que un doble clic no mande el formulario dos veces,
   3. pedir confirmación en los botones que la necesitan,
   4. el modo sin conexión: avisa con una franja amarilla y, si al guardar
      una cita el servidor no responde, la deja en este equipo y la envía
      sola cuando vuelve la conexión. */
(function () {
  'use strict';

  // 1. Mostrar la contraseña
  var ver = document.getElementById('ver-clave');
  var clave = document.getElementById('clave');
  if (ver && clave) ver.addEventListener('change', function () { clave.type = ver.checked ? 'text' : 'password'; });

  // 2. Un solo envío. La defensa de verdad está en el servidor (el token de la cita).
  document.querySelectorAll('[data-una-vez]').forEach(function (b) {
    b.form.addEventListener('submit', function (ev) {
      if (ev.defaultPrevented) return;
      setTimeout(function () { b.disabled = true; b.textContent = 'Guardando…'; }, 0);
    });
  });

  // 3. Confirmaciones
  document.querySelectorAll('form[data-confirmar]').forEach(function (f) {
    f.addEventListener('submit', function (ev) {
      if (!window.confirm(f.getAttribute('data-confirmar'))) ev.preventDefault();
    });
  });

  // 4. Sin conexión
  var COLA = 'clinagenda-cola';
  var franja = document.getElementById('sin-conexion');
  var ficha = document.querySelector('meta[name="csrf"]');
  var enviando = false;

  function leerCola() { try { return JSON.parse(localStorage.getItem(COLA) || '[]'); } catch (e) { return []; } }
  function guardarCola(c) { try { localStorage.setItem(COLA, JSON.stringify(c)); } catch (e) { /* sin espacio: se avisa abajo */ } }
  function plural(n) { return n === 1 ? '1 cita guardada' : n + ' citas guardadas'; }

  function avisar(texto, bien) {
    if (!franja) return;
    franja.textContent = texto;
    franja.hidden = !texto;
    franja.classList.toggle('ok', !!bien);
  }

  function pintarEstado() {
    var n = leerCola().length;
    if (!navigator.onLine) {
      avisar('Sin conexión. Puede seguir trabajando' + (n
        ? ': hay ' + plural(n) + ' en este equipo, se enviarán solas al volver la conexión.'
        : '. Si al guardar una cita el sistema no responde, se queda en este equipo y se envía sola después.'));
    } else if (n) {
      avisar('Hay ' + plural(n) + ' en este equipo esperando para enviarse…');
    } else if (franja && !franja.classList.contains('ok')) {
      avisar('');
    }
  }

  function enviar(datos) {
    return fetch('agendar.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-ClinAgenda-Cola': '1' },
      body: datos
    }).then(function (r) { return r.json(); });
  }

  // Envía las citas pendientes de una en una. El token de cada cita hace que,
  // si una ya había llegado, no se guarde dos veces.
  function vaciarCola() {
    var cola = leerCola();
    if (enviando || !navigator.onLine || !cola.length || !ficha) return;
    enviando = true;
    var datos = new URLSearchParams(cola[0].datos);
    datos.set('csrf', ficha.content);
    enviar(datos.toString()).then(function (r) {
      enviando = false;
      if (r.reintentar) { avisar(r.mensaje); return; }
      var resto = leerCola();
      resto.shift();
      guardarCola(resto);
      avisar(r.mensaje, r.ok);
      if (resto.length) vaciarCola();
    }).catch(function () { enviando = false; pintarEstado(); });
  }

  document.querySelectorAll('form[data-cola]').forEach(function (f) {
    f.addEventListener('submit', function (ev) {
      if (navigator.onLine || ev.defaultPrevented) return;   // con conexión, el formulario normal
      ev.preventDefault();
      var datos = new URLSearchParams(new FormData(f)).toString();
      var boton = f.querySelector('button[type="submit"]');
      enviar(datos).then(function (r) {
        // Sin internet, pero el servidor está en esta misma computadora y respondió.
        if (r.ok) { window.location.href = 'agenda.php'; return; }
        avisar(r.mensaje);
        if (boton) { boton.disabled = false; boton.textContent = 'Guardar la cita'; }
      }).catch(function () {
        var cola = leerCola();
        cola.push({ datos: datos, cuando: new Date().toISOString() });
        guardarCola(cola);
        pintarEstado();
        if (boton) { boton.disabled = true; boton.textContent = 'Guardada en este equipo: se enviará sola'; }
      });
    });
  });

  window.addEventListener('online', function () { pintarEstado(); vaciarCola(); });
  window.addEventListener('offline', pintarEstado);
  setInterval(vaciarCola, 15000);
  pintarEstado();
  vaciarCola();

  // Instalable como aplicación, y la última versión de cada pantalla queda a mano sin red.
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('sw.js').catch(function () { /* sin service worker, todo sigue igual */ });
    // En la pantalla de entrar no hay sesión: se borran las pantallas guardadas del usuario anterior.
    if (ver && window.caches) caches.delete('clinagenda-paginas');
  }
})();
