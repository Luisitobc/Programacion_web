/* ClinAgenda · service worker.
   Va en la raíz para que cubra todo el sistema. Hace dos cosas:
   - guarda una copia de los estilos, el JavaScript y el ícono, para que todo se vea bien sin red,
   - guarda la última versión de cada pantalla visitada, por si el servidor no responde.
   Las citas sin conexión no pasan por aquí: las maneja assets/app.js. */
var ESTATICOS = 'clinagenda-v1';
var PAGINAS = 'clinagenda-paginas';
var ARCHIVOS = ['assets/estilos.css', 'assets/app.js', 'assets/icono.svg', 'assets/manifest.json'];

self.addEventListener('install', function (ev) {
  ev.waitUntil(caches.open(ESTATICOS).then(function (c) { return c.addAll(ARCHIVOS); }));
  self.skipWaiting();
});

self.addEventListener('activate', function (ev) {
  ev.waitUntil(caches.keys().then(function (nombres) {
    return Promise.all(nombres.filter(function (n) { return n !== ESTATICOS && n !== PAGINAS; })
      .map(function (n) { return caches.delete(n); }));
  }).then(function () { return self.clients.claim(); }));
});

self.addEventListener('fetch', function (ev) {
  var r = ev.request;
  if (r.method !== 'GET' || new URL(r.url).origin !== self.location.origin) return;

  if (r.mode === 'navigate') {
    ev.respondWith(fetch(r).then(function (resp) {
      if (resp.ok && !resp.redirected) {
        var copia = resp.clone();
        caches.open(PAGINAS).then(function (c) { c.put(r, copia); });
      }
      return resp;
    }).catch(function () {
      return caches.match(r).then(function (guardada) {
        return guardada || new Response(
          '<!DOCTYPE html><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">' +
          '<link rel="stylesheet" href="assets/estilos.css"><main class="sin-nav"><div class="tarjeta"><div class="vacio">' +
          '<h1>No hay conexión con el sistema</h1><p>Esta pantalla no se había abierto antes en este equipo.' +
          ' Vuelva a la anterior: las citas que guarde se quedan aquí y se envían cuando vuelva la conexión.</p>' +
          '<p><a class="btn" href="javascript:history.back()">Volver</a></p></div></div></main>',
          { headers: { 'Content-Type': 'text/html; charset=utf-8' } });
      });
    }));
    return;
  }

  // Primero la red, para que un cambio en los estilos se vea al momento; sin red, la copia guardada.
  ev.respondWith(fetch(r).then(function (resp) {
    if (resp.ok) { var copia = resp.clone(); caches.open(ESTATICOS).then(function (c) { c.put(r, copia); }); }
    return resp;
  }).catch(function () { return caches.match(r); }));
});
