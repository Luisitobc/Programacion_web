<?php
$usuario="root";
$contraseña="";
$cedula=$_POST['cedula'];
$nombre=$_POST['nombre'];
$correo=$_POST['correo'];
$fechan=$_POST['fechan'];

//Verificación del dato a buscar

try {
    $mbd = new PDO('mysql:host=localhost;dbname=bd-programacion', $usuario, $contraseña);
    $sentencia="INSERT INTO personas(cedula, apellidonombre, correoe, fechan) VALUES ('$cedula','$nombre','$correo','$fechan')";

    //Verificación de la sentencia a ejecutar
    echo(" la sentencia es" . $sentencia). "<br/>";
    $mbd->exec($sentencia);
    echo ("Los datos han sido ingresados correctamente ");
    echo  "<br/>";
        $mbd = null;
} catch (PDOException $e) {
    print "¡Error!: " . $e->getMessage() . "<br/>";
    die();
}
?>