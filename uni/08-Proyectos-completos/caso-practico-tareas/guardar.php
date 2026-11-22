<?php
// Crea una tarea nueva o actualiza una existente (si llega un id).
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('index.php');
}

$id = (int) ($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$prioridad = $_POST['prioridad'] ?? '';

// Validación en el servidor: el "required" del HTML se puede saltar.
$datosValidos = $nombre !== '' && mb_strlen($nombre) <= 100 && in_array($prioridad, PRIORIDADES, true);

if ($id > 0) {
    // Actualizar: solo nombre, descripción y prioridad (lo que pide el caso).
    if (!$datosValidos) {
        redirigir("editar.php?id=$id&error=datos");
    }
    $sql = 'UPDATE tareas SET nombre = ?, descripcion = ?, prioridad = ? WHERE id = ?';
    $pdo->prepare($sql)->execute([$nombre, $descripcion, $prioridad, $id]);
    redirigir('index.php?ok=actualizada');
}

// Crear: además se valida la fecha (formato AAAA-MM-DD).
if (!$datosValidos) {
    redirigir('index.php?error=datos');
}
$fecha = $_POST['fecha_fin'] ?? '';
$fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
    redirigir('index.php?error=fecha');
}

$sql = 'INSERT INTO tareas (nombre, descripcion, prioridad, fecha_fin) VALUES (?, ?, ?, ?)';
$pdo->prepare($sql)->execute([$nombre, $descripcion, $prioridad, $fecha]);
redirigir('index.php?ok=creada');
