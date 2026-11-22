<?php
$usuario="root";
$contraseña="";
$cedula=$_POST['cedula'];

//Verificación del dato a buscar
echo("La búsqueda del dato " . $cedula);
echo  "<br/>";

$mbd = new PDO('mysql:host=localhost;dbname=bd-programacion', $usuario, $contraseña);

$sentencia="SELECT * from personas where cedula=$cedula";

//Verificación de la sentencia a ejecutar
echo(" la sentencia es " . $sentencia). "<br/>";
echo ("Los datos son los siguientes: ");
echo  "<br/>";
    foreach($mbd->query($sentencia) as $fila) {
        echo ("{$fila['cedula']}--");
        echo ("{$fila['apellidonombre']}--");
        echo ("{$fila['correoe']}--");
        echo ("{$fila['fechan']} <br/>");
    }
    $mbd = null;
?>