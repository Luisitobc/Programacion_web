/* Se incluye en todas las páginas (menos el login).
   - Si no hay cookie de sesión, manda al login.
   - Si hay, muestra en el header: punto verde + nombre + botón "Cerrar sesión". */
(function () {
    var raiz = document.currentScript.src.replace(/sesion\.js(\?.*)?$/, "");
    var m = document.cookie.match(/(?:^|; )sesion=([^;]*)/);

    if (!m) {
        location.replace(raiz + "login.html");
        return;
    }

    var nombre = decodeURIComponent(m[1]).split(".")[0];

    document.addEventListener("DOMContentLoaded", function () {
        var enc = document.querySelector(".encabezado");
        if (!enc) return;

        var caja = document.createElement("div");
        caja.className = "usuario-sesion";
        caja.innerHTML =
            '<span class="usuario-info" title="Sesión activa">' +
            '<span class="punto-activo"></span>' +
            '<span class="usuario-nombre"></span></span>' +
            '<a class="btn-salir" href="' + raiz + 'cerrar-sesion.php">Cerrar sesión</a>';
        caja.querySelector(".usuario-nombre").textContent = nombre;
        enc.appendChild(caja);
    });
})();
