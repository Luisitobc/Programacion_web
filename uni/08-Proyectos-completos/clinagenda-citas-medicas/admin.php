<?php
// Administración: cuentas, médicos y feriados. Todo lo que se cambia aquí
// son datos, no código: si la clienta cambia de idea, se resuelve desde esta
// pantalla sin tocar el programa.
require __DIR__ . '/lib/auth.php';

$seccion = in_array($_GET['s'] ?? '', ['usuarios', 'medicos', 'feriados'], true) ? $_GET['s'] : 'usuarios';
exigir($seccion === 'usuarios' ? 'gestionar_usuarios' : 'gestionar_catalogos');

$errores = [];
$form = ['nombre' => '', 'usuario' => '', 'clave' => '', 'rol' => 'recepcion', 'clinica_id' => '', 'modo_simple' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_csrf();
    $id = (int)($_POST['id'] ?? 0);

    switch ((string)($_POST['accion'] ?? '')) {
        case 'crear_usuario':
            foreach (array_keys($form) as $k) $form[$k] = (string)($_POST[$k] ?? '');
            $r = usuario_crear($form);
            if ($r['ok']) { avisar('Cuenta creada para ' . limpiar_texto($form['nombre']) . '.'); ir('admin.php?s=usuarios'); }
            $errores = $r['errores'];
            break;
        case 'modo_simple':
        case 'activo':
            usuario_alternar($id, $_POST['accion']);
            ir('admin.php?s=usuarios');
        case 'clave':
            $r = usuario_cambiar_clave($id, (string)($_POST['clave'] ?? ''));
            avisar($r['ok'] ? 'Contraseña cambiada. Désela a la persona en privado.' : $r['error'], $r['ok'] ? 'bien' : 'mal');
            ir('admin.php?s=usuarios');
        case 'duracion':
            $r = medico_cambiar_duracion($id, (int)($_POST['minutos'] ?? 0));
            avisar($r['ok'] ? 'Duración guardada. Vale para las citas nuevas; las que ya estaban no cambian.' : $r['error'], $r['ok'] ? 'bien' : 'mal');
            ir('admin.php?s=medicos');
        case 'medico_activo':
            medico_alternar_activo($id);
            avisar('Listo, se cambió la disponibilidad del médico.');
            ir('admin.php?s=medicos');
        case 'crear_feriado':
            $r = feriado_agregar((string)($_POST['fecha'] ?? ''), (int)($_POST['clinica_id'] ?? 0), (string)($_POST['descripcion'] ?? ''));
            avisar($r['ok'] ? 'Feriado agregado. Ese día no se podrán dar citas.' : $r['error'], $r['ok'] ? 'bien' : 'mal');
            ir('admin.php?s=feriados');
        case 'quitar_feriado':
            feriado_quitar($id);
            avisar('Ese día vuelve a ser laborable.');
            ir('admin.php?s=feriados');
    }
}

cabecera('Administración', ['volver' => 'inicio.php']);
?>

<nav class="fila pestanas" aria-label="Secciones de administración">
  <?php foreach (['usuarios' => 'Cuentas', 'medicos' => 'Médicos', 'feriados' => 'Feriados'] as $k => $t): ?>
    <a class="btn chico <?= $seccion === $k ? '' : 'gris' ?>" href="admin.php?s=<?= $k ?>"><?= $t ?></a>
  <?php endforeach; ?>
  <a class="btn chico gris" href="auditoria.php">Auditoría</a>
</nav>

