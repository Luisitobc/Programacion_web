<?php
$usuario="root";
$contraseña="";
try {
    $mbd = new PDO('mysql:host=localhost;dbname=bd-programacion', $usuario, $contraseña);
    echo ("LOS DATOS DE LA BASE DE DATOS SON: ");
    echo("<br/>");
    $sentencia="SELECT * from personas where cedula='1021548474'";
    foreach($mbd->query($sentencia) as $fila) {
        echo ("{$fila['cedula']}--");
        echo ("{$fila['apellidonombre']}--");
        echo ("{$fila['correoe']}--");
        echo ("{$fila['fechan']} <br/>");
    }
    $mbd = null;
} catch (PDOException $e) {
    print "¡Error!: " . $e->getMessage() . "<br/>";
    die();
}
?>