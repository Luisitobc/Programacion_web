<?php
require 'conexion.php';
require 'funciones.php';

// ----- Filtros (GET) -----
$filtroEstado    = $_GET['estado'] ?? '';
$filtroCategoria = $_GET['categoria'] ?? '';

$sql = 'SELECT * FROM productos WHERE 1=1';
$params = [];
if (in_array($filtroEstado, ESTADOS, true)) {
    $sql .= ' AND estado = :e';
    $params[':e'] = $filtroEstado;
}
if (in_array($filtroCategoria, CATEGORIAS, true)) {
    $sql .= ' AND categoria = :c';
    $params[':c'] = $filtroCategoria;
}
$sql .= ' ORDER BY nombre ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ----- Modo edición: ?editar=ID -----
$editar = null;
if (!empty($_GET['editar'])) {
    $q = $pdo->prepare('SELECT * FROM productos WHERE id = :id');
    $q->execute([':id' => (int)$_GET['editar']]);
    $editar = $q->fetch(PDO::FETCH_ASSOC) ?: null;
}

$msg  = $_GET['msg'] ?? '';
$tipo = ($_GET['tipo'] ?? 'ok') === 'error' ? 'error' : 'ok';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inventario - El Ahorro</title>
  <link rel="stylesheet" href="estilos.css">
</head>
<body>
<h1>Inventario - Tienda El Ahorro</h1>

<?php if ($msg !== ''): ?>
  <p class="mensaje <?= $tipo ?>"><?= e($msg) ?></p>
<?php endif; ?>

<!-- ===== FORMULARIO (crear o editar) ===== -->
<form class="panel" method="POST" action="acciones.php" onsubmit="return validarProducto(this)">
  <h2><?= $editar ? 'Editar producto' : 'Registrar producto' ?></h2>
  <input type="hidden" name="accion" value="<?= $editar ? 'editar' : 'crear' ?>">
  <?php if ($editar): ?>
    <input type="hidden" name="id" value="<?= (int)$editar['id'] ?>">
  <?php endif; ?>

  <label>Nombre
    <input type="text" name="nombre" value="<?= e($editar['nombre'] ?? '') ?>">
  </label>
  <label>Descripción
    <textarea name="descripcion"><?= e($editar['descripcion'] ?? '') ?></textarea>
  </label>
  <label>Categoría
    <select name="categoria">
      <?php foreach (CATEGORIAS as $c): ?>
        <option value="<?= $c ?>" <?= ($editar['categoria'] ?? '') === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label>Precio
    <input type="number" name="precio" step="0.01" min="0" value="<?= e($editar['precio'] ?? '') ?>">
  </label>
  <?php if (!$editar): ?>
    <label>Stock inicial
      <input type="number" name="stock" min="0" value="0">
    </label>
  <?php endif; ?>
  <label>Stock mínimo
    <input type="number" name="stock_minimo" min="0" value="<?= e($editar['stock_minimo'] ?? '0') ?>">
  </label>

  <button type="submit"><?= $editar ? 'Actualizar' : 'Agregar producto' ?></button>
  <?php if ($editar): ?><a class="cancelar" href="index.php">Cancelar</a><?php endif; ?>
</form>

<!-- ===== FILTROS ===== -->
<form class="panel filtros" method="GET" action="index.php">
  <select name="estado">
    <option value="">Todos los estados</option>
    <?php foreach (ESTADOS as $est): ?>
      <option value="<?= $est ?>" <?= $filtroEstado === $est ? 'selected' : '' ?>><?= ETIQUETAS[$est] ?></option>
    <?php endforeach; ?>
  </select>
  <select name="categoria">
    <option value="">Todas las categorías</option>
    <?php foreach (CATEGORIAS as $c): ?>
      <option value="<?= $c ?>" <?= $filtroCategoria === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit">Filtrar</button>
  <a class="cancelar" href="index.php">Limpiar</a>
</form>

<!-- ===== LISTADO ===== -->
<?php if (!$productos): ?>
  <p>No hay productos para mostrar.</p>
<?php endif; ?>

<?php foreach ($productos as $p): ?>
  <div class="producto <?= e($p['estado']) ?>">
    <div class="datos">
      <strong><?= e($p['nombre']) ?></strong>
      <span class="etiqueta"><?= ETIQUETAS[$p['estado']] ?></span>
      <p><?= e($p['descripcion']) ?></p>
      <small>
        <?= ucfirst(e($p['categoria'])) ?> ·
        $<?= number_format((float)$p['precio'], 2) ?> ·
        Stock: <strong><?= (int)$p['stock'] ?></strong> (mínimo <?= (int)$p['stock_minimo'] ?>)
      </small>
    </div>

    <div class="acciones">
      <form method="POST" action="acciones.php" onsubmit="return validarCantidad(this)">
        <input type="hidden" name="accion" value="vender">
        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
        <input type="number" name="cantidad" min="1" placeholder="Cant.">
        <button type="submit">Vender</button>
      </form>

      <form method="POST" action="acciones.php" onsubmit="return validarCantidad(this)">
        <input type="hidden" name="accion" value="reponer">
        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
        <input type="number" name="cantidad" min="1" placeholder="Cant.">
        <button type="submit">Reponer</button>
      </form>

      <a href="index.php?editar=<?= (int)$p['id'] ?>">Editar</a>

      <form method="POST" action="acciones.php" onsubmit="return confirm('¿Eliminar este producto?')">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
        <button type="submit" class="peligro">Eliminar</button>
      </form>
    </div>
  </div>
<?php endforeach; ?>

<script src="app.js"></script>
</body>
</html>
