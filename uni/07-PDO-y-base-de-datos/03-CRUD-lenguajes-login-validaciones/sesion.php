<?php
/* Sesión simple con cookie. Usuario y clave están definidos aquí (no van a la base de datos). */
const SESION_USUARIO = 'alejandro';      // se compara sin importar mayúsculas
const SESION_NOMBRE  = 'Alejandro';      // nombre que se muestra
const SESION_CLAVE   = 'admin123';
const SESION_SECRETO = 'lenguajes-ejercicio-3';   // para firmar la cookie y que no se pueda inventar

function firma_sesion($nombre) {
    return hash_hmac('sha256', $nombre, SESION_SECRETO);
}

// La cookie "sesion" vale:  Alejandro.<firma>
function sesion_activa() {
    if (empty($_COOKIE['sesion'])) return false;
    $p = explode('.', $_COOKIE['sesion'], 2);
    return count($p) === 2 && $p[0] === SESION_NOMBRE && hash_equals(firma_sesion($p[0]), $p[1]);
}

// Se usa al inicio de los PHP de las carpetas: si no hay sesión, manda al login
function exigir_sesion() {
    if (!sesion_activa()) {
        header('Location: ../login.html');
        exit;
    }
}
