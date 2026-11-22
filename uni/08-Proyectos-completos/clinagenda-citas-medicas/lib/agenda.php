<?php
// Las reglas de las citas. Es el corazón del sistema.
//
// Cómo se evita que dos recepcionistas den la misma hora:
//  1. Cada cambio abre una transacción y bloquea la fila del médico
//     (SELECT ... FOR UPDATE). Si Rosa y María guardan a la vez para el
//     mismo médico, la base las pone en fila: la segunda espera a que la
//     primera termine y recién entonces revisa si la hora sigue libre.
//  2. Por si algo se salta el paso 1, la tabla citas tiene la clave única
//     uk_medico_slot. La base rechaza la segunda cita con el error 1062 y
//     aquí se traduce a un mensaje que una persona entiende.

declare(strict_types=1);

/* ---------- Clínicas, médicos, horarios ---------- */

function clinicas_todas(): array
{
    static $c = null;
    return $c ??= filas('SELECT * FROM clinicas WHERE activo = 1 ORDER BY id');
}

function clinica(int $id): ?array
{
    foreach (clinicas_todas() as $c) if ((int)$c['id'] === $id) return $c;
    return null;
}

function medico(int $id): ?array
{
    return fila('SELECT * FROM medicos WHERE id = ?', [$id]);
}

