<?php
// Cabecera y pie de todas las pantallas, más las piezas que se repiten.
// Si la cuenta tiene la pantalla sencilla, el <body> lleva la clase
// "simple" y todo crece: letra, botones y espacios. Es la misma aplicación,
// no una versión recortada.

declare(strict_types=1);

function cabecera(string $titulo, array $op = []): void
{
    $u      = usuario_actual();
    $volver = $op['volver'] ?? null;
    $sinNav = $op['sin_nav'] ?? false;
    $aviso  = tomar_aviso();
    $lugar  = '';
    if ($u) {
        $lugar = $u['clinica_id'] ? (clinica((int)$u['clinica_id'])['nombre'] ?? '') : 'las tres clínicas';
        $lugar = NOMBRE_ROL[$u['rol']] . ' · ' . $lugar;
    }
    ?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0f6b64">
<title><?= h($titulo) ?> · ClinAgenda</title>
<link rel="icon" href="assets/icono.svg" type="image/svg+xml">
<link rel="manifest" href="assets/manifest.json">
<link rel="stylesheet" href="assets/estilos.css">
<?php if ($u): ?><meta name="csrf" content="<?= h(csrf()) ?>"><?php endif; ?>
</head>
<body class="<?= modo_simple() ? 'simple' : '' ?>">

<div id="sin-conexion" class="banner-red" role="status" hidden></div>

<?php if ($u): ?>
<header class="barra">
  <?php if ($volver): ?>
    <a class="btn-volver" href="<?= h($volver) ?>">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
      <span>Volver</span>
    </a>
  <?php endif; ?>
  <div class="barra-tit">
    <strong><?= h($titulo) ?></strong>
    <small><?= h($u['nombre']) ?> · <?= h($lugar) ?></small>
  </div>
  <a class="btn-salir" href="salir.php">Salir</a>
</header>
<?php endif; ?>

<main class="<?= $sinNav ? 'sin-nav' : '' ?>">
<?php if ($aviso): ?>
  <p class="aviso <?= h($aviso['tipo']) ?>" role="status"><?= h($aviso['texto']) ?></p>
<?php endif; ?>
<?php
}

const ICONOS = [
    'inicio'    => '<path d="M3 11l9-7 9 7"/><path d="M5.5 9.5V20h13V9.5"/>',
    'agenda'    => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
    'mas'       => '<path d="M12 5v14M5 12h14"/>',
    'cancelar'  => '<circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/>',
    'pacientes' => '<circle cx="10" cy="8" r="3.6"/><path d="M3.5 20c0-3.6 2.9-6 6.5-6 1.4 0 2.7.4 3.8 1"/><circle cx="17" cy="17" r="3.4"/><path d="M19.6 19.6L22 22"/>',
    'usuarios'  => '<circle cx="9" cy="8" r="3.4"/><path d="M2.5 20c0-3.4 2.9-5.6 6.5-5.6s6.5 2.2 6.5 5.6"/><path d="M17.5 4.5l1.3 2.7 2.9.4-2.1 2 .5 2.9-2.6-1.4-2.6 1.4.5-2.9-2.1-2 2.9-.4z"/>',
    'medicos'   => '<path d="M8 3v5a4 4 0 008 0V3"/><path d="M12 12v3a4 4 0 004 4 3 3 0 003-3v-2"/><circle cx="19" cy="12" r="2"/>',
    'auditoria' => '<path d="M5 4h14v16H5z"/><path d="M8.5 8.5h7M8.5 12h7M8.5 15.5h4"/>',
    'respaldo'  => '<path d="M12 4v11M7.5 10.5L12 15l4.5-4.5"/><path d="M4 16v3a1 1 0 001 1h14a1 1 0 001-1v-3"/>',
    'manual'    => '<path d="M12 7.2v12.3"/><path d="M12 7.2C10.4 5.6 7.8 5 4.3 5v12.4c3.5 0 6.1.6 7.7 2.1"/><path d="M12 7.2c1.6-1.6 4.2-2.2 7.7-2.2v12.4c-3.5 0-6.1.6-7.7 2.1"/>',
    'salir'     => '<path d="M15 4h3.5A1.5 1.5 0 0120 5.5v13a1.5 1.5 0 01-1.5 1.5H15"/><path d="M10 8l-4 4 4 4M6 12h10"/>',
];

function icono(string $nombre): string
{
    return '<svg viewBox="0 0 24 24" aria-hidden="true">' . (ICONOS[$nombre] ?? '') . '</svg>';
}

function pie(array $op = []): void
{
    $u      = usuario_actual();
    $sinNav = $op['sin_nav'] ?? false;
    $actual = $op['activo'] ?? '';
    $enlaces = modo_simple()
        ? ['inicio' => 'Inicio', 'manual' => 'Manual de uso']
        : ['inicio' => 'Inicio', 'agenda' => 'Agenda', 'agendar' => 'Agendar', 'pacientes' => 'Pacientes', 'manual' => 'Manual'];
    $iconoDe = ['agendar' => 'mas'];
    ?>
</main>

<?php if ($u && !$sinNav): ?>
<nav class="<?= modo_simple() ? 'nav-simple' : 'nav' ?> no-imprimir" aria-label="Secciones">
  <?php foreach ($enlaces as $pagina => $texto): ?>
    <a href="<?= $pagina ?>.php" <?= $actual === $pagina ? 'aria-current="page"' : '' ?>>
      <?= icono($iconoDe[$pagina] ?? $pagina) ?><span><?= h($texto) ?></span></a>
  <?php endforeach; ?>
</nav>
<?php endif; ?>

<script src="assets/app.js"></script>
</body>
</html>
<?php
}

