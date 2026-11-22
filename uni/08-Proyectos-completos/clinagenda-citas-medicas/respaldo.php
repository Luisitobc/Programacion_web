<?php
// Respaldo: descarga toda la base en un archivo .sql que se puede volver a
// cargar en phpMyAdmin. Se arma con PHP, sin depender de mysqldump, para
// que funcione igual en cualquier XAMPP.
require __DIR__ . '/lib/auth.php';

exigir('respaldo');

// En este orden se pueden volver a crear sin pelear con las claves foráneas.
const TABLAS = ['clinicas', 'usuarios', 'medicos', 'medico_horario', 'feriados', 'pacientes', 'citas', 'auditoria'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_csrf();
    registrar('RESPALDO', 'sistema', null, 'Se descargó un respaldo completo de la base');

    $pdo = db();
    $nombre = 'clinagenda-respaldo-' . date('Y-m-d-Hi') . '.sql';
    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nombre . '"');
    header('Cache-Control: no-store');

    echo "-- Respaldo de ClinAgenda, " . date('d/m/Y H:i') . ", hecho por " . usuario_actual()['nombre'] . "\n";
    echo "-- Para recuperarlo: en phpMyAdmin elija la base clinagenda, pestaña Importar, y cargue este archivo.\n\n";
    echo "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n";
    foreach (array_reverse(TABLAS) as $t) echo "DROP TABLE IF EXISTS `$t`;\n";
    echo "\n";

    foreach (TABLAS as $t) {
        echo $pdo->query("SHOW CREATE TABLE `$t`")->fetch()['Create Table'] . ";\n\n";

        // Las columnas calculadas (slot_medico, slot_paciente) no se copian: la base las vuelve a calcular.
        $columnas = $pdo->query("SELECT COLUMN_NAME FROM information_schema.columns
                                 WHERE table_schema = DATABASE() AND table_name = '$t' AND EXTRA NOT LIKE '%GENERATED%'
                                 ORDER BY ORDINAL_POSITION")->fetchAll(PDO::FETCH_COLUMN);
        $lista = '`' . implode('`, `', $columnas) . '`';
        $filas = $pdo->query("SELECT $lista FROM `$t` ORDER BY 1");
        while ($f = $filas->fetch(PDO::FETCH_NUM)) {
            $valores = array_map(fn($v) => $v === null ? 'NULL' : (is_int($v) ? (string)$v : $pdo->quote((string)$v)), $f);
            echo "INSERT INTO `$t` ($lista) VALUES (" . implode(', ', $valores) . ");\n";
        }
        echo "\n";
    }
    echo "SET FOREIGN_KEY_CHECKS = 1;\n";
    exit;
}

cabecera('Respaldo', ['volver' => 'inicio.php']);
?>

<div class="tarjeta">
  <h1>Respaldo</h1>
  <p>Descarga una copia completa del sistema: pacientes, citas, cuentas y la auditoría.
     Guárdela fuera de esta computadora, por ejemplo en una memoria USB.</p>
  <p class="suave">Para recuperarla: en phpMyAdmin, elija la base <code>clinagenda</code>, entre a
     <em>Importar</em> y cargue el archivo. También se puede abrir en Excel para sacar reportes.</p>
  <form method="post" action="respaldo.php">
    <?= campo_csrf() ?>
    <button class="btn ancho" type="submit"><?= icono('respaldo') ?> Descargar el respaldo</button>
  </form>
</div>

<div class="tarjeta">
  <h2>La hoja del día, para el plan en papel</h2>
  <p>Si un lunes a las 8 el sistema no prende, la recepción trabaja con la hoja impresa de la
     agenda. Se imprime desde <a href="agenda.php">la agenda del día</a>, con el botón
     <strong>Imprimir la hoja del día</strong>. Lo que se anote a mano se pasa después con el
     formulario normal de Agendar.</p>
</div>

<?php pie(['activo' => 'respaldo']); ?>
