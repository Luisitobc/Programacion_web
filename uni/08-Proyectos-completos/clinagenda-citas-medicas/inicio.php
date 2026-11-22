<?php
// Inicio. Todas las personas entran a esta misma pantalla de cuadros; lo que
// cambia es cuáles ve cada una según lo que su rol le permite hacer. La
// pantalla sencilla no cambia los cuadros, cambia su tamaño.
require __DIR__ . '/lib/auth.php';

exigir_login();

$u      = usuario_actual();
$hoy    = date('Y-m-d');
$hora   = (int)date('G');
$saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
$nombre = explode(' ', str_replace(['Dra. ', 'Dr. ', 'Lcda. '], '', $u['nombre']))[0];

// Si hoy ya no queda ninguna cita, en lugar de un "0" seco se dice cuándo es
// la próxima: esa es la pregunta que la persona realmente tiene.
$ids        = array_map(fn($c) => (int)$c['id'], clinicas_visibles());
$pendientes = contar_pendientes($ids, $hoy);
$fechaResumen = $hoy;
$cuando = 'hoy en ' . ($u['clinica_id'] ? clinica((int)$u['clinica_id'])['nombre'] : 'las tres clínicas');
if ($pendientes === 0 && ($proximo = proximo_dia_con_citas($ids, $hoy))) {
    $pendientes   = contar_pendientes($ids, $proximo);
    $fechaResumen = $proximo;
    $cuando       = 'el ' . fecha_larga($proximo);
}

$cuadros = [];
if (puede('ver_agenda')) $cuadros[] = ['agenda.php', 'agenda', 'acento', 'Ver las citas',
    puede('ver_todas_las_clinicas') ? 'La agenda de las tres clínicas' : 'Quién viene hoy y a qué hora'];
if (puede('agendar'))  $cuadros[] = ['agendar.php', 'mas', '', 'Agendar', 'Dar una cita nueva a un paciente'];
if (puede('cancelar')) $cuadros[] = ['agenda.php?modo=cancelar', 'cancelar', '', 'Cancelar', 'Quitar una cita y dejar la hora libre'];
if (puede('ver_pacientes')) $cuadros[] = ['pacientes.php', 'pacientes', '', 'Pacientes', 'Buscar o registrar a una persona'];
if (puede('gestionar_usuarios'))  $cuadros[] = ['admin.php?s=usuarios', 'usuarios', 'mando', 'Usuarios', 'Quién entra y qué puede hacer'];
if (puede('gestionar_catalogos')) $cuadros[] = ['admin.php?s=medicos', 'medicos', 'mando', 'Médicos y feriados', 'Duración de las citas y días libres'];
if (puede('ver_auditoria')) $cuadros[] = ['auditoria.php', 'auditoria', 'mando', 'Auditoría', 'Quién hizo qué y cuándo'];
if (puede('respaldo'))      $cuadros[] = ['respaldo.php', 'respaldo', 'mando', 'Respaldo', 'Descargar una copia de todo'];
$cuadros[] = ['manual.php', 'manual', 'ayuda', 'Manual de uso', 'Cómo se hace cada cosa, paso a paso'];
// En la pantalla normal ya está el botón Salir arriba; en la sencilla se
// agrega como cuadro para que no haya que buscarlo.
if (modo_simple()) $cuadros[] = ['salir.php', 'salir', 'salida', 'Salir', 'Cerrar la sesión en este equipo'];

cabecera('Inicio');
?>

<div class="saludo">
  <div>
    <h1><?= h($saludo) ?>, <?= h($nombre) ?></h1>
    <p class="fecha">Hoy es <?= h(fecha_larga($hoy)) ?>.</p>
  </div>
  <?php if ($pendientes > 0): ?>
    <a class="resumen" href="agenda.php?fecha=<?= h($fechaResumen) ?>">
      <span class="numero"><?= $pendientes ?></span>
      <span class="texto"><?= $pendientes === 1 ? 'cita pendiente' : 'citas pendientes' ?><br><?= h($cuando) ?></span>
    </a>
  <?php else: ?>
    <a class="resumen sin-citas" href="agenda.php">
      <span class="texto">No hay citas<br>agendadas por ahora</span>
    </a>
  <?php endif; ?>
</div>

<p class="pregunta">¿Qué quiere hacer?</p>

<div class="cuadros">
  <?php foreach ($cuadros as [$href, $icono, $clase, $rotulo, $pista]): ?>
    <a class="cuadro <?= h($clase) ?>" href="<?= h($href) ?>">
      <?= icono($icono) ?>
      <span class="rotulo"><?= h($rotulo) ?></span>
      <span class="pista"><?= h($pista) ?></span>
    </a>
  <?php endforeach; ?>
</div>

<?php pie(['activo' => 'inicio']); ?>
