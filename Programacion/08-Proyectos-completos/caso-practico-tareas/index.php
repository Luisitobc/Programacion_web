<?php
require 'conexion.php';

// 1. Regla automática: toda tarea pendiente cuya fecha ya pasó queda atrasada.
$pdo->exec("UPDATE tareas SET estado = 'atrasada' WHERE estado = 'pendiente' AND fecha_fin < CURDATE()");

// 2. Filtros por estado y prioridad (solo se aceptan valores conocidos).
$estado = in_array($_GET['estado'] ?? '', ESTADOS, true) ? $_GET['estado'] : '';
$prioridad = in_array($_GET['prioridad'] ?? '', PRIORIDADES, true) ? $_GET['prioridad'] : '';

$sql = 'SELECT * FROM tareas WHERE 1 = 1';
$params = [];
if ($estado !== '') {
    $sql .= ' AND estado = ?';
    $params[] = $estado;
}
if ($prioridad !== '') {
    $sql .= ' AND prioridad = ?';
    $params[] = $prioridad;
}
$sql .= " ORDER BY FIELD(estado, 'atrasada', 'pendiente', 'completada'), fecha_fin";

$consulta = $pdo->prepare($sql);
$consulta->execute($params);
$tareas = $consulta->fetchAll();

// 3. Mensajes que llegan después de guardar, completar o eliminar.
$mensajes = [
    'creada' => 'Tarea agregada.',
    'actualizada' => 'Tarea actualizada.',
    'completada' => 'Tarea marcada como completada.',
    'eliminada' => 'Tarea eliminada.',
];
$errores = [
    'datos' => 'Revisa los datos: el nombre es obligatorio y la prioridad debe ser baja, media o alta.',
    'fecha' => 'La fecha estimada no es válida.',
    'noexiste' => 'La tarea que buscas ya no existe.',
];
$ok = $mensajes[$_GET['ok'] ?? ''] ?? '';
$error = $errores[$_GET['error'] ?? ''] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestor de tareas</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<main class="contenedor">
    <h1>Gestor de tareas</h1>

    <?php if ($ok): ?><p class="aviso ok"><?= e($ok) ?></p><?php endif; ?>
    <?php if ($error): ?><p class="aviso error"><?= e($error) ?></p><?php endif; ?>

    <section class="panel">
        <h2>Nueva tarea</h2>
        <form action="guardar.php" method="post" class="formulario">
            <label>Nombre
                <input type="text" name="nombre" maxlength="100" required>
            </label>
            <label>Descripción
                <textarea name="descripcion" rows="3"></textarea>
            </label>
            <div class="fila">
                <label>Prioridad
                    <select name="prioridad" required>
                        <option value="baja">Baja</option>
                        <option value="media" selected>Media</option>
                        <option value="alta">Alta</option>
                    </select>
                </label>
                <label>Fecha estimada de finalización
                    <input type="date" name="fecha_fin" required>
                </label>
            </div>
            <button type="submit">Agregar tarea</button>
        </form>
    </section>

    <section class="panel">
        <h2>Tareas (<?= count($tareas) ?>)</h2>

        <form method="get" class="filtros">
            <label>Estado
                <select name="estado">
                    <option value="">Todos</option>
                    <?php foreach (ESTADOS as $valor): ?>
                        <option value="<?= $valor ?>" <?= $estado === $valor ? 'selected' : '' ?>><?= ucfirst($valor) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Prioridad
                <select name="prioridad">
                    <option value="">Todas</option>
                    <?php foreach (PRIORIDADES as $valor): ?>
                        <option value="<?= $valor ?>" <?= $prioridad === $valor ? 'selected' : '' ?>><?= ucfirst($valor) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit">Filtrar</button>
            <a href="index.php">Quitar filtros</a>
        </form>

        <?php if (!$tareas): ?>
            <p class="vacio">No hay tareas con esos filtros.</p>
        <?php endif; ?>

        <?php foreach ($tareas as $t): ?>
            <article class="tarea prioridad-<?= e($t['prioridad']) ?> <?= $t['estado'] === 'completada' ? 'hecha' : '' ?>">
                <div class="tarea-info">
                    <h3><?= e($t['nombre']) ?></h3>
                    <?php if ((string) $t['descripcion'] !== ''): ?>
                        <p><?= nl2br(e($t['descripcion'])) ?></p>
                    <?php endif; ?>
                    <p class="datos">
                        <span class="etiqueta">Prioridad <?= e($t['prioridad']) ?></span>
                        <span class="etiqueta estado-<?= e($t['estado']) ?>"><?= ucfirst(e($t['estado'])) ?></span>
                        <span>Vence: <?= date('d/m/Y', strtotime($t['fecha_fin'])) ?></span>
                    </p>
                </div>
                <div class="acciones">
                    <?php if ($t['estado'] !== 'completada'): ?>
                        <form action="acciones.php" method="post">
                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                            <button type="submit" name="accion" value="completar">Completar</button>
                        </form>
                    <?php endif; ?>
                    <a class="boton secundario" href="editar.php?id=<?= $t['id'] ?>">Editar</a>
                    <form action="acciones.php" method="post" onsubmit="return confirm('¿Eliminar esta tarea?');">
                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                        <button type="submit" name="accion" value="eliminar" class="peligro">Eliminar</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
</main>
</body>
</html>
