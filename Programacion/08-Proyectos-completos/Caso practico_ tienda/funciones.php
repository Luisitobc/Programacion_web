<?php
// Constantes y funciones reutilizables.

const CATEGORIAS = ['alimentos', 'limpieza', 'tecnologia'];
const ESTADOS = ['disponible', 'stock_bajo', 'agotado'];
const ETIQUETAS = [
    'disponible' => 'Disponible',
    'stock_bajo' => 'Stock bajo',
    'agotado'    => 'Agotado',
];

// Escapa texto para mostrarlo en HTML (evita XSS).
function e($texto): string {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

// REGLA AUTOMÁTICA DEL ESTADO según el stock.
function calcularEstado(int $stock, int $stockMinimo): string {
    if ($stock === 0) {
        return 'agotado';
    }
    if ($stock <= $stockMinimo) {
        return 'stock_bajo';
    }
    return 'disponible';
}

// Validación en el servidor. Devuelve un mensaje de error o null si todo está bien.
function validarProducto(string $nombre, string $categoria, float $precio, int $stockMinimo, ?int $stock = null): ?string {
    if ($nombre === '') {
        return 'El nombre es obligatorio.';
    }
    if (!in_array($categoria, CATEGORIAS, true)) {
        return 'Categoría no válida.';
    }
    if ($precio <= 0) {
        return 'El precio debe ser mayor que 0.';
    }
    if ($stockMinimo < 0) {
        return 'El stock mínimo no puede ser negativo.';
    }
    if ($stock !== null && $stock < 0) {
        return 'El stock no puede ser negativo.';
    }
    return null;
}
