<?php
// Agendar una cita en tres pasos, en una sola pantalla. También sirve para
// cambiar la hora de una cita que ya existe (?reagendar=id).
//
// Todo lo elegido viaja en campos ocultos del formulario: funciona aunque
// el navegador no tenga JavaScript y nada se pierde si algo sale mal.
require __DIR__ . '/lib/auth.php';

exigir('agendar');

$esPost = $_SERVER['REQUEST_METHOD'] === 'POST';
if ($esPost) exigir_csrf();
$f      = $esPost ? $_POST : $_GET;
$accion = (string)($_POST['accion'] ?? '');
$error  = null;

/* ---------- ¿Es una cita nueva o se está moviendo una? ---------- */
$reagendarId = (int)($f['reagendar'] ?? 0);
$vieja = $reagendarId ? cita($reagendarId) : null;
if ($reagendarId && (!$vieja || $vieja['estado'] !== 'AGENDADA' || !clinica_permitida((int)$vieja['clinica_id']))) {
    avisar('Esa cita ya no está agendada, así que no se puede cambiar de hora.', 'mal');
    ir('agenda.php');
}

$clinId = (int)($f['clinica'] ?? $vieja['clinica_id'] ?? 0);
if (!$clinId || !clinica_permitida($clinId)) $clinId = clinica_actual();
$pacId  = $vieja ? (int)$vieja['paciente_id'] : (int)($f['paciente'] ?? 0);
$medId  = (int)($f['medico'] ?? $vieja['medico_id'] ?? 0);
$fecha  = (string)($f['fecha'] ?? $vieja['fecha'] ?? '');
$hora   = (string)($f['hora'] ?? '');
$motivo = (string)($f['motivo'] ?? ($vieja && puede('ver_motivo') ? $vieja['motivo'] : ''));
$q      = (string)($f['q'] ?? '');

if (!fecha_valida($fecha) || $fecha < date('Y-m-d')) $fecha = primer_dia_agendable($clinId);
if (in_array($accion, ['cambiar_clinica', 'cambiar_medico', 'cambiar_fecha'], true)) $hora = '';
if ($accion === 'cambiar_clinica') $medId = 0;
if ($accion === 'quitar_paciente' && !$vieja) { $pacId = 0; $q = ''; }
if ($medId && !medico_atiende_en($medId, $clinId)) { $medId = 0; $hora = ''; }

/* ---------- Saltar a la próxima hora libre ---------- */
if ($accion === 'buscar_libre' && $medId) {
    $libre = proxima_hora_libre($clinId, $medId, $fecha);
    if ($libre) {
        [$fecha, $hora] = [$libre['fecha'], $libre['hora']];
        avisar('La próxima hora libre es el ' . fecha_larga($fecha) . ' a las ' . $hora . '. Ya quedó elegida.', 'info');
    } else {
        $error = 'No hay horas libres con ese médico en el próximo mes.';
    }
}

/* ---------- Guardar ---------- */
if ($accion === 'guardar') {
    $datos = ['clinica_id' => $clinId, 'medico_id' => $medId, 'paciente_id' => $pacId,
              'fecha' => $fecha, 'hora' => $hora, 'motivo' => $motivo, 'token' => (string)($_POST['token'] ?? '')];
    $r = $vieja ? cita_reagendar($reagendarId, $datos) : cita_crear($datos);
    $p = paciente($pacId);
    $hecho = $p ? nombre_paciente($p) . ', ' . fecha_larga($fecha) . ' a las ' . $hora : '';

    // Las citas que quedaron guardadas sin conexión llegan aquí desde app.js.
    // El aviso también queda para la próxima pantalla que se abra.
    if (es_envio_de_cola()) {
        $mensaje = $r['ok'] ? "Se envió la cita que estaba guardada en el equipo: $hecho."
                            : "No se pudo guardar la cita de $hecho que estaba en el equipo. " . $r['error'];
        avisar($mensaje, $r['ok'] ? 'bien' : 'mal');
        responder_json(['ok' => $r['ok'], 'mensaje' => $mensaje]);
    }
    if ($r['ok']) {
        avisar(!empty($r['repetida']) ? 'Esa cita ya estaba guardada: no se duplicó nada.'
            : ($vieja ? "Listo, la cita se cambió: $hecho." : "Cita guardada: $hecho."));
        ir('agenda.php?fecha=' . urlencode($fecha) . '&clinica=' . $clinId);
    }
    $error = $r['error'];
    $hora  = '';
}

$paciente = $pacId ? paciente($pacId) : null;
$med      = $medId ? medico($medId) : null;
$horas    = $med ? horas_del_dia($clinId, $medId, $fecha) : [];
$hayLibre = (bool)array_filter($horas, fn($x) => !$x['ocupada'] && !$x['pasada']);
$listo    = $paciente && $med && $hora;
$estado   = ['clinica' => $clinId, 'paciente' => $pacId, 'medico' => $medId, 'fecha' => $fecha,
             'hora' => $hora, 'motivo' => $motivo, 'q' => $q, 'reagendar' => $reagendarId ?: ''];

