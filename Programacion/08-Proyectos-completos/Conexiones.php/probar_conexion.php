<?php
// Abre este archivo en el navegador para comprobar que la conexión funciona.
// Muestra los mismos datos que verías en phpMyAdmin (tabla productos).
require 'conexion.php';

echo '<h2>Conexión correcta a la base: ' . htmlspecialchars($base) . '</h2>';

$filas = $pdo->query('SELECT id, nombre, stock, stock_minimo, estado FROM productos ORDER BY id')
             ->fetchAll(PDO::FETCH_ASSOC);

if (!$filas) {
    echo '<p>La tabla productos está vacía.</p>';
    exit;
}

echo '<table border="1" cellpadding="6"><tr>';
foreach (array_keys($filas[0]) as $columna) {
    echo '<th>' . htmlspecialchars($columna) . '</th>';
}
echo '</tr>';
foreach ($filas as $fila) {
    echo '<tr>';
    foreach ($fila as $valor) {
        echo '<td>' . htmlspecialchars((string)$valor) . '</td>';
    }
    echo '</tr>';
}
echo '</table>';
