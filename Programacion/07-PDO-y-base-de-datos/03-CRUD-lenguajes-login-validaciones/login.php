<?php
require 'sesion.php';

$u = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$c = isset($_POST['clave']) ? $_POST['clave'] : '';

if (strtolower($u) === SESION_USUARIO && hash_equals(SESION_CLAVE, $c)) {
    // cookie de sesión (se borra al cerrar el navegador); JavaScript la lee para mostrar el nombre
    setcookie('sesion', SESION_NOMBRE . '.' . firma_sesion(SESION_NOMBRE), [
        'expires'  => 0,
        'path'     => '/',
        'samesite' => 'Lax'
    ]);
    header('Location: index.html');
    exit;
}

header('Location: login.html?error=1');
exit;
