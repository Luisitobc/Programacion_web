<?php
$usuario="root";
$contraseña="";
$cedula=$_POST['cedula'];
$correo=$_POST['correo'];

try {
    $mbd = new PDO('mysql:host=localhost;dbname=bd-programacion', $usuario, $contraseña);
    $sentencia="UPDATE personas SET correoe='$correo' WHERE cedula='$cedula'";

    //Verificación de la sentencia a ejecutar
    echo(" la sentencia es " . $sentencia . "<br/>");
    $filas = $mbd->exec($sentencia);

    if ($filas > 0) {
        echo ("El correo ha sido actualizado correctamente ");
    } else {
        echo ("No se actualizó ningún registro (la cédula no existe o el correo es el mismo) ");
    }
    echo  "<br/>";
    $mbd = null;
} catch (PDOException $e) {
    print "¡Error!: " . $e->getMessage() . "<br/>";
    die();
}
?>