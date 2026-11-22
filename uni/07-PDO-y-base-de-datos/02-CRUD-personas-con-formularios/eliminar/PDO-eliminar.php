<?php
$usuario="root";
$contraseña="";
$cedula=$_POST['cedula'];

try {
    $mbd = new PDO('mysql:host=localhost;dbname=bd-programacion', $usuario, $contraseña);
    $sentencia="DELETE FROM personas WHERE cedula='$cedula'";

    //Verificación de la sentencia a ejecutar
    echo(" la sentencia es " . $sentencia . "<br/>");
    $filas = $mbd->exec($sentencia);

    if ($filas > 0) {
        echo ("El registro ha sido eliminado correctamente ");
    } else {
        echo ("No se eliminó ningún registro (la cédula no existe) ");
    }
    echo  "<br/>";
    $mbd = null;
} catch (PDOException $e) {
    print "¡Error!: " . $e->getMessage() . "<br/>";
    die();
}
?>