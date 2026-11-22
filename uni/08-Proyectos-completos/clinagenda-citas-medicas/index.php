<?php
// Entrar al sistema.
require __DIR__ . '/lib/auth.php';

if (usuario_actual()) ir('inicio.php');

$error = null;
$usuarioEscrito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_csrf();
    $usuarioEscrito = trim((string)($_POST['usuario'] ?? ''));
    $r = intentar_login($usuarioEscrito, (string)($_POST['clave'] ?? ''));
    if ($r['ok']) {
        $destino = $_SESSION['volver_a'] ?? 'inicio.php';
        unset($_SESSION['volver_a']);
        // Solo se vuelve a páginas del propio sistema, nunca a otro sitio.
        ir(preg_match('#^/(?!/)[\w/.-]*\.php(\?\S*)?$#', $destino) ? $destino : 'inicio.php');
    }
    $error = $r['error'];
}

cabecera('Entrar', ['sin_nav' => true]);
?>

<div class="entrada">

  <div class="portada">
    <div class="dentro">
      <div class="marca">
        <svg viewBox="0 0 48 48" aria-hidden="true">
          <rect x="7" y="9.5" width="34" height="31" rx="5"/>
          <path d="M15.5 9.5V4.5M32.5 9.5V4.5M7 18.5h34"/>
          <circle cx="24" cy="29.5" r="6.6"/>
          <path d="M24 26.4v3.4l2.5 1.6"/>
        </svg>
        <h1>ClinAgenda</h1>
      </div>
      <p>Las citas de los tres consultorios de la Dra. Paredes, en un solo lugar.</p>
      <div class="sedes">
        <?php foreach (clinicas_todas() as $c): ?>
          <span><?= h(str_replace('Clínica ', '', $c['nombre'])) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <?php if ($error): ?>
    <p class="aviso mal" role="alert"><?= h($error) ?></p>
  <?php endif; ?>

  <form class="tarjeta" method="post" action="index.php" novalidate>
    <?= campo_csrf() ?>
    <h2>Entrar al sistema</h2>

    <div class="campo">
      <label for="usuario">Usuario</label>
      <input type="text" id="usuario" name="usuario" value="<?= h($usuarioEscrito) ?>"
             autocomplete="username" autocapitalize="none" spellcheck="false" required autofocus>
    </div>

    <div class="campo">
      <label for="clave">Contraseña</label>
      <input type="password" id="clave" name="clave" autocomplete="current-password" required>
      <label class="marcar">
        <input type="checkbox" id="ver-clave"> Mostrar la contraseña
      </label>
    </div>

    <button class="btn ancho" type="submit">Entrar</button>
  </form>

  <div class="tarjeta">
    <h3>Cuentas para la demostración</h3>
    <p class="ayuda-campo">Cada rol ve una parte distinta del sistema.</p>
    <div class="cuenta"><span><strong>admin</strong> · admin2026</span><span>Administración: todo</span></div>
    <div class="cuenta"><span><strong>aparedes</strong> · clinica2026</span><span>Doctora: ve motivos y auditoría</span></div>
    <div class="cuenta"><span><strong>rosa</strong> · rosa2026</span><span>Recepción, pantalla sencilla</span></div>
    <div class="cuenta"><span><strong>maria</strong> · maria2026</span><span>Recepción, Clínica Centro</span></div>
    <div class="cuenta"><span><strong>tania</strong> · tania2026</span><span>Recepción, Clínica Sur</span></div>
  </div>

  <div class="pie-entrada">
    <p>Las contraseñas se guardan cifradas. Después de cinco intentos fallidos hay que
       esperar un minuto, y la sesión se cierra sola tras 20 minutos sin uso.</p>
    <p><a href="manual.php">Cómo usar el sistema</a></p>
  </div>

</div>

<?php pie(['sin_nav' => true]); ?>
