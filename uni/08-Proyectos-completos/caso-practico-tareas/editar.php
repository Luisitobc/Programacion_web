<?php
// Formulario para cambiar nombre, descripción o prioridad de una tarea.
require 'conexion.php';

$id = (int) ($_GET['id'] ?? 0);
$consulta = $pdo->prepare('SELECT * FROM tareas WHERE id = ?');
$consulta->execute([$id]);
$tarea = $consulta->fetch();

if (!$tarea) {
    redirigir('index.php?error=noexiste');
}

$error = ($_GET['error'] ?? '') === 'datos'
    ? 'Revisa los datos: el nombre es obligatorio y la prioridad debe ser baja, media o alta.'
    : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar tarea</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<main class="contenedor">
    <h1>Editar tarea</h1>

    <?php if ($error): ?><p class="aviso error"><?= e($error) ?></p><?php endif; ?>

    <section class="panel">
        <form action="guardar.php" method="post" class="formulario">
            <input type="hidden" name="id" value="<?= $tarea['id'] ?>">
            <label>Nombre
                <input type="text" name="nombre" maxlength="100" required value="<?= e($tarea['nombre']) ?>">
            </label>
            <label>Descripción
                <textarea name="descripcion" rows="3"><?= e($tarea['descripcion']) ?></textarea>
            </label>
            <label>Prioridad
                <select name="prioridad" required>
                    <?php foreach (PRIORIDADES as $valor): ?>
                        <option value="<?= $valor ?>" <?= $tarea['prioridad'] === $valor ? 'selected' : '' ?>><?= ucfirst($valor) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <p class="datos">
                Estado: <strong><?= ucfirst(e($tarea['estado'])) ?></strong> ·
                Vence: <strong><?= date('d/m/Y', strtotime($tarea['fecha_fin'])) ?></strong>
            </p>
            <div class="fila-botones">
                <button type="submit">Guardar cambios</button>
                <a class="boton secundario" href="index.php">Cancelar</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>