/* ---------- Piezas que se repiten ---------- */

function etiqueta_estado(string $estado): string
{
    $clase = ['AGENDADA' => 'agendada', 'ATENDIDA' => 'atendida', 'CANCELADA' => 'cancelada', 'NO_ASISTIO' => 'noasistio'][$estado] ?? 'agendada';
    return '<span class="etq ' . $clase . '">' . h(texto_estado($estado)) . '</span>';
}

function codigo_chip(int $pacienteId): string
{
    return '<span class="codigo">' . h(codigo_paciente($pacienteId)) . '</span>';
}

/** Un botón que manda un POST con su ficha CSRF. */
function boton_post(string $accion, array $campos, string $texto, string $clase = 'btn gris chico', string $confirmar = ''): string
{
    $s = '<form method="post" action="' . h($accion) . '" class="en-linea"'
       . ($confirmar ? ' data-confirmar="' . h($confirmar) . '"' : '') . '>' . campo_csrf();
    foreach ($campos as $k => $v) $s .= '<input type="hidden" name="' . h($k) . '" value="' . h((string)$v) . '">';
    return $s . '<button class="' . h($clase) . '" type="submit">' . h($texto) . '</button></form>';
}

/** Tarjeta de una cita, la misma en la agenda y en la ficha del paciente. */
function pintar_cita(array $c, bool $conAcciones = true): void
{
    $edad = edad_texto($c);
    $agendada = $c['estado'] === 'AGENDADA';
    ?>
    <article class="cita est-<?= h($c['estado']) ?>">
      <div class="hora"><?= h($c['hora']) ?></div>
      <div class="cuerpo">
        <p class="nombre"><?= h(nombre_paciente($c)) ?> <?= codigo_chip((int)$c['paciente_id']) ?>
          <?php if ($edad): ?><span class="dato">(<?= h($edad) ?>)</span><?php endif; ?></p>
        <p class="dato"><?= h($c['medico_nombre']) ?> · <?= h((string)$c['especialidad']) ?></p>
        <p class="dato"><?= h(contacto_paciente($c) ?: 'sin teléfono') ?> · <?= h(documento_paciente($c)) ?></p>
        <?php if ($c['motivo'] && puede('ver_motivo')): ?>
          <p class="dato">Motivo: <?= h($c['motivo']) ?></p>
        <?php endif; ?>
        <?php if ($c['estado'] === 'CANCELADA'): ?>
          <p class="dato aviso-linea">Cancelada por <?= h($c['cancelada_por_nombre'] ?? '—') ?>:
            <?= h((string)$c['motivo_cancelacion']) ?></p>
        <?php endif; ?>
        <p><?= etiqueta_estado($c['estado']) ?></p>

        <?php if ($conAcciones): ?>
        <div class="acciones">
          <?php if ($agendada && $c['fecha'] <= date('Y-m-d') && puede('marcar_estado')): ?>
            <?= boton_post('agenda.php', ['id' => $c['id'], 'accion' => 'ATENDIDA'], 'Marcar atendida', 'btn sec chico') ?>
            <?= boton_post('agenda.php', ['id' => $c['id'], 'accion' => 'NO_ASISTIO'], 'No asistió') ?>
          <?php endif; ?>
          <?php if ($agendada && puede('agendar')): ?>
            <a class="btn gris chico" href="agendar.php?reagendar=<?= (int)$c['id'] ?>">Cambiar la hora</a>
          <?php endif; ?>
          <?php if ($agendada && puede('cancelar')): ?>
            <a class="btn peligro chico" href="agenda.php?cancelar=<?= (int)$c['id'] ?>">Cancelar cita</a>
          <?php endif; ?>
          <?php if ($c['estado'] === 'CANCELADA' && puede('cancelar') && strtotime($c['inicio']) > time()): ?>
            <?= boton_post('agenda.php', ['id' => $c['id'], 'accion' => 'REACTIVAR'], 'Deshacer cancelación', 'btn sec chico') ?>
          <?php endif; ?>
          <a class="btn gris chico" href="paciente.php?id=<?= (int)$c['paciente_id'] ?>">Ver paciente</a>
        </div>
        <?php endif; ?>
      </div>
    </article>
    <?php
}

/** Campo de formulario con su error debajo, si lo hay. Repinta siempre lo que la persona escribió. */
function campo(string $nombre, string $etiqueta, array $datos, array $errores, array $op = []): void
{
    $tipo  = $op['tipo'] ?? 'text';
    $ayuda = $op['ayuda'] ?? '';
    $extra = $op['extra'] ?? '';
    $opc   = !empty($op['opcional']) ? ' <span class="opt">(opcional)</span>' : '';
    ?>
    <div class="campo <?= isset($errores[$nombre]) ? 'malo' : '' ?>">
      <label for="<?= h($nombre) ?>"><?= h($etiqueta) ?><?= $opc ?></label>
      <input type="<?= h($tipo) ?>" id="<?= h($nombre) ?>" name="<?= h($nombre) ?>"
             value="<?= h((string)($datos[$nombre] ?? '')) ?>" <?= $extra ?>
             <?= isset($errores[$nombre]) ? 'aria-invalid="true" aria-describedby="err-' . h($nombre) . '"' : '' ?>>
      <?php if (isset($errores[$nombre])): ?>
        <span class="error-campo" id="err-<?= h($nombre) ?>"><?= h($errores[$nombre]) ?></span>
      <?php elseif ($ayuda): ?>
        <span class="ayuda-campo"><?= h($ayuda) ?></span>
      <?php endif; ?>
    </div>
    <?php
}