/** Médicos activos que tienen horario en esa clínica. */
function medicos_de_clinica(int $clinicaId): array
{
    return filas('SELECT DISTINCT m.* FROM medicos m JOIN medico_horario h ON h.medico_id = m.id
                  WHERE h.clinica_id = ? AND m.activo = 1 ORDER BY m.nombres', [$clinicaId]);
}

function medico_atiende_en(int $medicoId, int $clinicaId): bool
{
    return (bool)valor('SELECT COUNT(*) FROM medico_horario WHERE medico_id = ? AND clinica_id = ?', [$medicoId, $clinicaId]);
}

function feriado_en(int $clinicaId, string $fecha): ?array
{
    return fila('SELECT * FROM feriados WHERE activo = 1 AND fecha = ? AND (clinica_id IS NULL OR clinica_id = ?) LIMIT 1',
                [$fecha, $clinicaId]);
}

/** Tramos en que el médico atiende en esa clínica ese día. Vacío si es feriado. */
function bloques_atencion(int $clinicaId, int $medicoId, string $fecha): array
{
    if (feriado_en($clinicaId, $fecha)) return [];
    return filas("SELECT TIME_FORMAT(hora_inicio, '%H:%i') AS ini, TIME_FORMAT(hora_fin, '%H:%i') AS fin
                  FROM medico_horario WHERE clinica_id = ? AND medico_id = ? AND dia_semana = ?
                  ORDER BY hora_inicio", [$clinicaId, $medicoId, (int)date('N', strtotime($fecha))]);
}

/**
 * Las horas del día de un médico en una clínica, con su estado.
 * "Ocupada" mira las citas del médico en todas las clínicas: por eso el
 * Dr. Salazar no aparece libre en Centro a la hora que ya atiende en Sur.
 */
function horas_del_dia(int $clinicaId, int $medicoId, string $fecha): array
{
    $m = medico($medicoId);
    if (!$m || !fecha_valida($fecha)) return [];
    $dur = (int)$m['duracion_cita_min'];

    $citas = filas("SELECT c.inicio, c.fin, c.clinica_id, cl.nombre AS clinica_nombre
                    FROM citas c JOIN clinicas cl ON cl.id = c.clinica_id
                    WHERE c.medico_id = ? AND c.estado <> 'CANCELADA' AND c.inicio < ? AND c.fin > ?",
                   [$medicoId, $fecha . ' 23:59:59', $fecha . ' 00:00:00']);

    $horas = [];
    foreach (bloques_atencion($clinicaId, $medicoId, $fecha) as $b) {
        for ($t = a_minutos($b['ini']); $t + $dur <= a_minutos($b['fin']); $t += $dur) {
            $ini = "$fecha " . a_hora($t) . ':00';
            $fin = "$fecha " . a_hora($t + $dur) . ':00';
            $choque = null;
            foreach ($citas as $c) if ($c['inicio'] < $fin && $c['fin'] > $ini) { $choque = $c; break; }
            $horas[] = [
                'hora'         => a_hora($t),
                'ocupada'      => $choque !== null,
                'otra_clinica' => $choque && (int)$choque['clinica_id'] !== $clinicaId ? $choque['clinica_nombre'] : null,
                'pasada'       => strtotime($ini) < time(),
            ];
        }
    }
    return $horas;
}

/** "Estas siguen libres: 10:30, 11:00 y 11:30." Las más cercanas a la hora que se pidió. */
function horas_libres_texto(int $clinicaId, int $medicoId, string $fecha, string $cerca = '', int $cuantas = 3): string
{
    $libres = [];
    foreach (horas_del_dia($clinicaId, $medicoId, $fecha) as $h)
        if (!$h['ocupada'] && !$h['pasada']) $libres[] = $h['hora'];
    if (!$libres) return 'Ese día ya no le quedan horas libres: pruebe con otra fecha.';
    if ($cerca !== '') usort($libres, fn($a, $b) => abs(a_minutos($a) - a_minutos($cerca)) <=> abs(a_minutos($b) - a_minutos($cerca)));
    $libres = array_slice($libres, 0, $cuantas);
    sort($libres);
    $ultima = array_pop($libres);
    return 'Estas siguen libres: ' . ($libres ? implode(', ', $libres) . ' y ' : '') . $ultima . '.';
}

/** Primer día con alguna hora libre para ese médico, desde $desde. */
function proxima_hora_libre(int $clinicaId, int $medicoId, string $desde): ?array
{
    $f = max($desde, date('Y-m-d'));
    for ($i = 0; $i < 30; $i++) {
        foreach (horas_del_dia($clinicaId, $medicoId, $f) as $h)
            if (!$h['ocupada'] && !$h['pasada']) return ['fecha' => $f, 'hora' => $h['hora']];
        $f = date('Y-m-d', strtotime("$f +1 day"));
    }
    return null;
}

/** Primer día en que la clínica todavía atiende (hoy, si aún no cerró). */
function primer_dia_agendable(int $clinicaId): string
{
    $f = date('Y-m-d');
    for ($i = 0; $i < 15; $i++) {
        $desde = $i === 0 ? date('H:i:s') : '00:00:00';
        if (!feriado_en($clinicaId, $f) && valor('SELECT COUNT(*) FROM medico_horario
                WHERE clinica_id = ? AND dia_semana = ? AND hora_fin > ?', [$clinicaId, (int)date('N', strtotime($f)), $desde]))
            return $f;
        $f = date('Y-m-d', strtotime("$f +1 day"));
    }
    return date('Y-m-d');
}

/* ---------- Consultar citas ---------- */

const SELECT_CITA = "SELECT c.*, DATE(c.inicio) AS fecha, DATE_FORMAT(c.inicio, '%H:%i') AS hora,
       p.nombres, p.apellidos, p.tipo_documento, p.documento, p.telefono, p.fecha_nacimiento,
       p.representante, p.representante_telefono,
       m.nombres AS medico_nombre, m.especialidad, cl.nombre AS clinica_nombre,
       uc.nombre AS creada_por_nombre, ux.nombre AS cancelada_por_nombre
  FROM citas c
  JOIN pacientes p ON p.id = c.paciente_id
  JOIN medicos m   ON m.id = c.medico_id
  JOIN clinicas cl ON cl.id = c.clinica_id
  JOIN usuarios uc ON uc.id = c.creada_por
  LEFT JOIN usuarios ux ON ux.id = c.cancelada_por";

function cita(int $id): ?array
{
    return fila(SELECT_CITA . ' WHERE c.id = ?', [$id]);
}

/** Citas de una clínica en un día (y de un médico, si se pide), por hora. */
function citas_del_dia(int $clinicaId, string $fecha, int $medicoId = 0): array
{
    $sql = SELECT_CITA . ' WHERE c.clinica_id = ? AND c.inicio >= ? AND c.inicio < ?';
    $p = [$clinicaId, "$fecha 00:00:00", date('Y-m-d', strtotime("$fecha +1 day")) . ' 00:00:00'];
    if ($medicoId) { $sql .= ' AND c.medico_id = ?'; $p[] = $medicoId; }
    return filas($sql . ' ORDER BY c.inicio, m.nombres', $p);
}

function citas_de_paciente(int $pacienteId): array
{
    return filas(SELECT_CITA . ' WHERE c.paciente_id = ? ORDER BY c.inicio DESC', [$pacienteId]);
}

function contar_pendientes(array $clinicaIds, string $fecha): int
{
    if (!$clinicaIds) return 0;
    $marcas = implode(',', array_fill(0, count($clinicaIds), '?'));
    return (int)valor("SELECT COUNT(*) FROM citas WHERE estado = 'AGENDADA' AND clinica_id IN ($marcas)
                       AND inicio >= ? AND inicio < ?",
                      [...$clinicaIds, "$fecha 00:00:00", date('Y-m-d', strtotime("$fecha +1 day")) . ' 00:00:00']);
}

/** Próximo día (después de $fecha) que tiene citas pendientes. */
function proximo_dia_con_citas(array $clinicaIds, string $fecha): ?string
{
    if (!$clinicaIds) return null;
    $marcas = implode(',', array_fill(0, count($clinicaIds), '?'));
    $f = valor("SELECT DATE(MIN(inicio)) FROM citas WHERE estado = 'AGENDADA' AND clinica_id IN ($marcas) AND inicio >= ?",
               [...$clinicaIds, date('Y-m-d', strtotime("$fecha +1 day")) . ' 00:00:00']);
    return $f ?: null;
}

/* ---------- Revisar una cita antes de guardarla ---------- */

/**
 * ¿El médico o el paciente ya tienen algo a esa hora? Devuelve el mensaje
 * para la recepcionista, o null si está libre. $excluir es la cita que se
 * está moviendo o reactivando, que no cuenta como choque consigo misma.
 */
function choque_de_cita(int $clinicaId, int $medicoId, int $pacienteId, string $inicio, string $fin, int $excluir = 0): ?string
{
    $otra = fila("SELECT c.*, DATE_FORMAT(c.inicio, '%H:%i') AS hora, cl.nombre AS clinica_nombre, u.nombre AS creada_por_nombre,
                         m.nombres AS medico_nombre
                  FROM citas c JOIN clinicas cl ON cl.id = c.clinica_id JOIN usuarios u ON u.id = c.creada_por
                  JOIN medicos m ON m.id = c.medico_id
                  WHERE c.medico_id = ? AND c.estado <> 'CANCELADA' AND c.inicio < ? AND c.fin > ? AND c.id <> ?
                  LIMIT 1", [$medicoId, $fin, $inicio, $excluir]);
    if ($otra) {
        $fecha = substr($inicio, 0, 10);
        if ((int)$otra['clinica_id'] !== $clinicaId) {
            $texto = "{$otra['medico_nombre']} ya está atendiendo en {$otra['clinica_nombre']} a las {$otra['hora']}. "
                   . 'No puede estar en dos clínicas a la vez.';
        } elseif (time() - strtotime($otra['creada_en']) < 120 && (int)$otra['creada_por'] !== uid()) {
            $texto = "Esa hora acaba de ser tomada por {$otra['creada_por_nombre']} hace un instante.";
        } else {
            $texto = "{$otra['medico_nombre']} ya tiene una cita a las {$otra['hora']}.";
        }
        return $texto . ' ' . horas_libres_texto($clinicaId, $medicoId, $fecha, substr($inicio, 11, 5));
    }

    $delPaciente = fila("SELECT c.*, DATE_FORMAT(c.inicio, '%H:%i') AS hora, m.nombres AS medico_nombre,
                                p.nombres, p.apellidos
                         FROM citas c JOIN medicos m ON m.id = c.medico_id JOIN pacientes p ON p.id = c.paciente_id
                         WHERE c.paciente_id = ? AND c.estado <> 'CANCELADA' AND c.inicio < ? AND c.fin > ? AND c.id <> ?
                         LIMIT 1", [$pacienteId, $fin, $inicio, $excluir]);
    if ($delPaciente) {
        return nombre_paciente($delPaciente) . " ya tiene una cita a las {$delPaciente['hora']} ese día, con "
             . "{$delPaciente['medico_nombre']}. Elija otra hora.";
    }
    return null;
}

/**
 * Revisa todo antes de guardar. Devuelve ['error' => mensaje] o
 * ['error' => null, 'inicio' => ..., 'fin' => ...].
 */
function validar_cita(array $d, int $excluir = 0): array
{
    $cl = clinica((int)($d['clinica_id'] ?? 0));
    $me = medico((int)($d['medico_id'] ?? 0));
    $pa = paciente((int)($d['paciente_id'] ?? 0));
    $fecha = (string)($d['fecha'] ?? '');
    $hora  = (string)($d['hora'] ?? '');

    if (!$pa) return ['error' => 'Elija para quién es la cita (paso 1).'];
    if (!$cl) return ['error' => 'Elija la clínica.'];
    if (!$me || !$me['activo']) return ['error' => 'Elija el médico (paso 2).'];
    if (!fecha_valida($fecha)) return ['error' => 'Elija una fecha válida.'];
    if (!preg_match('/^\d{2}:\d{2}$/', $hora)) return ['error' => 'Elija una de las horas libres (paso 3).'];

    if (!medico_atiende_en((int)$me['id'], (int)$cl['id']))
        return ['error' => "{$me['nombres']} no atiende en {$cl['nombre']}. Elija otro médico u otra clínica."];

    $inicio = "$fecha $hora:00";
    $fin    = date('Y-m-d H:i:s', strtotime($inicio) + (int)$me['duracion_cita_min'] * 60);

    if (strtotime($inicio) < time())
        return ['error' => 'Esa fecha u hora ya pasó. Elija una hora de hoy en adelante.'];

    if ($f = feriado_en((int)$cl['id'], $fecha))
        return ['error' => "Ese día es feriado ({$f['descripcion']}) y la clínica no atiende. Elija otra fecha."];

    $horas = array_column(horas_del_dia((int)$cl['id'], (int)$me['id'], $fecha), 'hora');
    if (!$horas)
        return ['error' => "{$me['nombres']} no atiende en {$cl['nombre']} los " . nombre_dia($fecha) . '. Elija otro día.'];
    if (!in_array($hora, $horas, true))
        return ['error' => 'Esa hora no es uno de los turnos del médico. Elija una de las horas que muestra la pantalla.'];

    $choque = choque_de_cita((int)$cl['id'], (int)$me['id'], (int)$pa['id'], $inicio, $fin, $excluir);
    if ($choque) return ['error' => $choque];

    return ['error' => null, 'inicio' => $inicio, 'fin' => $fin];
}

/** Pone en fila a quien quiera tocar la agenda de estos médicos y de este paciente. */
function bloquear_agenda(array $medicoIds, int $pacienteId): void
{
    $medicoIds = array_values(array_unique(array_map('intval', $medicoIds)));
    sort($medicoIds);
    $marcas = implode(',', array_fill(0, count($medicoIds), '?'));
    consulta("SELECT id FROM medicos WHERE id IN ($marcas) ORDER BY id FOR UPDATE", $medicoIds);
    consulta('SELECT id FROM pacientes WHERE id = ? FOR UPDATE', [$pacienteId]);
}

/** Traduce un choque con una clave única a algo que se entienda. */
function mensaje_por_clave(PDOException $e, array $d): ?string
{
    $clave = clave_repetida($e);
    if ($clave === 'uk_medico_slot' || $clave === 'uk_paciente_slot') {
        $v = validar_cita($d);
        return $v['error'] ?? 'Esa hora acaba de ser tomada por otra persona. Elija otra de las horas libres.';
    }
    return null;
}

/* ---------- Cambiar citas ---------- */

/**
 * Guarda una cita nueva. $d trae clinica_id, medico_id, paciente_id, fecha,
 * hora, motivo y token. El token hace que la misma cita enviada dos veces
 * (doble clic, o la cola sin conexión que reintenta) se guarde una sola vez.
 */
function cita_crear(array $d): array
{
    $token = preg_match('/^[a-f0-9]{16,32}$/', (string)($d['token'] ?? '')) ? $d['token'] : bin2hex(random_bytes(16));
    try {
        return transaccion(function () use ($d, $token) {
            $ya = valor('SELECT id FROM citas WHERE token = ?', [$token]);
            if ($ya) return ['ok' => true, 'repetida' => true, 'id' => (int)$ya];

            bloquear_agenda([(int)$d['medico_id']], (int)$d['paciente_id']);
            $v = validar_cita($d);
            if ($v['error']) return ['ok' => false, 'error' => $v['error']];

            $fila = ['clinica_id' => (int)$d['clinica_id'], 'medico_id' => (int)$d['medico_id'],
                     'paciente_id' => (int)$d['paciente_id'], 'inicio' => $v['inicio'], 'fin' => $v['fin'],
                     'motivo' => limpiar_texto($d['motivo'] ?? '', 160) ?: null];
            consulta('INSERT INTO citas (clinica_id, medico_id, paciente_id, inicio, fin, motivo, creada_por, token)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [...array_values($fila), uid(), $token]);
            $id = (int)db()->lastInsertId();

            $c = cita($id);
            registrar('AGENDAR', 'citas', $id, nombre_paciente($c) . " con {$c['medico_nombre']}, "
                . fecha_larga($c['fecha']) . " a las {$c['hora']} en {$c['clinica_nombre']}", null, $fila);
            return ['ok' => true, 'id' => $id];
        });
    } catch (PDOException $e) {
        if (clave_repetida($e) === 'uk_token') return ['ok' => true, 'repetida' => true, 'id' => 0];
        $mensaje = mensaje_por_clave($e, $d);
        if ($mensaje) return ['ok' => false, 'error' => $mensaje];
        throw $e;
    }
}

/**
 * Cancelar no borra nada: la cita cambia de estado, guarda el motivo y quién
 * lo hizo, y la hora queda libre. Cancelar dos veces no es un error.
 */
function cita_cancelar(int $id, string $motivo): array
{
    $motivo = limpiar_texto($motivo, 160);
    if ($motivo === '') return ['ok' => false, 'error' => 'Escriba por qué se cancela. Así después se sabe qué pasó.'];

    return transaccion(function () use ($id, $motivo) {
        $antes = cita($id);
        if (!$antes) return ['ok' => false, 'error' => 'No se encontró esa cita.'];

        $n = consulta("UPDATE citas SET estado = 'CANCELADA', motivo_cancelacion = ?, cancelada_por = ?, cancelada_en = NOW()
                       WHERE id = ? AND estado = 'AGENDADA'", [$motivo, uid(), $id])->rowCount();
        if ($n === 0) {
            $real = cita($id);
            if ($real['estado'] === 'CANCELADA') {
                return ['ok' => true, 'ya_estaba' => true, 'mensaje' => 'Esta cita ya estaba cancelada desde las '
                    . date('H:i', strtotime($real['cancelada_en'])) . ' del ' . fecha_corta($real['cancelada_en'])
                    . ' por ' . ($real['cancelada_por_nombre'] ?? 'otra persona') . '. No hizo falta hacer nada.'];
            }
            return ['ok' => false, 'error' => 'Esta cita ya se marcó como "' . texto_estado($real['estado']) . '" y ya no se puede cancelar.'];
        }

        registrar('CANCELAR', 'citas', $id, nombre_paciente($antes) . ', ' . fecha_larga($antes['fecha'])
            . " a las {$antes['hora']} con {$antes['medico_nombre']}. Motivo: $motivo",
            ['estado' => $antes['estado']], ['estado' => 'CANCELADA', 'motivo_cancelacion' => $motivo]);
        return ['ok' => true, 'ya_estaba' => false];
    });
}

/** Deshacer una cancelación. Si alguien ya tomó esa hora, no se le quita a nadie. */
function cita_reactivar(int $id): array
{
    $c = cita($id);
    if (!$c) return ['ok' => false, 'error' => 'No se encontró esa cita.'];
    if ($c['estado'] !== 'CANCELADA') return ['ok' => true];
    if (strtotime($c['inicio']) < time()) return ['ok' => false, 'error' => 'Esa cita ya pasó: no tiene sentido reactivarla. Dele una hora nueva al paciente.'];

    try {
        return transaccion(function () use ($id, $c) {
            bloquear_agenda([(int)$c['medico_id']], (int)$c['paciente_id']);
            $choque = choque_de_cita((int)$c['clinica_id'], (int)$c['medico_id'], (int)$c['paciente_id'], $c['inicio'], $c['fin'], $id);
            if ($choque) return ['ok' => false, 'error' => 'No se puede deshacer. ' . $choque];

            $n = consulta("UPDATE citas SET estado = 'AGENDADA', motivo_cancelacion = NULL, cancelada_por = NULL, cancelada_en = NULL
                           WHERE id = ? AND estado = 'CANCELADA'", [$id])->rowCount();
            if ($n) registrar('REACTIVAR', 'citas', $id, 'Se deshizo la cancelación: ' . nombre_paciente($c) . ', '
                . fecha_larga($c['fecha']) . " a las {$c['hora']}",
                ['estado' => 'CANCELADA', 'motivo_cancelacion' => $c['motivo_cancelacion']], ['estado' => 'AGENDADA']);
            return ['ok' => true];
        });
    } catch (PDOException $e) {
        if (clave_repetida($e)) return ['ok' => false, 'error' => 'No se puede deshacer: esa hora ya la tomó otro paciente. Dele una hora nueva.'];
        throw $e;
    }
}

/** Atendida o No asistió. Solo para citas agendadas cuyo día ya llegó. */
function cita_marcar(int $id, string $estado): array
{
    if (!in_array($estado, ['ATENDIDA', 'NO_ASISTIO'], true)) return ['ok' => false, 'error' => 'Esa acción no existe.'];
    $c = cita($id);
    if (!$c) return ['ok' => false, 'error' => 'No se encontró esa cita.'];
    if ($c['fecha'] > date('Y-m-d')) return ['ok' => false, 'error' => 'Todavía no llega el día de esta cita.'];

    return transaccion(function () use ($id, $estado, $c) {
        $n = consulta("UPDATE citas SET estado = ? WHERE id = ? AND estado = 'AGENDADA'", [$estado, $id])->rowCount();
        if (!$n) return ['ok' => false, 'error' => 'Esta cita ya no está agendada; alguien la cambió antes.'];
        registrar($estado === 'ATENDIDA' ? 'ATENDIDA' : 'NO_ASISTIO', 'citas', $id,
            nombre_paciente($c) . ' · ' . fecha_larga($c['fecha']) . " a las {$c['hora']}",
            ['estado' => 'AGENDADA'], ['estado' => $estado]);
        return ['ok' => true];
    });
}

/** Mueve una cita a otra hora: la vieja queda cancelada con la nota y la nueva se crea, todo de una vez. */
function cita_reagendar(int $idVieja, array $d): array
{
    $vieja = cita($idVieja);
    if (!$vieja || $vieja['estado'] !== 'AGENDADA') return ['ok' => false, 'error' => 'Esa cita ya no está agendada, así que no se puede mover.'];
    $d['paciente_id'] = (int)$vieja['paciente_id'];
    $token = preg_match('/^[a-f0-9]{16,32}$/', (string)($d['token'] ?? '')) ? $d['token'] : bin2hex(random_bytes(16));

    try {
        return transaccion(function () use ($idVieja, $vieja, $d, $token) {
            if ($ya = valor('SELECT id FROM citas WHERE token = ?', [$token])) return ['ok' => true, 'repetida' => true, 'id' => (int)$ya];

            bloquear_agenda([(int)$vieja['medico_id'], (int)$d['medico_id']], (int)$vieja['paciente_id']);
            $v = validar_cita($d, $idVieja);
            if ($v['error']) return ['ok' => false, 'error' => $v['error']];

            $nota = 'Se reagendó para el ' . fecha_larga($d['fecha']) . ' a las ' . $d['hora'];
            $n = consulta("UPDATE citas SET estado = 'CANCELADA', motivo_cancelacion = ?, cancelada_por = ?, cancelada_en = NOW()
                           WHERE id = ? AND estado = 'AGENDADA'", [$nota, uid(), $idVieja])->rowCount();
            if (!$n) return ['ok' => false, 'error' => 'Alguien cambió esta cita mientras usted la movía. Revise la agenda.'];

            $motivo = limpiar_texto($d['motivo'] ?? '', 160) ?: $vieja['motivo'];
            consulta('INSERT INTO citas (clinica_id, medico_id, paciente_id, inicio, fin, motivo, creada_por, token)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                     [(int)$d['clinica_id'], (int)$d['medico_id'], (int)$vieja['paciente_id'], $v['inicio'], $v['fin'], $motivo, uid(), $token]);
            $id = (int)db()->lastInsertId();
            $nueva = cita($id);

            registrar('REAGENDAR', 'citas', $id, nombre_paciente($vieja) . ': de ' . fecha_larga($vieja['fecha'])
                . " {$vieja['hora']} a " . fecha_larga($nueva['fecha']) . " {$nueva['hora']} con {$nueva['medico_nombre']}",
                ['cita' => $idVieja, 'inicio' => $vieja['inicio'], 'medico' => $vieja['medico_nombre'], 'clinica' => $vieja['clinica_nombre']],
                ['cita' => $id, 'inicio' => $nueva['inicio'], 'medico' => $nueva['medico_nombre'], 'clinica' => $nueva['clinica_nombre']]);
            return ['ok' => true, 'id' => $id];
        });
    } catch (PDOException $e) {
        if (clave_repetida($e) === 'uk_token') return ['ok' => true, 'repetida' => true, 'id' => 0];
        $mensaje = mensaje_por_clave($e, $d);
        if ($mensaje) return ['ok' => false, 'error' => $mensaje];
        throw $e;
    }
}

function texto_estado(string $estado): string
{
    return ['AGENDADA' => 'Agendada', 'ATENDIDA' => 'Atendida', 'CANCELADA' => 'Cancelada', 'NO_ASISTIO' => 'No asistió'][$estado] ?? $estado;
}

/* ---------- Médicos y feriados (administración) ---------- */

function medicos_todos(): array
{
    return filas("SELECT m.*, GROUP_CONCAT(DISTINCT REPLACE(cl.nombre, 'Clínica ', '') ORDER BY cl.id SEPARATOR ', ') AS clinicas
                  FROM medicos m
                  LEFT JOIN medico_horario h ON h.medico_id = m.id
                  LEFT JOIN clinicas cl ON cl.id = h.clinica_id
                  GROUP BY m.id ORDER BY m.activo DESC, m.nombres");
}

/**
 * Las citas ya agendadas conservan su duración. El cambio vale para las
 * citas nuevas: así nadie pierde una hora que ya tenía.
 */
function medico_cambiar_duracion(int $id, int $minutos): array
{
    if ($minutos < 10 || $minutos > 120 || $minutos % 5 !== 0)
        return ['ok' => false, 'error' => 'La duración va de 10 a 120 minutos, de cinco en cinco.'];
    return transaccion(function () use ($id, $minutos) {
        $m = fila('SELECT * FROM medicos WHERE id = ? FOR UPDATE', [$id]);
        if (!$m) return ['ok' => false, 'error' => 'No se encontró ese médico.'];
        consulta('UPDATE medicos SET duracion_cita_min = ? WHERE id = ?', [$minutos, $id]);
        registrar('DURACION', 'medicos', $id, "{$m['nombres']}: citas de $minutos minutos",
            ['duracion_cita_min' => (int)$m['duracion_cita_min']], ['duracion_cita_min' => $minutos]);
        return ['ok' => true];
    });
}

function medico_alternar_activo(int $id): void
{
    transaccion(function () use ($id) {
        $m = fila('SELECT * FROM medicos WHERE id = ? FOR UPDATE', [$id]);
        if (!$m) return;
        $nuevo = $m['activo'] ? 0 : 1;
        consulta('UPDATE medicos SET activo = ? WHERE id = ?', [$nuevo, $id]);
        registrar('EDITAR_MEDICO', 'medicos', $id, $m['nombres'] . ($nuevo ? ' vuelve a recibir citas' : ' deja de recibir citas nuevas'),
            ['activo' => (int)$m['activo']], ['activo' => $nuevo]);
    });
}

function feriados_activos(): array
{
    return filas('SELECT f.*, c.nombre AS clinica_nombre FROM feriados f LEFT JOIN clinicas c ON c.id = f.clinica_id
                  WHERE f.activo = 1 ORDER BY f.fecha');
}

function feriado_agregar(string $fecha, int $clinicaId, string $descripcion): array
{
    $descripcion = limpiar_texto($descripcion, 120);
    if (!fecha_valida($fecha)) return ['ok' => false, 'error' => 'Elija la fecha del feriado.'];
    if ($descripcion === '') return ['ok' => false, 'error' => 'Escriba de qué es el feriado.'];
    if (feriado_en($clinicaId, $fecha))
        return ['ok' => false, 'error' => 'Ese día ya está marcado como feriado.'];
    return transaccion(function () use ($fecha, $clinicaId, $descripcion) {
        consulta('INSERT INTO feriados (fecha, clinica_id, descripcion) VALUES (?, ?, ?)', [$fecha, $clinicaId ?: null, $descripcion]);
        registrar('FERIADO', 'feriados', (int)db()->lastInsertId(), fecha_corta($fecha) . ": $descripcion");
        return ['ok' => true];
    });
}

/** Quitar un feriado lo deja inactivo: queda el registro de que existió. */
function feriado_quitar(int $id): void
{
    transaccion(function () use ($id) {
        $f = fila('SELECT * FROM feriados WHERE id = ? AND activo = 1 FOR UPDATE', [$id]);
        if (!$f) return;
        consulta('UPDATE feriados SET activo = 0 WHERE id = ?', [$id]);
        registrar('QUITAR_FERIADO', 'feriados', $id, fecha_corta($f['fecha']) . ': ' . $f['descripcion'] . ' ya no es feriado');
    });
}
