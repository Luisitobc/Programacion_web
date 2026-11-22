<?php
/**
 * config/utilidades.php
 * Funciones auxiliares usadas por las vistas y el controlador.
 */
declare(strict_types=1);

/** Escapa texto antes de imprimirlo en HTML (evita XSS). */
function e(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/** Formatea un precio: 12.5 -> $ 12.50 */
function precio(float|string $valor): string
{
    return '$ ' . number_format((float) $valor, 2, '.', ',');
}

/** Guarda un mensaje flash que se mostrará en la siguiente página. */
function mensaje(string $tipo, string $texto): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
}

/** Devuelve y borra el mensaje flash pendiente. */
function obtenerMensaje(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/** Redirige a una acción del controlador y termina la ejecución (patrón POST-Redirect-GET). */
function redirigir(string $accion): void
{
    header('Location: index.php?accion=' . $accion);
    exit;
}

/** Ruta de la imagen del producto, con imagen por defecto si no existe. */
function rutaImagen(?string $archivo): string
{
    $archivo = trim((string) $archivo);
    if ($archivo !== '' && file_exists(__DIR__ . '/../img/' . $archivo)) {
        return 'img/' . $archivo;
    }
    return 'img/sin-imagen.svg';
}
