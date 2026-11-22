<?php
// Agenda del día: ver las citas, cancelar con motivo, deshacer, marcar
// atendida o no asistió, e imprimir la hoja del día para el plan en papel.
require __DIR__ . '/lib/auth.php';

exigir('ver_agenda');

$errorCancelar = null;
$motivoEscrito = '';

/* ---------- Acciones sobre una cita ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_csrf();
    $id     = (int)($_POST['id'] ?? 0);
    $accion = (string)($_POST['accion'] ?? '');
    $c      = cita($id);
    if (!$c || !clinica_permitida((int)$c['clinica_id'])) {
        avisar('No se encontró esa cita en su clínica.', 'mal');
        ir('agenda.php');
    }
    $volver = 'agenda.php?fecha=' . urlencode($c['fecha']) . '&clinica=' . (int)$c['clinica_id'];

    if ($accion === 'CANCELAR') {
        exigir('cancelar');
        $motivoEscrito = (string)($_POST['motivo'] ?? '');
        $r = cita_cancelar($id, $motivoEscrito);
        if ($r['ok']) {
            avisar($r['ya_estaba'] ? $r['mensaje'] : 'Cita cancelada. La hora quedó libre para otro paciente.', $r['ya_estaba'] ? 'info' : 'bien');
            ir($volver);
        }
        // Se vuelve a mostrar la confirmación con lo que ya escribió.
        $errorCancelar = $r['error'];
        $_GET['cancelar'] = $id;
    } elseif ($accion === 'ATENDIDA' || $accion === 'NO_ASISTIO') {
        exigir('marcar_estado');
        $r = cita_marcar($id, $accion);
        avisar($r['ok'] ? ($accion === 'ATENDIDA' ? 'Listo: la cita quedó como atendida.' : 'Listo: quedó anotado que el paciente no vino.') : $r['error'],
               $r['ok'] ? 'bien' : 'mal');
        ir($volver);
    } elseif ($accion === 'REACTIVAR') {
        exigir('cancelar');
        $r = cita_reactivar($id);
        avisar($r['ok'] ? 'Se deshizo la cancelación: la cita vuelve a estar agendada.' : $r['error'], $r['ok'] ? 'bien' : 'mal');
        ir($volver);
    } else {
        ir($volver);
    }
}

/* ---------- Confirmar una cancelación ---------- */
if (isset($_GET['cancelar'])) {
    exigir('cancelar');
    $c = cita((int)$_GET['cancelar']);
    if (!$c || !clinica_permitida((int)$c['clinica_id'])) {
        avisar('No se encontró esa cita en su clínica.', 'mal');
        ir('agenda.php');
    }
    $volver = 'agenda.php?fecha=' . urlencode($c['fecha']) . '&clinica=' . (int)$c['clinica_id'];
    cabecera('Cancelar una cita', ['volver' => $volver]);
    ?>
    <div class="tarjeta">
      <h1>¿Seguro que quiere cancelar esta cita?</h1>
      <div class="aviso ojo">
        <strong><?= h(nombre_paciente($c)) ?></strong> <?= codigo_chip((int)$c['paciente_id']) ?><br>
        <?= h(fecha_larga($c['fecha'])) ?> a las <?= h($c['hora']) ?><br>
        <?= h($c['medico_nombre']) ?> · <?= h($c['clinica_nombre']) ?><br>
        Teléfono: <?= h(contacto_paciente($c) ?: 'sin teléfono registrado') ?>
      </div>

      <?php if ($c['estado'] !== 'AGENDADA'): ?>
        <p class="aviso info">Esta cita ya está <?= h(mb_strtolower(texto_estado($c['estado']))) ?>. No hace falta hacer nada más.</p>
        <a class="btn gris ancho" href="<?= h($volver) ?>">Volver a la agenda</a>
      <?php else: ?>
        <p>La hora quedará libre para otro paciente. La cita <strong>no se borra</strong>: queda
           guardada con el motivo y con su nombre, para que después se sepa qué pasó.</p>
        <form method="post" action="agenda.php" novalidate>
          <?= campo_csrf() ?>
          <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
          <input type="hidden" name="accion" value="CANCELAR">
          <?php campo('motivo', '¿Por qué se cancela?', ['motivo' => $motivoEscrito],
                      $errorCancelar ? ['motivo' => $errorCancelar] : [],
                      ['ayuda' => 'Por ejemplo: el paciente llamó, no puede ir ese día.', 'extra' => 'maxlength="160" autofocus']); ?>
          <button class="btn peligro ancho" type="submit" data-una-vez>Sí, cancelar la cita</button>
          <a class="btn gris ancho separado" href="<?= h($volver) ?>">No, volver atrás</a>
        </form>
      <?php endif; ?>
    </div>
    <?php
    pie(['sin_nav' => true]);
    exit;
}

