<?php
// Sesión, roles y permisos. Todas las páginas empiezan con
//     require __DIR__ . '/lib/auth.php';
// y desde aquí se carga el resto de lib/.

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/validacion.php';
require_once __DIR__ . '/auditoria.php';
require_once __DIR__ . '/pacientes.php';
require_once __DIR__ . '/agenda.php';
require_once __DIR__ . '/plantilla.php';

const MINUTOS_INACTIVIDAD = 20;

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// Si nadie tocó el sistema en 20 minutos, la sesión se cierra sola. Así una
// computadora olvidada en recepción no queda abierta con el nombre de alguien.
if (!empty($_SESSION['uid']) && time() - (int)($_SESSION['ultimo_movimiento'] ?? time()) > MINUTOS_INACTIVIDAD * 60) {
    $_SESSION = [];
    session_regenerate_id(true);
    avisar('La sesión se cerró porque pasaron ' . MINUTOS_INACTIVIDAD . ' minutos sin uso. Vuelva a entrar.', 'info');
}
$_SESSION['ultimo_movimiento'] = time();

/* ---------- Quién está usando el sistema ---------- */

function usuario_actual(): ?array
{
    static $cache = [];
    $id = (int)($_SESSION['uid'] ?? 0);
    if (!$id) return null;
    if (!array_key_exists($id, $cache)) {
        $u = fila('SELECT * FROM usuarios WHERE id = ? AND activo = 1', [$id]);
        $cache[$id] = $u;
    }
    return $cache[$id];
}

function uid(): int
{
    return (int)(usuario_actual()['id'] ?? 0);
}

/** La pantalla sencilla es una preferencia de la cuenta, no algo atado al nombre de alguien. */
function modo_simple(): bool
{
    return !empty(usuario_actual()['modo_simple']);
}

function ir(string $destino): void
{
    header('Location: ' . $destino);
    exit;
}

/* ---------- Permisos ----------
   Esconder un botón no es seguridad. Cada página que hace algo llama a
   exigir(), y la revisión pasa en el servidor antes de tocar un dato. */

function permisos_de(string $rol): array
{
    $recepcion = ['ver_agenda', 'agendar', 'cancelar', 'marcar_estado',
                  'ver_pacientes', 'crear_paciente', 'editar_paciente'];
    $doctora   = array_merge($recepcion, ['ver_todas_las_clinicas', 'ver_motivo', 'ver_auditoria', 'respaldo']);
    $admin     = array_merge($doctora, ['gestionar_usuarios', 'gestionar_catalogos']);
    return ['recepcion' => $recepcion, 'doctora' => $doctora, 'admin' => $admin][$rol] ?? [];
}

const NOMBRE_ROL = ['admin' => 'Administración', 'doctora' => 'Doctora', 'recepcion' => 'Recepción'];

function puede(string $accion): bool
{
    $u = usuario_actual();
    return $u !== null && in_array($accion, permisos_de($u['rol']), true);
}

function exigir_login(): void
{
    if (usuario_actual()) return;
    if (es_envio_de_cola()) responder_json(['ok' => false, 'reintentar' => true,
        'mensaje' => 'Hay citas guardadas en este equipo. Entre al sistema para que se envíen.']);
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') $_SESSION['volver_a'] = $_SERVER['REQUEST_URI'] ?? 'inicio.php';
    ir('index.php');
}

/** Corta aquí si el usuario no tiene el permiso, y deja anotado el intento. */
function exigir(string $accion): void
{
    exigir_login();
    if (puede($accion)) return;

    http_response_code(403);
    registrar('ACCESO_DENEGADO', 'usuarios', uid(), 'Intentó entrar a algo sin permiso: ' . $accion);
    cabecera('Sin permiso', ['sin_nav' => true]);
    ?>
    <div class="tarjeta">
      <div class="vacio">
        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>
        <h1>Esta parte no es para su usuario</h1>
        <p>Su cuenta no tiene permiso para abrir esta pantalla. Si cree que sí debería
           tenerlo, pídaselo a la administración.</p>
        <p><a class="btn" href="inicio.php">Volver al inicio</a></p>
      </div>
    </div>
    <?php
    pie(['sin_nav' => true]);
    exit;
}

/** Clínicas que este usuario puede ver. Recepción solo ve la suya. */
function clinicas_visibles(): array
{
    $u = usuario_actual();
    if (!$u) return [];
    if (puede('ver_todas_las_clinicas')) return clinicas_todas();
    return array_values(array_filter(clinicas_todas(), fn($c) => (int)$c['id'] === (int)$u['clinica_id']));
}

