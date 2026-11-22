<?php
$usuario="root";
$contraseña="";
try {
    $mbd = new PDO('mysql:host=localhost;dbname=bd-programacion', $usuario, $contraseña);
    $sentencia="INSERT INTO personas(cedula, apellidonombre,correoe,fechan)
                VALUES ('9998979695','Abram Stoker','astoker@gmail.com','1900-11-11')";
    $mbd->exec($sentencia);
    echo ("LOS DATOS HAN SIDO INSERTADOS EN LA BASE DE DATOS");
    $mbd = null;
} catch (PDOException $e) {
    print "¡Error!: " . $e->getMessage() . "<br/>";
    die();
}
?>