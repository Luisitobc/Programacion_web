<?php
// Conexión a MySQL/MariaDB con PDO.
// phpMyAdmin NO interviene aquí: es solo una pantalla para ver la MISMA base de datos.

// ===== Cambia solo esta línea: 'xampp' (laboratorio) o 'fedora' (tu equipo) =====
$entorno = 'xampp';
// ================================================================================

$configuraciones = [
    'xampp'  => ['usuario' => 'root',   'clave' => ''],            // XAMPP por defecto
    'fedora' => ['usuario' => 'tienda', 'clave' => 'tienda123'],   // usuario creado con CREATE USER
];

$host    = 'localhost';
$base    = 'inventario_tienda';
$usuario = $configuraciones[$entorno]['usuario'];
$clave   = $configuraciones[$entorno]['clave'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$base;charset=utf8mb4", $usuario, $clave);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}
