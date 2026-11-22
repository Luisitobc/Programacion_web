<?php
// Marca una tarea como completada o la elimina.
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('index.php');
}

$id = (int) ($_POST['id'] ?? 0);
$accion = $_POST['accion'] ?? '';

if ($accion === 'completar') {
    $pdo->prepare("UPDATE tareas SET estado = 'completada' WHERE id = ?")->execute([$id]);
    redirigir('index.php?ok=completada');
}

if ($accion === 'eliminar') {
    $pdo->prepare('DELETE FROM tareas WHERE id = ?')->execute([$id]);
    redirigir('index.php?ok=eliminada');
}

redirigir('index.php');
