<?php
$usuario="root";
$contraseña="";
try {
    $mbd = new PDO('mysql:host=localhost;dbname=bd-programacion', $usuario, $contraseña);
    $sentencia="DELETE FROM personas WHERE cedula='9998979695'";
    $mbd->exec($sentencia);
    echo ("LOS DATOS HAN SIDO ELIMINADOS DE LA BASE DE DATOS");
    $mbd = null;
} catch (PDOException $e) {
    print "¡Error!: " . $e->getMessage() . "<br/>";
    die();
}
?>