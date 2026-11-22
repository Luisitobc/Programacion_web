<?php
$usuario    = "root";
$contrasena = "";

try {
    $mbd = new PDO(
        'mysql:host=localhost;dbname=bd-programacion;charset=utf8mb4',
        $usuario,
        $contrasena,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Se ha realizado la conexión con la base de datos bd-programacion";
    $mbd = null;
} catch (PDOException $e) {
    echo "¡Error!: " . $e->getMessage() . "<br/>";
    die();
}
?>