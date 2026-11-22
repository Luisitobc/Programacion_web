<?php
// Conexión a la base y las tres o cuatro funciones con las que el resto del
// sistema le habla. Todas las consultas son preparadas: lo que escribe la
// gente nunca se pega dentro del SQL.

declare(strict_types=1);

date_default_timezone_set('America/Guayaquil');
mb_internal_encoding('UTF-8');

// Los errores técnicos van a un archivo, no a la pantalla.
const ARCHIVO_ERRORES = __DIR__ . '/../errores.log';
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', ARCHIVO_ERRORES);

set_exception_handler(function (Throwable $e): void {
    error_log(date('Y-m-d H:i:s') . ' ' . $e . PHP_EOL, 3, ARCHIVO_ERRORES);
    if (!headers_sent()) http_response_code(500);

    $sinBase = $e instanceof PDOException && in_array((int)$e->getCode(), [2002, 1045, 2006], true);
    $titulo  = $sinBase ? 'No se pudo abrir la base de datos' : 'Algo falló en esta pantalla';
    $texto   = $sinBase
        ? 'Revise que MySQL esté encendido en el panel de XAMPP y vuelva a cargar la página.'
        : 'Lo que ya estaba guardado sigue a salvo. Vuelva a la pantalla anterior e inténtelo otra vez. Si se repite, avise a la administración.';
    // Los avisos de configuración los escribimos nosotros y sí sirven en pantalla.
    if (get_class($e) === RuntimeException::class) {
        $titulo = 'Falta un paso de configuración';
        $texto  = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>ClinAgenda</title><link rel="stylesheet" href="assets/estilos.css"></head>'
       . '<body><main class="sin-nav"><div class="tarjeta"><div class="vacio">'
       . '<h1>' . $titulo . '</h1><p>' . $texto . '</p>'
       . '<p><a class="btn" href="inicio.php">Volver al inicio</a></p>'
       . '</div></div></main></body></html>';
});

function config(): array
{
    static $c = null;
    if ($c === null) {
        $local = __DIR__ . '/../config/local.php';
        $c = require (is_file($local) ? $local : __DIR__ . '/../config/local.example.php');
        if (!preg_match('/^\w+$/', (string)$c['base'])) {
            throw new RuntimeException('El nombre de la base en config/local.php solo puede tener letras, números y guion bajo.');
        }
    }
    return $c;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;

    $c = config();
    $pdo = new PDO(
        "mysql:host={$c['host']};port={$c['puerto']};charset=utf8mb4",
        $c['usuario'],
        $c['clave'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
    // Misma hora en PHP y en MySQL. Ecuador no cambia de hora en el año.
    $pdo->exec("SET time_zone = '-05:00'");

    require_once __DIR__ . '/instalador.php';
    instalar_si_hace_falta($pdo, $c['base']);
    $pdo->exec('USE `' . $c['base'] . '`');
    return $pdo;
}

function consulta(string $sql, array $parametros = []): PDOStatement
{
    $s = db()->prepare($sql);
    $s->execute($parametros);
    return $s;
}

function fila(string $sql, array $parametros = []): ?array
{
    $r = consulta($sql, $parametros)->fetch();
    return $r === false ? null : $r;
}

function filas(string $sql, array $parametros = []): array
{
    return consulta($sql, $parametros)->fetchAll();
}

function valor(string $sql, array $parametros = [])
{
    return consulta($sql, $parametros)->fetchColumn();
}

/** Ejecuta $fn dentro de una transacción. Si algo falla, no queda nada a medias. */
function transaccion(callable $fn)
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $resultado = $fn();
        if ($pdo->inTransaction()) $pdo->commit();
        return $resultado;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

/** ¿El error es un choque con una clave única? Devuelve el nombre de la clave, o null. */
function clave_repetida(PDOException $e): ?string
{
    if ((int)($e->errorInfo[1] ?? 0) !== 1062) return null;
    return preg_match("/for key '(?:[\w]+\.)?(\w+)'/", (string)($e->errorInfo[2] ?? ''), $m) ? $m[1] : '';
}
