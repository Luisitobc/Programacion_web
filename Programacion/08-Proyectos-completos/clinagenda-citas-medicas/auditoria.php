<?php
// Auditoría: quién hizo qué y cuándo. Solo la ven la doctora y la administración.
// Esta pantalla solo lee: no hay forma de cambiar ni borrar una línea.
require __DIR__ . '/lib/auth.php';

exigir('ver_auditoria');

$filtros = ['' => 'Todo', 'citas' => 'Citas', 'pacientes' => 'Pacientes', 'usuarios' => 'Cuentas y entradas',
            'medicos' => 'Médicos', 'feriados' => 'Feriados', 'sistema' => 'Respaldos'];
$entidad = (string)($_GET['ver'] ?? '');
if (!array_key_exists($entidad, $filtros)) $entidad = '';
$lineas  = auditoria_listar(150, $entidad);

/** Muestra el antes y el después de forma legible, sin llaves ni comillas. */
function cambios_legibles(?string $json): string
{
    $datos = $json ? json_decode($json, true) : null;
    if (!is_array($datos)) return '';
    $partes = [];
    foreach ($datos as $k => $v) $partes[] = str_replace('_', ' ', (string)$k) . ': ' . ($v === null || $v === '' ? '—' : (is_scalar($v) ? (string)$v : json_encode($v, JSON_UNESCAPED_UNICODE)));
    return implode(' · ', $partes);
}

cabecera('Auditoría', ['volver' => 'inicio.php']);
?>

<div class="tarjeta">
  <h1>Auditoría</h1>
  <p class="suave">Cada cambio queda anotado con quién lo hizo, desde qué equipo y a qué hora.
     Esta lista no se puede editar ni borrar desde el sistema: solo crece.</p>
  <nav class="fila" aria-label="Filtrar">
    <?php foreach ($filtros as $k => $t): ?>
      <a class="btn chico <?= $entidad === $k ? '' : 'gris' ?>" href="auditoria.php<?= $k ? '?ver=' . $k : '' ?>"><?= h($t) ?></a>
    <?php endforeach; ?>
  </nav>
</div>

<div class="tarjeta">
  <?php if (!$lineas): ?>
    <p class="suave sin-margen">Todavía no hay movimientos de este tipo.</p>
  <?php else: ?>
    <?php foreach ($lineas as $l): ?>
      <div class="linea-rastro">
        <div class="cuando"><?= h(date('d/m/Y', strtotime($l['creado_en']))) ?><br><strong><?= h(date('H:i', strtotime($l['creado_en']))) ?></strong></div>
        <div class="que">
          <strong><?= h($l['usuario_nombre']) ?></strong> <span class="etq agendada"><?= h(str_replace('_', ' ', $l['accion'])) ?></span><br>
          <?= h($l['detalle']) ?>
          <?php if ($l['antes'] || $l['despues']): ?>
            <details>
              <summary>Ver el cambio</summary>
              <?php if ($l['antes']): ?><p><strong>Antes:</strong> <?= h(cambios_legibles($l['antes'])) ?></p><?php endif; ?>
              <?php if ($l['despues']): ?><p><strong>Después:</strong> <?= h(cambios_legibles($l['despues'])) ?></p><?php endif; ?>
            </details>
          <?php endif; ?>
          <?php if ($l['ip']): ?><div class="ayuda-campo">Equipo: <?= h($l['ip']) ?></div><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
    <p class="ayuda-campo">Se muestran los últimos 150 movimientos. El respaldo descargable tiene la lista completa.</p>
  <?php endif; ?>
</div>

<?php pie(['activo' => 'auditoria']); ?>