function clinica_permitida(int $id): bool
{
    foreach (clinicas_visibles() as $c) if ((int)$c['id'] === $id) return true;
    return false;
}

/** La clínica en la que se está trabajando ahora. Se recuerda entre pantallas. */
function clinica_actual(): int
{
    $pedida = (int)($_GET['clinica'] ?? $_SESSION['clinica'] ?? 0);
    if ($pedida && clinica_permitida($pedida)) return $_SESSION['clinica'] = $pedida;
    $u = usuario_actual();
    $id = (int)($u['clinica_id'] ?? 0) ?: (int)(clinicas_visibles()[0]['id'] ?? 1);
    return $_SESSION['clinica'] = $id;
}

/* ---------- Formularios: ningún POST se acepta sin su ficha ---------- */

function csrf(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function campo_csrf(): string
{
    return '<input type="hidden" name="csrf" value="' . h(csrf()) . '">';
}

function exigir_csrf(): void
{
    if (hash_equals(csrf(), (string)($_POST['csrf'] ?? ''))) return;
    http_response_code(400);
    if (es_envio_de_cola()) responder_json(['ok' => false, 'reintentar' => true,
        'mensaje' => 'La sesión cambió. Entre de nuevo al sistema para enviar las citas pendientes.']);
    exit('La página caducó. Vuelva a la pantalla anterior y pruebe de nuevo.');
}

/** Las citas guardadas sin conexión llegan desde assets/app.js con esta cabecera. */
function es_envio_de_cola(): bool
{
    return ($_SERVER['HTTP_X_CLINAGENDA_COLA'] ?? '') === '1';
}

function responder_json(array $datos): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

/* ---------- Entrar y salir ---------- */

/**
 * El mensaje de error no dice si falló el usuario o la contraseña: decirlo
 * ayudaría a adivinar qué usuarios existen.
 */
function intentar_login(string $usuario, string $clave): array
{
    $bloqueado = (int)($_SESSION['bloqueado_hasta'] ?? 0);
    if ($bloqueado > time()) {
        return ['ok' => false, 'error' => 'Demasiados intentos seguidos. Espere ' . ($bloqueado - time()) . ' segundos y vuelva a probar.'];
    }

    $u = fila('SELECT * FROM usuarios WHERE usuario = ? AND activo = 1', [mb_strtolower(trim($usuario))]);
    if (!$u || !password_verify($clave, $u['clave_hash'])) {
        $_SESSION['intentos'] = (int)($_SESSION['intentos'] ?? 0) + 1;
        if ($_SESSION['intentos'] >= 5) {
            $_SESSION['intentos'] = 0;
            $_SESSION['bloqueado_hasta'] = time() + 60;
            return ['ok' => false, 'error' => 'Demasiados intentos seguidos. Espere un minuto antes de volver a probar.'];
        }
        return ['ok' => false, 'error' => 'El usuario o la contraseña no son correctos. Revise que no esté activada la tecla de mayúsculas.'];
    }

    // Identificador de sesión nuevo al entrar: nadie puede reutilizar uno viejo.
    session_regenerate_id(true);
    $_SESSION['uid'] = (int)$u['id'];
    unset($_SESSION['intentos'], $_SESSION['bloqueado_hasta'], $_SESSION['clinica']);
    registrar('ENTRAR', 'usuarios', (int)$u['id'], $u['nombre'] . ' entró al sistema', null, null, $u);
    return ['ok' => true];
}

function cerrar_sesion(): void
{
    $u = usuario_actual();
    if ($u) registrar('SALIR', 'usuarios', (int)$u['id'], $u['nombre'] . ' salió del sistema');
    $_SESSION = [];
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    session_destroy();
}

/* ---------- Avisos que viajan de una pantalla a la siguiente ---------- */

function avisar(string $texto, string $tipo = 'bien'): void
{
    $_SESSION['aviso'] = ['texto' => $texto, 'tipo' => $tipo];
}

function tomar_aviso(): ?array
{
    $a = $_SESSION['aviso'] ?? null;
    unset($_SESSION['aviso']);
    return $a;
}

/* ---------- Cuentas (solo administración) ---------- */

function usuarios_todos(): array
{
    return filas('SELECT u.*, c.nombre AS clinica_nombre FROM usuarios u
                  LEFT JOIN clinicas c ON c.id = u.clinica_id ORDER BY u.activo DESC, u.nombre');
}

function usuario_crear(array $d): array
{
    $usuario = mb_strtolower(trim((string)($d['usuario'] ?? '')));
    $nombre  = limpiar_texto($d['nombre'] ?? '', 120);
    $rol     = (string)($d['rol'] ?? '');
    $clinica = (int)($d['clinica_id'] ?? 0);
    $clave   = (string)($d['clave'] ?? '');
    $e = [];

    if ($nombre === '') $e['nombre'] = 'Escriba el nombre completo de la persona.';
    if (!preg_match('/^[a-z0-9._-]{3,20}$/', $usuario))
        $e['usuario'] = 'Entre 3 y 20 letras o números, sin espacios. Ejemplo: lucia';
    elseif (valor('SELECT COUNT(*) FROM usuarios WHERE usuario = ?', [$usuario]))
        $e['usuario'] = 'Ya existe una cuenta con ese usuario. Elija otro.';
    if (!isset(NOMBRE_ROL[$rol])) $e['rol'] = 'Elija el rol.';
    if ($rol === 'recepcion' && !clinica($clinica)) $e['clinica_id'] = 'Recepción tiene que pertenecer a una clínica.';
    if (mb_strlen($clave) < 8) $e['clave'] = 'La contraseña necesita al menos 8 caracteres.';
    if ($e) return ['ok' => false, 'errores' => $e];

    return transaccion(function () use ($usuario, $nombre, $rol, $clinica, $clave, $d) {
        $fila = ['usuario' => $usuario, 'nombre' => $nombre, 'rol' => $rol,
                 'clinica_id' => $rol === 'recepcion' ? $clinica : null,
                 'modo_simple' => empty($d['modo_simple']) ? 0 : 1];
        consulta('INSERT INTO usuarios (usuario, nombre, rol, clinica_id, modo_simple, clave_hash) VALUES (?, ?, ?, ?, ?, ?)',
            [$usuario, $nombre, $rol, $fila['clinica_id'], $fila['modo_simple'], password_hash($clave, PASSWORD_DEFAULT)]);
        $id = (int)db()->lastInsertId();
        registrar('CREAR_USUARIO', 'usuarios', $id, "Nueva cuenta: $nombre ($usuario), " . NOMBRE_ROL[$rol], null, $fila);
        return ['ok' => true];
    });
}

/** Cambia una sola preferencia de una cuenta: modo_simple o activo. */
function usuario_alternar(int $id, string $campo): void
{
    if (!in_array($campo, ['modo_simple', 'activo'], true)) return;
    if ($campo === 'activo' && $id === uid()) {
        avisar('No puede desactivar su propia cuenta mientras la está usando.', 'mal');
        return;
    }
    transaccion(function () use ($id, $campo) {
        $u = fila('SELECT * FROM usuarios WHERE id = ? FOR UPDATE', [$id]);
        if (!$u) return;
        $nuevo = $u[$campo] ? 0 : 1;
        consulta("UPDATE usuarios SET $campo = ? WHERE id = ?", [$nuevo, $id]);
        $texto = $campo === 'activo'
            ? ($nuevo ? 'Se activó la cuenta de ' : 'Se desactivó la cuenta de ')
            : ($nuevo ? 'Pantalla sencilla activada para ' : 'Pantalla normal para ');
        registrar('EDITAR_USUARIO', 'usuarios', $id, $texto . $u['nombre'], [$campo => (int)$u[$campo]], [$campo => $nuevo]);
        avisar($texto . $u['nombre'] . '.');
    });
}

/** No hay "olvidé mi contraseña": la administración pone una nueva. */
function usuario_cambiar_clave(int $id, string $clave): array
{
    if (mb_strlen($clave) < 8) return ['ok' => false, 'error' => 'La contraseña necesita al menos 8 caracteres.'];
    $u = fila('SELECT nombre FROM usuarios WHERE id = ?', [$id]);
    if (!$u) return ['ok' => false, 'error' => 'No se encontró esa cuenta.'];
    transaccion(function () use ($id, $clave, $u) {
        consulta('UPDATE usuarios SET clave_hash = ? WHERE id = ?', [password_hash($clave, PASSWORD_DEFAULT), $id]);
        registrar('CAMBIAR_CLAVE', 'usuarios', $id, 'Se puso una contraseña nueva a ' . $u['nombre']);
    });
    return ['ok' => true];
}
