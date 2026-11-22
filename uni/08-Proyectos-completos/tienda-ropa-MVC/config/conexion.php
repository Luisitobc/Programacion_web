<?php
/**
 * config/conexion.php
 * ---------------------------------------------------------------
 * Único punto de conexión a MySQL mediante PDO.
 * Si cambian las credenciales, SOLO se edita este archivo.
 * ---------------------------------------------------------------
 */
declare(strict_types=1);

// ----------------- Parámetros de conexión (XAMPP) ----------------
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NOMBRE', 'tienda_ropa');
define('DB_USUARIO', 'root');
define('DB_CLAVE', '');          // en XAMPP root normalmente no tiene clave
define('DB_CHARSET', 'utf8mb4');

/**
 * Devuelve una única instancia de PDO (patrón Singleton simple).
 *
 * @throws void  Si falla la conexión se muestra un mensaje y se detiene la app.
 */
function obtenerConexion(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;                      // reutiliza la conexión ya abierta
    }

    $dsn = 'mysql:host=' . DB_HOST
         . ';port=' . DB_PORT
         . ';dbname=' . DB_NOMBRE
         . ';charset=' . DB_CHARSET;

    $opciones = [
        // 1. Lanza excepciones ante cualquier error de SQL -> manejo con try/catch
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // 2. Los resultados llegan como arreglos asociativos
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // 3. Usa sentencias preparadas REALES del motor (no emuladas)
        PDO::ATTR_EMULATE_PREPARES   => false,
        // 4. Conexión persistente desactivada (recomendado en desarrollo)
        PDO::ATTR_PERSISTENT         => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USUARIO, DB_CLAVE, $opciones);
    } catch (PDOException $e) {
        // El detalle técnico va al log del servidor, NO al navegador
        error_log('[CONEXION] ' . $e->getMessage());

        http_response_code(500);
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
           . '<title>Error de conexión</title></head><body style="font-family:system-ui;padding:40px">'
           . '<h1>No se pudo conectar a la base de datos</h1>'
           . '<p>Verifique que <strong>MySQL esté iniciado en XAMPP</strong> y que la base '
           . '<code>' . DB_NOMBRE . '</code> haya sido importada desde '
           . '<code>sql/bd_tienda_ropa.sql</code>.</p>'
           . '</body></html>';
        exit;
    }

    return $pdo;
}
