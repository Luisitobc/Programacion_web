<?php
// Buscar pacientes.
require __DIR__ . '/lib/auth.php';

exigir('ver_pacientes');

$q         = (string)($_GET['q'] ?? '');
$lista     = pacientes_buscar($q, 60);
$total     = pacientes_contar();
$repetidos = $q === '' ? nombres_repetidos() : [];

cabecera('Pacientes', modo_simple() ? ['volver' => 'inicio.php'] : []);
?>

<div class="tarjeta">
  <h1>Pacientes</h1>
  <form method="get" action="pacientes.php">
    <div class="campo">
      <label for="q">Buscar por nombre, cédula, teléfono o código</label>
      <div class="fila">
        <input class="crece" type="text" id="q" name="q" value="<?= h($q) ?>" placeholder="Ej.: maria lopez" autocomplete="off">
        <button class="btn" type="submit">Buscar</button>
      </div>
      <span class="ayuda-campo">No importan las tildes, las mayúsculas ni el orden de los nombres.</span>
    </div>
  </form>
  <?php if (puede('crear_paciente')): ?>
    <a class="btn sec ancho" href="paciente.php?nuevo=1">+ Registrar un paciente nuevo</a>
  <?php endif; ?>
</div>

<?php if ($repetidos && !modo_simple()): ?>
  <div class="aviso ojo">
    <strong>Hay personas distintas que se llaman igual</strong>
    El sistema no las junta por su cuenta. Se distinguen por el código, la cédula y la fecha de nacimiento.
    <?php foreach ($repetidos as $grupo): ?>
      <div class="grupo"><strong><?= h(nombre_paciente($grupo[0])) ?></strong>
        <?php foreach ($grupo as $p): ?>
          <div><a href="paciente.php?id=<?= (int)$p['id'] ?>"><?= h(codigo_paciente((int)$p['id'])) ?></a>
            · <?= h(documento_paciente($p)) ?> · nació el <?= h($p['fecha_nacimiento'] ? fecha_corta($p['fecha_nacimiento']) : 'sin dato') ?></div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="tarjeta">
  <p class="ayuda-campo"><?= $q === '' ? "Hay $total pacientes registrados." : count($lista) . ' resultado' . (count($lista) === 1 ? '' : 's') . " de $total pacientes." ?></p>

  <?php if (!$lista): ?>
    <div class="vacio">
      <?= icono('pacientes') ?>
      <p><strong>No se encontró a nadie con ese dato.</strong></p>
      <p>Revise cómo está escrito, o registre a la persona.</p>
    </div>
  <?php else: ?>
    <?php foreach ($lista as $p): ?>
      <a class="item" href="paciente.php?id=<?= (int)$p['id'] ?>">
        <span class="av"><?= h(iniciales(nombre_paciente($p))) ?></span>
        <span class="cuerpo">
          <span class="t"><?= h(nombre_paciente($p)) ?> <?= codigo_chip((int)$p['id']) ?></span>
          <span class="s"><?= h(documento_paciente($p)) ?><?= edad_texto($p) ? ' · ' . h(edad_texto($p)) : '' ?>
            · <?= h(contacto_paciente($p) ?: 'sin teléfono') ?></span>
        </span>
      </a>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php pie(['activo' => 'pacientes']); ?>
