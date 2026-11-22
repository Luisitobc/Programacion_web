<?php
// Ficha de un paciente, y el formulario para registrarlo o corregir sus datos.
// Regla de oro: si algo está mal, el formulario vuelve con todo lo que la
// persona ya escribió, el campo marcado en rojo y el mensaje debajo.
require __DIR__ . '/lib/auth.php';

exigir('ver_pacientes');

$id      = (int)($_REQUEST['id'] ?? 0);
$editar  = $id && !empty($_REQUEST['editar']);
$nuevo   = !$id && (!empty($_REQUEST['nuevo']) || $_SERVER['REQUEST_METHOD'] === 'POST');
$volverA = ($_REQUEST['volver'] ?? '') === 'agendar' ? 'agendar' : '';
$errores = [];
$datos   = array_fill_keys(CAMPOS_PACIENTE, '');
$datos['tipo_documento'] = 'CEDULA';
$parecidos = [];

$p = $id ? paciente($id) : null;
if ($id && !$p) {
    avisar('No se encontró ese paciente.', 'mal');
    ir('pacientes.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_csrf();
    exigir($id ? 'editar_paciente' : 'crear_paciente');
    $datos = paciente_desde_formulario($_POST);

    // Antes de crear a alguien, se muestran las personas que ya se llaman
    // igual. Decide la recepcionista: o es una de ellas, o es otra persona.
    if (!$id && empty($_POST['es_otra_persona'])) {
        $parecidos = pacientes_homonimos($datos['nombres'], $datos['apellidos']);
    }
    if (!$parecidos) {
        $r = paciente_guardar($datos, $id);
        if ($r['ok']) {
            avisar('Se guardaron los datos de ' . nombre_paciente($datos) . ' (' . codigo_paciente($r['id']) . ').');
            ir($volverA === 'agendar' ? 'agendar.php?paciente=' . $r['id'] : 'paciente.php?id=' . $r['id']);
        }
        $errores = $r['errores'];
    }
} elseif ($editar) {
    foreach (CAMPOS_PACIENTE as $k) $datos[$k] = (string)$p[$k];
}

$formulario = $nuevo || $editar;
$volverUrl  = $volverA === 'agendar' ? 'agendar.php' : ($id ? 'paciente.php?id=' . $id : 'pacientes.php');
cabecera($formulario ? ($editar ? 'Corregir datos' : 'Registrar paciente') : 'Ficha del paciente',
         ['volver' => $formulario ? $volverUrl : 'pacientes.php']);
?>

<?php if ($formulario): ?>

  <?php if ($parecidos): ?>
    <div class="aviso ojo">
      <strong>Ya hay <?= count($parecidos) === 1 ? 'alguien' : count($parecidos) . ' personas' ?> con ese mismo nombre</strong>
      <?php foreach ($parecidos as $o): ?>
        <div class="grupo"><a href="paciente.php?id=<?= (int)$o['id'] ?>"><?= h(codigo_paciente((int)$o['id'])) ?></a>
          · <?= h(documento_paciente($o)) ?>
          · nació el <?= h($o['fecha_nacimiento'] ? fecha_corta($o['fecha_nacimiento']) : 'sin dato') ?>
          · última cita: <?= h($o['ultima_cita'] ? fecha_corta($o['ultima_cita']) : 'ninguna') ?></div>
      <?php endforeach; ?>
      <p class="sin-margen">Si es <strong>la misma persona</strong>, abra su ficha en lugar de registrarla de nuevo.
        Si es <strong>otra persona</strong>, toque el botón de abajo.</p>
    </div>
  <?php elseif ($errores): ?>
    <p class="aviso mal" role="alert"><strong>Revise lo que está marcado en rojo.</strong><br>
      No se borró nada de lo que ya escribió.</p>
  <?php endif; ?>

  <?php /* novalidate: los avisos del navegador salen a veces en inglés y no
           dicen cómo corregir. Se valida en el servidor, con nuestros mensajes. */ ?>
  <form class="tarjeta" method="post" action="paciente.php" novalidate>
    <?= campo_csrf() ?>
    <?php if ($id): ?><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="editar" value="1"><?php endif; ?>
    <?php if ($volverA): ?><input type="hidden" name="volver" value="agendar"><?php endif; ?>
    <?php if ($parecidos): ?><input type="hidden" name="es_otra_persona" value="1"><?php endif; ?>
    <?php if ($id): ?><p class="suave">Código <?= codigo_chip($id) ?></p><?php endif; ?>

    <div class="campo">
      <label for="tipo_documento">Tipo de documento</label>
      <select id="tipo_documento" name="tipo_documento"
              onchange="document.getElementById('bloque-doc').hidden = (this.value === 'SIN_DOCUMENTO')">
        <?php foreach (['CEDULA' => 'Cédula', 'PASAPORTE' => 'Pasaporte', 'SIN_DOCUMENTO' => 'No tiene documento'] as $v => $t): ?>
          <option value="<?= $v ?>" <?= $datos['tipo_documento'] === $v ? 'selected' : '' ?>><?= $t ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div id="bloque-doc" <?= $datos['tipo_documento'] === 'SIN_DOCUMENTO' ? 'hidden' : '' ?>>
      <?php campo('documento', 'Número de documento', $datos, $errores,
                  ['ayuda' => 'La cédula se revisa con su dígito verificador.', 'extra' => 'maxlength="20" autocomplete="off"']); ?>
    </div>

    <?php campo('nombres', 'Nombres', $datos, $errores, ['extra' => 'maxlength="120" autocomplete="off"']); ?>
    <?php campo('apellidos', 'Apellidos', $datos, $errores, ['extra' => 'maxlength="120" autocomplete="off"']); ?>
    <?php campo('fecha_nacimiento', 'Fecha de nacimiento', $datos, $errores,
                ['tipo' => 'date', 'extra' => 'min="1900-01-01" max="' . date('Y-m-d') . '"',
                 'ayuda' => 'Obligatoria si la persona no tiene documento.']); ?>
    <?php campo('telefono', 'Teléfono', $datos, $errores,
                ['tipo' => 'tel', 'opcional' => true, 'ayuda' => 'Celular 09… (10 números) o fijo 02… (9 números).', 'extra' => 'maxlength="20" inputmode="tel"']); ?>
    <?php campo('correo', 'Correo', $datos, $errores,
                ['tipo' => 'email', 'opcional' => true, 'extra' => 'maxlength="120" inputmode="email"']); ?>

    <fieldset>
      <legend>Para niños, adultos mayores o quien no tiene teléfono</legend>
      <?php campo('representante', '¿Quién responde por el paciente?', $datos, $errores,
                  ['opcional' => true, 'extra' => 'maxlength="160" placeholder="Ej.: Madre: Diana Quishpe"']); ?>
      <?php campo('representante_telefono', 'Teléfono de esa persona', $datos, $errores,
                  ['tipo' => 'tel', 'opcional' => true, 'extra' => 'maxlength="20" inputmode="tel"']); ?>
    </fieldset>

    <?php campo('nota', 'Nota para recepción', $datos, $errores,
                ['opcional' => true, 'ayuda' => 'Algo práctico, como "llamar a la hija". No anote datos de salud aquí.', 'extra' => 'maxlength="255"']); ?>

    <button class="btn ancho" type="submit" data-una-vez><?= $parecidos ? 'Es otra persona: registrarla' : 'Guardar' ?></button>
    <a class="btn gris ancho separado" href="<?= h($volverUrl) ?>">Cancelar</a>
  </form>

<?php else: ?>

  <?php $citas = citas_de_paciente($id); ?>
  <div class="tarjeta">
    <h1 class="sin-margen"><?= h(nombre_paciente($p)) ?></h1>
    <p class="suave"><?= codigo_chip($id) ?> · <?= h(documento_paciente($p)) ?><?= edad_texto($p) ? ' · ' . h(edad_texto($p)) : '' ?></p>

    <table class="ficha">
      <tr><th>Teléfono</th><td><?= h($p['telefono'] ?: '—') ?></td></tr>
      <tr><th>Correo</th><td><?= h($p['correo'] ?: '—') ?></td></tr>
      <tr><th>Nacimiento</th><td><?= h($p['fecha_nacimiento'] ? fecha_corta($p['fecha_nacimiento']) : 'sin dato') ?></td></tr>
      <?php if ($p['representante'] || $p['representante_telefono']): ?>
        <tr><th>Responde por él o ella</th><td><?= h(trim($p['representante'] . ' ' . $p['representante_telefono'])) ?></td></tr>
      <?php endif; ?>
      <?php if ($p['nota']): ?><tr><th>Nota</th><td><?= h($p['nota']) ?></td></tr><?php endif; ?>
    </table>

    <div class="fila separado">
      <?php if (puede('agendar')): ?><a class="btn" href="agendar.php?paciente=<?= $id ?>">Agendar una cita</a><?php endif; ?>
      <?php if (puede('editar_paciente')): ?><a class="btn sec" href="paciente.php?id=<?= $id ?>&amp;editar=1">Corregir datos</a><?php endif; ?>
    </div>
  </div>

  <div class="tarjeta">
    <h2>Citas (<?= count($citas) ?>)</h2>
    <?php if (!$citas): ?>
      <p class="suave sin-margen">Todavía no tiene citas.</p>
    <?php else: ?>
      <?php foreach ($citas as $c): ?>
        <div class="linea-cita">
          <strong><?= h(fecha_larga($c['fecha'])) ?> de <?= date('Y', strtotime($c['fecha'])) ?> · <?= h($c['hora']) ?></strong>
          <?= etiqueta_estado($c['estado']) ?>
          <div class="ayuda-campo"><?= h($c['medico_nombre']) ?> · <?= h($c['clinica_nombre']) ?><?php
            if ($c['motivo'] && puede('ver_motivo')) echo ' · ' . h($c['motivo']);
            if ($c['estado'] === 'CANCELADA') echo ' · ' . h((string)$c['motivo_cancelacion']); ?></div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

<?php endif; ?>

<?php pie(['activo' => 'pacientes']); ?>