<?php if ($seccion === 'usuarios'): ?>

  <div class="tarjeta">
    <h1>Quién puede entrar</h1>
    <p class="suave"><strong>Recepción</strong> trabaja en su clínica: agenda, cancela y registra pacientes.
      La <strong>doctora</strong> ve además las tres clínicas, el motivo de cada consulta y la auditoría.
      <strong>Administración</strong> ve todo y maneja las cuentas.</p>

    <?php foreach (usuarios_todos() as $x): ?>
      <div class="fila-cuenta <?= $x['activo'] ? '' : 'inactiva' ?>">
        <div class="crece">
          <strong><?= h($x['nombre']) ?></strong>
          <span class="etq <?= $x['activo'] ? 'atendida' : 'cancelada' ?>"><?= $x['activo'] ? NOMBRE_ROL[$x['rol']] : 'Desactivada' ?></span><br>
          <span class="ayuda-campo"><?= h($x['usuario']) ?><?= $x['clinica_nombre'] ? ' · ' . h($x['clinica_nombre']) : '' ?>
            · pantalla <?= $x['modo_simple'] ? 'sencilla' : 'normal' ?></span>
        </div>
        <div class="fila">
          <?= boton_post('admin.php', ['id' => $x['id'], 'accion' => 'modo_simple'], $x['modo_simple'] ? 'Pasar a normal' : 'Pasar a sencilla') ?>
          <?php if ((int)$x['id'] !== uid()): ?>
            <?= boton_post('admin.php', ['id' => $x['id'], 'accion' => 'activo'], $x['activo'] ? 'Desactivar' : 'Activar',
                           $x['activo'] ? 'btn peligro chico' : 'btn sec chico',
                           $x['activo'] ? '¿Desactivar la cuenta de ' . $x['nombre'] . '? No podrá entrar hasta que la active de nuevo.' : '') ?>
          <?php endif; ?>
          <details class="cambiar-clave">
            <summary class="btn gris chico">Nueva contraseña</summary>
            <form method="post" action="admin.php" class="fila">
              <?= campo_csrf() ?>
              <input type="hidden" name="id" value="<?= (int)$x['id'] ?>">
              <input type="hidden" name="accion" value="clave">
              <input class="corto" type="text" name="clave" minlength="8" autocomplete="new-password" aria-label="Contraseña nueva" placeholder="Mínimo 8 caracteres">
              <button class="btn sec chico" type="submit">Guardar</button>
            </form>
          </details>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="aviso info separado">
      <strong>Pantalla sencilla</strong>
      Es la misma aplicación con letra y botones más grandes. Se activa por cuenta, no por el
      nombre ni la edad de nadie: sirve para quien recién empieza, para quien ve poco o para
      quien simplemente la prefiere.
    </div>
  </div>

  <form class="tarjeta" method="post" action="admin.php?s=usuarios" novalidate>
    <?= campo_csrf() ?>
    <input type="hidden" name="accion" value="crear_usuario">
    <h2>Crear una cuenta</h2>
    <?php if ($errores): ?><p class="aviso mal" role="alert">Revise lo que está marcado en rojo. No se borró nada de lo que escribió.</p><?php endif; ?>
    <?php campo('nombre', 'Nombre completo', $form, $errores, ['extra' => 'maxlength="120"']); ?>
    <?php campo('usuario', 'Usuario para entrar', $form, $errores,
                ['ayuda' => 'Letras y números, sin espacios. Ejemplo: lucia', 'extra' => 'maxlength="20" autocapitalize="none" spellcheck="false"']); ?>
    <?php campo('clave', 'Contraseña', $form, $errores,
                ['ayuda' => 'Mínimo 8 caracteres. Se guarda cifrada: nadie podrá volver a verla.', 'extra' => 'autocomplete="new-password"']); ?>

    <div class="campo <?= isset($errores['rol']) ? 'malo' : '' ?>">
      <label for="rol">Rol</label>
      <select id="rol" name="rol">
        <?php foreach (NOMBRE_ROL as $v => $t): ?>
          <option value="<?= $v ?>" <?= $form['rol'] === $v ? 'selected' : '' ?>><?= $t ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="campo <?= isset($errores['clinica_id']) ? 'malo' : '' ?>">
      <label for="clinica_id">Clínica <span class="opt">(solo para recepción)</span></label>
      <select id="clinica_id" name="clinica_id">
        <option value="">Ninguna</option>
        <?php foreach (clinicas_todas() as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= $form['clinica_id'] === (string)$c['id'] ? 'selected' : '' ?>><?= h($c['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
      <?php if (isset($errores['clinica_id'])): ?><span class="error-campo"><?= h($errores['clinica_id']) ?></span><?php endif; ?>
    </div>

    <label class="marcar campo">
      <input type="checkbox" name="modo_simple" value="1" <?= $form['modo_simple'] ? 'checked' : '' ?>>
      Empezar con la pantalla sencilla
    </label>

    <button class="btn ancho" type="submit">Crear la cuenta</button>
  </form>

<?php elseif ($seccion === 'medicos'): ?>

  <div class="tarjeta">
    <h1>Médicos</h1>
    <p class="suave">La duración de la cita es de cada médico: pediatría puede necesitar 20 minutos y
      ginecología 40. Si se cambia, las horas que ofrece la agenda cambian para las citas nuevas.</p>

    <?php foreach (medicos_todos() as $m): ?>
      <div class="fila-cuenta <?= $m['activo'] ? '' : 'inactiva' ?>">
        <div class="crece">
          <strong><?= h($m['nombres']) ?></strong><br>
          <span class="ayuda-campo"><?= h((string)$m['especialidad']) ?> · <?= h((string)$m['clinicas']) ?><?= $m['activo'] ? '' : ' · no recibe citas' ?></span>
        </div>
        <div class="fila">
          <form method="post" action="admin.php" class="fila">
            <?= campo_csrf() ?>
            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
            <input type="hidden" name="accion" value="duracion">
            <input class="minutos" type="number" name="minutos" value="<?= (int)$m['duracion_cita_min'] ?>"
                   min="10" max="120" step="5" aria-label="Minutos por cita de <?= h($m['nombres']) ?>">
            <button class="btn sec chico" type="submit">Guardar</button>
          </form>
          <?= boton_post('admin.php', ['id' => $m['id'], 'accion' => 'medico_activo'], $m['activo'] ? 'Pausar' : 'Activar') ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

<?php else: ?>

  <div class="tarjeta">
    <h1>Feriados</h1>
    <p class="suave">Un día marcado como feriado no acepta citas nuevas en esa clínica.</p>
    <?php $feriados = feriados_activos(); ?>
    <?php if (!$feriados): ?><p class="suave">No hay feriados cargados.</p><?php endif; ?>
    <?php foreach ($feriados as $f): ?>
      <div class="fila-cuenta">
        <div class="crece"><strong><?= h(fecha_corta($f['fecha'])) ?></strong> · <?= h($f['descripcion']) ?><br>
          <span class="ayuda-campo"><?= $f['clinica_nombre'] ? h($f['clinica_nombre']) : 'Las tres clínicas' ?></span></div>
        <?= boton_post('admin.php', ['id' => $f['id'], 'accion' => 'quitar_feriado'], 'Quitar', 'btn peligro chico') ?>
      </div>
    <?php endforeach; ?>
  </div>

  <form class="tarjeta" method="post" action="admin.php?s=feriados" novalidate>
    <?= campo_csrf() ?>
    <input type="hidden" name="accion" value="crear_feriado">
    <h2>Agregar un feriado</h2>
    <?php campo('fecha', 'Fecha', [], [], ['tipo' => 'date']); ?>
    <?php campo('descripcion', 'De qué es', [], [], ['extra' => 'maxlength="120" placeholder="Ej.: fiestas de la ciudad"']); ?>
    <div class="campo">
      <label for="clinica_f">Clínica</label>
      <select id="clinica_f" name="clinica_id">
        <option value="">Las tres</option>
        <?php foreach (clinicas_todas() as $c): ?><option value="<?= (int)$c['id'] ?>"><?= h($c['nombre']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <button class="btn ancho" type="submit">Agregar</button>
  </form>

<?php endif; ?>

<?php pie(['activo' => 'admin']); ?>
