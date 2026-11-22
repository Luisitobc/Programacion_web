<?php
// Conexión a MySQL con PDO. En XAMPP el usuario es root y la contraseña va vacía.
$pdo = new PDO(
    'mysql:host=localhost;dbname=gestor_tareas;charset=utf8mb4',
    'root',
    '',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,       // los errores lanzan excepción
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // filas como arreglo asociativo
    ]
);

// Valores permitidos: se usan para validar formularios y filtros.
const PRIORIDADES = ['baja', 'media', 'alta'];
const ESTADOS = ['pendiente', 'atrasada', 'completada'];

// Escapa texto antes de mostrarlo en HTML (evita XSS).
function e($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

// Redirige y termina el script (patrón POST -> redirección -> GET).
function redirigir($url)
{
    header('Location: ' . $url);
    exit;
}
