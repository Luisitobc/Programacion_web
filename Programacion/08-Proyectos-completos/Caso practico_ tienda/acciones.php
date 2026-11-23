<?php
// Procesa todas las acciones (POST) y vuelve a index.php con un mensaje.
require 'conexion.php';
require 'funciones.php';

function volver(string $mensaje, string $tipo = 'ok'): void {
    header('Location: index.php?msg=' . urlencode($mensaje) . '&tipo=' . $tipo);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$accion = $_POST['accion'] ?? '';

try {
    switch ($accion) {

        // ---------- CREAR ----------
        case 'crear':
            $nombre      = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $categoria   = $_POST['categoria'] ?? '';
            $precio      = (float)($_POST['precio'] ?? 0);
            $stock       = (int)($_POST['stock'] ?? 0);
            $stockMin    = (int)($_POST['stock_minimo'] ?? 0);

            $error = validarProducto($nombre, $categoria, $precio, $stockMin, $stock);
            if ($error) {
                volver($error, 'error');
            }

            $sql = 'INSERT INTO productos (nombre, descripcion, categoria, precio, stock, stock_minimo, estado)
                    VALUES (:n, :d, :c, :p, :s, :m, :e)';
            $pdo->prepare($sql)->execute([
                ':n' => $nombre,
                ':d' => $descripcion,
                ':c' => $categoria,
                ':p' => $precio,
                ':s' => $stock,
                ':m' => $stockMin,
                ':e' => calcularEstado($stock, $stockMin),
            ]);
            volver('Producto registrado.');
            break;

        // ---------- EDITAR (nombre, descripción, categoría, precio, stock mínimo) ----------
        case 'editar':
            $id          = (int)($_POST['id'] ?? 0);
            $nombre      = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $categoria   = $_POST['categoria'] ?? '';
            $precio      = (float)($_POST['precio'] ?? 0);
            $stockMin    = (int)($_POST['stock_minimo'] ?? 0);

            $error = validarProducto($nombre, $categoria, $precio, $stockMin);
            if ($error) {
                volver($error, 'error');
            }

            // Se necesita el stock actual para recalcular el estado.
            $q = $pdo->prepare('SELECT stock FROM productos WHERE id = :id');
            $q->execute([':id' => $id]);
            $actual = $q->fetch(PDO::FETCH_ASSOC);
            if (!$actual) {
                volver('Producto no encontrado.', 'error');
            }

            $sql = 'UPDATE productos
                    SET nombre = :n, descripcion = :d, categoria = :c, precio = :p,
                        stock_minimo = :m, estado = :e
                    WHERE id = :id';
            $pdo->prepare($sql)->execute([
                ':n'  => $nombre,
                ':d'  => $descripcion,
                ':c'  => $categoria,
                ':p'  => $precio,
                ':m'  => $stockMin,
                ':e'  => calcularEstado((int)$actual['stock'], $stockMin),
                ':id' => $id,
            ]);
            volver('Producto actualizado.');
            break;

        // ---------- VENDER (descuenta stock) y REPONER (suma stock) ----------
        case 'vender':
        case 'reponer':
            $id       = (int)($_POST['id'] ?? 0);
            $cantidad = (int)($_POST['cantidad'] ?? 0);
            if ($cantidad <= 0) {
                volver('La cantidad debe ser mayor que 0.', 'error');
            }

            $pdo->beginTransaction();
            $q = $pdo->prepare('SELECT stock, stock_minimo FROM productos WHERE id = :id FOR UPDATE');
            $q->execute([':id' => $id]);
            $p = $q->fetch(PDO::FETCH_ASSOC);

            if (!$p) {
                $pdo->rollBack();
                volver('Producto no encontrado.', 'error');
            }

            if ($accion === 'vender') {
                if ($cantidad > (int)$p['stock']) {
                    $pdo->rollBack();
                    volver('No hay stock suficiente. Disponible: ' . $p['stock'] . '.', 'error');
                }
                $nuevoStock = (int)$p['stock'] - $cantidad;
            } else {
                $nuevoStock = (int)$p['stock'] + $cantidad;
            }

            $pdo->prepare('UPDATE productos SET stock = :s, estado = :e WHERE id = :id')->execute([
                ':s'  => $nuevoStock,
                ':e'  => calcularEstado($nuevoStock, (int)$p['stock_minimo']),
                ':id' => $id,
            ]);
            $pdo->commit();
            volver($accion === 'vender' ? 'Venta registrada.' : 'Reposición registrada.');
            break;

        // ---------- ELIMINAR ----------
        case 'eliminar':
            $id = (int)($_POST['id'] ?? 0);
            $pdo->prepare('DELETE FROM productos WHERE id = :id')->execute([':id' => $id]);
            volver('Producto eliminado.');
            break;

        default:
            volver('Acción no válida.', 'error');
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    volver('Error de base de datos.', 'error');
}
