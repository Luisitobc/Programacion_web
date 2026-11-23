<?php
// Conexión a MySQL con PDO. Se incluye con require en los demás archivos.
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=inventario_tienda;charset=utf8mb4',
        'root',   // usuario (XAMPP por defecto)
        ''        // contraseña (vacía en XAMPP por defecto)
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}