/** Repite lo elegido hasta ahora como campos ocultos, con la ficha CSRF. */
function ocultos(array $v): string
{
    $s = campo_csrf();
    foreach ($v as $k => $x) $s .= '<input type="hidden" name="' . h((string)$k) . '" value="' . h((string)$x) . '">';
    return $s;
}

cabecera($vieja ? 'Cambiar la hora de una cita' : 'Agendar una cita', ['volver' => modo_simple() || $vieja ? 'agenda.php' : null]);
?>

<?php if ($error): ?>
  <div class="aviso mal" role="alert"><strong>No se pudo guardar la cita.</strong><br><?= h($error) ?><br>
    <small>Lo que ya eligió sigue aquí: solo escoja otra hora y vuelva a guardar.</small></div>
<?php endif; ?>

<?php if ($vieja): ?>
  <p class="aviso info">Está cambiando la cita del <strong><?= h(fecha_larga($vieja['fecha'])) ?> a las <?= h($vieja['hora']) ?></strong>
    con <?= h($vieja['medico_nombre']) ?>. Cuando guarde la nueva hora, la anterior queda libre.</p>
<?php endif; ?>

<!-- Paso 1: el paciente -->
<div class="tarjeta">
  <p class="paso">Paso 1 de 3</p>
  <h2>¿Para quién es la cita?</h2>

  <?php if ($paciente): ?>
    <div class="item elegido">
      <span class="av"><?= h(iniciales(nombre_paciente($paciente))) ?></span>
      <span class="cuerpo">
        <span class="t"><?= h(nombre_paciente($paciente)) ?> <?= codigo_chip($pacId) ?></span>
        <span class="s"><?= h(documento_paciente($paciente)) ?> · <?= h(contacto_paciente($paciente) ?: 'sin teléfono') ?></span>
      </span>
    </div>
    <?php if (!$vieja): ?>
      <form method="post" action="agendar.php">
        <?= ocultos($estado) ?>
        <button class="btn gris chico" type="submit" name="accion" value="quitar_paciente">Elegir a otra persona</button>
      </form>
    <?php endif; ?>

  <?php else: ?>
    <form method="post" action="agendar.php">
      <?= ocultos(array_merge($estado, ['q' => ''])) ?>
      <div class="campo">
        <label for="q">Busque por nombre, cédula, teléfono o código</label>
        <div class="fila">
          <input class="crece" type="text" id="q" name="q" value="<?= h($q) ?>" placeholder="Ej.: juan perez" autocomplete="off">
          <button class="btn" type="submit" name="accion" value="buscar">Buscar</button>
        </div>
        <span class="ayuda-campo">No importan las tildes, las mayúsculas ni el orden de los nombres.</span>
      </div>
    </form>

    <?php $resultados = pacientes_buscar($q, 8); ?>
    <?php if (!$resultados): ?>
      <div class="vacio compacto">
        <p><strong>No se encontró a nadie con ese dato.</strong></p>
        <p>Revise cómo está escrito, o registre a la persona.</p>
      </div>
    <?php else: ?>
      <?php foreach ($resultados as $p): ?>
        <form method="post" action="agendar.php">
          <?= ocultos(array_merge($estado, ['paciente' => (int)$p['id']])) ?>
          <button class="item" type="submit" name="accion" value="elegir_paciente">
            <span class="av"><?= h(iniciales(nombre_paciente($p))) ?></span>
            <span class="cuerpo">
              <span class="t"><?= h(nombre_paciente($p)) ?> <?= codigo_chip((int)$p['id']) ?></span>
              <span class="s"><?= h(documento_paciente($p)) ?><?= edad_texto($p) ? ' · ' . h(edad_texto($p)) : '' ?>
                · <?= h(contacto_paciente($p) ?: 'sin teléfono') ?></span>
            </span>
          </button>
        </form>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if (puede('crear_paciente')): ?>
      <a class="btn sec ancho" href="paciente.php?nuevo=1&amp;volver=agendar">+ Registrar un paciente nuevo</a>
    <?php endif; ?>
  <?php endif; ?>
</div>

