<?php
// Borra la cookie y vuelve al login
setcookie('sesion', '', [
    'expires'  => time() - 3600,
    'path'     => '/',
    'samesite' => 'Lax'
]);
header('Location: login.html');
exit;
