<?php
$usuario="root";
$contraseña="";
try {
    $mbd = new PDO('mysql:host=localhost;dbname=bd-programacion', $usuario, $contraseña);
    echo ("LOS DATOS DE LA BASE DE DATOS SON: ");
    echo("<br/>");
    foreach($mbd->query('SELECT * from personas') as $fila) {
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