<!-- Paso 2: con quién y cuándo -->
<div class="tarjeta">
  <p class="paso">Paso 2 de 3</p>
  <h2>¿Con quién y cuándo?</h2>

  <form method="post" action="agendar.php">
    <?= ocultos(array_diff_key($estado, ['clinica' => 1, 'medico' => 1, 'fecha' => 1])) ?>
    <input type="hidden" name="accion" value="cambiar_medico">

    <?php if (count(clinicas_visibles()) > 1): ?>
      <div class="campo">
        <label for="clinica">Clínica</label>
        <select id="clinica" name="clinica" onchange="this.form.accion.value='cambiar_clinica'; this.form.submit()">
          <?php foreach (clinicas_visibles() as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (int)$c['id'] === $clinId ? 'selected' : '' ?>><?= h($c['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php else: ?>
      <input type="hidden" name="clinica" value="<?= $clinId ?>">
    <?php endif; ?>

    <div class="campo">
      <label for="medico">Médico</label>
      <select id="medico" name="medico" onchange="this.form.accion.value='cambiar_medico'; this.form.submit()">
        <option value="">Elija el médico</option>
        <?php foreach (medicos_de_clinica($clinId) as $m): ?>
          <option value="<?= (int)$m['id'] ?>" <?= (int)$m['id'] === $medId ? 'selected' : '' ?>>
            <?= h($m['nombres']) ?> · <?= h((string)$m['especialidad']) ?> (<?= (int)$m['duracion_cita_min'] ?> min)</option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo">
      <label for="fecha">Fecha</label>
      <input type="date" id="fecha" name="fecha" value="<?= h($fecha) ?>" min="<?= h(date('Y-m-d')) ?>"
             onchange="this.form.accion.value='cambiar_fecha'; this.form.submit()">
    </div>

    <noscript><button class="btn" type="submit">Ver las horas de ese día</button></noscript>
  </form>
</div>

<!-- Paso 3: la hora -->
<div class="tarjeta">
  <p class="paso">Paso 3 de 3</p>
  <h2>Horas libres</h2>

  <?php if (!$med): ?>
    <p class="suave">Primero elija el médico.</p>

  <?php elseif (!$horas): ?>
    <?php $fer = feriado_en($clinId, $fecha); ?>
    <p class="aviso ojo"><strong>Ese día no hay horas.</strong><br>
      <?= $fer ? 'Es feriado: ' . h($fer['descripcion']) . '.'
               : h($med['nombres']) . ' no atiende en ' . h(clinica($clinId)['nombre']) . ' los ' . h(nombre_dia($fecha)) . '.' ?></p>
    <form method="post" action="agendar.php"><?= ocultos($estado) ?>
      <button class="btn sec" type="submit" name="accion" value="buscar_libre">Buscar la próxima hora libre</button></form>

  <?php else: ?>
    <?php if (!$hayLibre): ?>
      <p class="aviso ojo"><strong>Ese día ya no quedan horas.</strong><br>
        <?= array_filter($horas, fn($x) => !$x['pasada']) ? 'Están todas ocupadas.' : 'Las horas de ese día ya pasaron.' ?></p>
      <form method="post" action="agendar.php"><?= ocultos($estado) ?>
        <button class="btn sec" type="submit" name="accion" value="buscar_libre">Buscar la próxima hora libre</button></form>
    <?php endif; ?>

    <form method="post" action="agendar.php">
      <?= ocultos(array_diff_key($estado, ['hora' => 1])) ?>
      <input type="hidden" name="accion" value="elegir_hora">
      <div class="horas">
        <?php foreach ($horas as $x): ?>
          <?php if ($x['ocupada']): ?>
            <button class="hora-btn ocupada" type="button" disabled><?= h($x['hora']) ?>
              <small><?= $x['otra_clinica'] ? 'en ' . h(str_replace('Clínica ', '', $x['otra_clinica'])) : 'ocupado' ?></small></button>
          <?php elseif ($x['pasada']): ?>
            <button class="hora-btn ocupada" type="button" disabled><?= h($x['hora']) ?><small>ya pasó</small></button>
          <?php else: ?>
            <button class="hora-btn" type="submit" name="hora" value="<?= h($x['hora']) ?>"
                    aria-pressed="<?= $hora === $x['hora'] ? 'true' : 'false' ?>"><?= h($x['hora']) ?></button>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </form>
  <?php endif; ?>
</div>

<!-- Confirmar -->
<div class="tarjeta">
  <?php if ($listo): ?>
    <p class="aviso info"><strong>Va a guardar esta cita</strong><br>
      <?= h(nombre_paciente($paciente)) ?> con <?= h($med['nombres']) ?><br>
      <?= h(fecha_larga($fecha)) ?> a las <?= h($hora) ?> · <?= h(clinica($clinId)['nombre']) ?></p>

    <form method="post" action="agendar.php" data-cola>
      <?= ocultos(array_diff_key($estado, ['motivo' => 1])) ?>
      <input type="hidden" name="accion" value="guardar">
      <!-- El token hace que un doble clic, o un reintento sin conexión, no cree la cita dos veces. -->
      <input type="hidden" name="token" value="<?= h(bin2hex(random_bytes(16))) ?>">
      <?php campo('motivo', 'Motivo de la consulta', ['motivo' => $motivo], [], [
          'opcional' => true, 'extra' => 'maxlength="160" placeholder="Ej.: control de presión"',
          'ayuda' => puede('ver_motivo') ? '' : 'Un renglón. Después solo lo ve la doctora.']); ?>
      <button class="btn ancho" type="submit" data-una-vez><?= $vieja ? 'Guardar la nueva hora' : 'Guardar la cita' ?></button>
    </form>
  <?php else: ?>
    <p class="suave sin-margen">Complete los tres pasos para poder guardar.</p>
  <?php endif; ?>
</div>

<?php pie(['activo' => 'agendar']); ?>