/* ---------- La agenda del día ---------- */
$modo    = ($_GET['modo'] ?? '') === 'cancelar' ? 'cancelar' : 'normal';
$clinId  = clinica_actual();
$fecha   = fecha_valida($_GET['fecha'] ?? '') ? $_GET['fecha'] : date('Y-m-d');
$medId   = (int)($_GET['medico'] ?? 0);
$medicos = medicos_de_clinica($clinId);
if ($medId && !in_array($medId, array_map('intval', array_column($medicos, 'id')), true)) $medId = 0;

$ayer    = date('Y-m-d', strtotime("$fecha -1 day"));
$manana  = date('Y-m-d', strtotime("$fecha +1 day"));
$citas   = citas_del_dia($clinId, $fecha, $medId);
$vivas   = array_values(array_filter($citas, fn($c) => $c['estado'] !== 'CANCELADA'));
$canceladas = count($citas) - count($vivas);
$clinica = clinica($clinId);
$feriado = feriado_en($clinId, $fecha);
$filtro  = fn(array $extra) => 'agenda.php?' . http_build_query(array_merge(['fecha' => $fecha, 'clinica' => $clinId, 'medico' => $medId ?: null], $extra));

cabecera($modo === 'cancelar' ? 'Cancelar una cita' : 'Agenda del día', modo_simple() ? ['volver' => 'inicio.php'] : []);
?>

<?php if ($modo === 'cancelar'): ?>

  <div class="tarjeta">
    <h1>¿Cuál cita quiere cancelar?</h1>
    <p class="suave">Citas de <?= h(fecha_larga($fecha)) ?> en <?= h($clinica['nombre']) ?>. Al cancelar,
      la hora queda libre para otro paciente y la cita no se borra: queda anotada con el motivo.</p>
  </div>

  <?php $agendadas = array_filter($citas, fn($c) => $c['estado'] === 'AGENDADA'); ?>
  <?php if (!$agendadas): ?>
    <div class="tarjeta"><div class="vacio">
      <?= icono('agenda') ?>
      <p><strong>Este día no tiene citas para cancelar.</strong></p>
      <a class="btn sec" href="<?= h($filtro(['fecha' => $manana, 'modo' => 'cancelar'])) ?>">Ver el día siguiente</a>
    </div></div>
  <?php else: ?>
    <?php foreach ($agendadas as $c): ?>
      <article class="cita">
        <div class="hora"><?= h($c['hora']) ?></div>
        <div class="cuerpo">
          <p class="nombre"><?= h(nombre_paciente($c)) ?> <?= codigo_chip((int)$c['paciente_id']) ?></p>
          <p class="dato"><?= h($c['medico_nombre']) ?></p>
          <div class="acciones">
            <a class="btn peligro" href="agenda.php?cancelar=<?= (int)$c['id'] ?>">Cancelar esta cita</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>

  <p><a class="btn gris ancho" href="inicio.php">Volver al inicio</a></p>

