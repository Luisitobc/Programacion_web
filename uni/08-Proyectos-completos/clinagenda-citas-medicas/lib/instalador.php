<?php
// La primera vez que alguien abre el sistema, la base no existe todavía.
// En lugar de pedirle al jurado que abra phpMyAdmin e importe archivos, esto
// revisa si está la tabla citas y, si no, ejecuta los dos .sql de la carpeta
// sql/. Después de la primera vez no hace nada más que esa revisión.

declare(strict_types=1);

function instalar_si_hace_falta(PDO $pdo, string $base): void
{
    if (tabla_citas_existe($pdo, $base)) return;

    // Si dos pestañas abren el sistema a la vez, solo una instala.
    $pdo->query("SELECT GET_LOCK('clinagenda_instalar', 30)");
    try {
        if (tabla_citas_existe($pdo, $base)) return;

        $s = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?');
        $s->execute([$base]);
        if ((int)$s->fetchColumn() > 0) {
            throw new RuntimeException("La base '$base' ya existe pero tiene otras tablas. "
                . 'Bórrela en phpMyAdmin (o cambie el nombre en config/local.php) y vuelva a cargar.');
        }

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$base` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$base`");
        try {
            foreach (['01_esquema.sql', '02_datos_demo.sql'] as $archivo) {
                foreach (sentencias_sql((string)file_get_contents(__DIR__ . '/../sql/' . $archivo)) as $sentencia) {
                    $pdo->exec($sentencia);
                }
            }
        } catch (Throwable $e) {
            // La base la creamos recién nosotros: si quedó a medias, se quita
            // para que el próximo intento empiece limpio.
            $pdo->exec("DROP DATABASE `$base`");
            throw $e;
        }
    } finally {
        $pdo->query("SELECT RELEASE_LOCK('clinagenda_instalar')");
    }
}

function tabla_citas_existe(PDO $pdo, string $base): bool
{
    $s = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = ?');
    $s->execute([$base, 'citas']);
    return (int)$s->fetchColumn() > 0;
}

/**
 * Parte un archivo .sql en sentencias. Los archivos de sql/ están escritos
 * para que cada sentencia termine con ; al final de la línea. Se saltan
 * CREATE DATABASE y USE porque el nombre de la base sale de la configuración.
 */
function sentencias_sql(string $sql): array
{
    $sinComentarios = (string)preg_replace('/^\s*--.*$/m', '', $sql);
    $partes = preg_split('/;\s*$/m', $sinComentarios) ?: [];
    return array_values(array_filter(array_map('trim', $partes),
        fn($s) => $s !== '' && !preg_match('/^(CREATE DATABASE|USE)\b/i', $s)));
}