<?php else: ?>

  <div class="tarjeta no-imprimir">
    <p class="paso">Agenda del día</p>
    <h1 class="sin-margen"><?= h(fecha_larga($fecha)) ?><?= es_hoy($fecha) ? ' (hoy)' : '' ?></h1>
    <p class="suave">
      <?= h($clinica['nombre']) ?> · <?= count($vivas) ?> cita<?= count($vivas) === 1 ? '' : 's' ?>
      <?= $canceladas ? ' · ' . $canceladas . ' cancelada' . ($canceladas === 1 ? '' : 's') : '' ?>
    </p>

    <form class="fila" method="get" action="agenda.php">
      <a class="btn gris chico" href="<?= h($filtro(['fecha' => $ayer])) ?>">◀ Anterior</a>
      <a class="btn gris chico" href="<?= h($filtro(['fecha' => date('Y-m-d')])) ?>">Hoy</a>
      <a class="btn gris chico" href="<?= h($filtro(['fecha' => $manana])) ?>">Siguiente ▶</a>
      <input class="corto" type="date" name="fecha" value="<?= h($fecha) ?>" aria-label="Elegir el día" onchange="this.form.submit()">
      <?php if (count(clinicas_visibles()) > 1): ?>
        <select class="corto" name="clinica" aria-label="Clínica" onchange="this.form.medico.value=''; this.form.submit()">
          <?php foreach (clinicas_visibles() as $cl): ?>
            <option value="<?= (int)$cl['id'] ?>" <?= (int)$cl['id'] === $clinId ? 'selected' : '' ?>><?= h($cl['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      <?php else: ?>
        <input type="hidden" name="clinica" value="<?= $clinId ?>">
      <?php endif; ?>
      <select class="corto" name="medico" aria-label="Médico" onchange="this.form.submit()">
        <option value="">Todos los médicos</option>
        <?php foreach ($medicos as $m): ?>
          <option value="<?= (int)$m['id'] ?>" <?= (int)$m['id'] === $medId ? 'selected' : '' ?>><?= h($m['nombres']) ?></option>
        <?php endforeach; ?>
      </select>
      <noscript><button class="btn chico" type="submit">Ver</button></noscript>
    </form>
  </div>

  <?php if ($feriado): ?>
    <p class="aviso ojo no-imprimir"><strong>Feriado: <?= h($feriado['descripcion']) ?>.</strong> La clínica no atiende este día.</p>
  <?php endif; ?>

  <div class="no-imprimir">
    <?php if (!$citas): ?>
      <div class="tarjeta"><div class="vacio">
        <?= icono('agenda') ?>
        <p><strong><?= es_hoy($fecha) ? 'Hoy no hay citas.' : 'Este día no tiene citas.' ?></strong></p>
        <p>No es un error: la agenda está libre.</p>
        <?php if (puede('agendar') && !$feriado): ?>
          <a class="btn" href="agendar.php?fecha=<?= h($fecha) ?>&amp;clinica=<?= $clinId ?><?= $medId ? '&amp;medico=' . $medId : '' ?>">Agendar una</a>
        <?php endif; ?>
      </div></div>
    <?php else: ?>
      <?php foreach ($citas as $c) pintar_cita($c); ?>
    <?php endif; ?>

    <div class="fila separado">
      <?php if (puede('agendar') && !$feriado): ?>
        <a class="btn crece" href="agendar.php?fecha=<?= h($fecha) ?>&amp;clinica=<?= $clinId ?><?= $medId ? '&amp;medico=' . $medId : '' ?>">
          <?= icono('mas') ?> Agendar una cita</a>
      <?php endif; ?>
      <button class="btn gris crece" type="button" onclick="window.print()">Imprimir la hoja del día</button>
    </div>
  </div>

  <?php /* La hoja en papel: el plan B si el sistema no prende un lunes a las 8.
           Solo aparece al imprimir. */ ?>
  <section class="hoja solo-imprimir">
    <h1>ClinAgenda · <?= h($clinica['nombre']) ?></h1>
    <p><?= h(ucfirst(fecha_larga($fecha))) ?> de <?= date('Y', strtotime($fecha)) ?><?php
      if ($medId) echo ' · ' . h(medico($medId)['nombres']); ?>.
      Impresa el <?= date('d/m/Y \a \l\a\s H:i') ?>.</p>
    <table>
      <thead><tr><th>Hora</th><th>Paciente</th><th>Teléfono</th><th>Médico</th>
        <?php if (puede('ver_motivo')): ?><th>Motivo</th><?php endif; ?><th>¿Llegó?</th></tr></thead>
      <tbody>
        <?php foreach ($vivas as $c): ?>
          <tr><td><?= h($c['hora']) ?></td>
            <td><?= h(nombre_paciente($c)) ?><br><small><?= h(codigo_paciente((int)$c['paciente_id'])) ?></small></td>
            <td><?= h(contacto_paciente($c)) ?></td><td><?= h($c['medico_nombre']) ?></td>
            <?php if (puede('ver_motivo')): ?><td><?= h((string)$c['motivo']) ?></td><?php endif; ?>
            <td></td></tr>
        <?php endforeach; ?>
        <?php for ($i = 0; $i < 6; $i++): ?>
          <tr class="vacia"><td></td><td></td><td></td><td></td><?php if (puede('ver_motivo')): ?><td></td><?php endif; ?><td></td></tr>
        <?php endfor; ?>
      </tbody>
    </table>
    <p><small>Las filas en blanco son para anotar a mano las citas que se den sin sistema.
      Después se pasan con el formulario normal de Agendar.</small></p>
  </section>

<?php endif; ?>

<?php pie(['activo' => 'agenda']); ?>
