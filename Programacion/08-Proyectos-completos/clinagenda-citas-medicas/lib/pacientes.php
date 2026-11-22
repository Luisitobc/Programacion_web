<?php
// Pacientes: registrar, buscar y avisar de posibles duplicados.
// Regla de la casa: el sistema nunca une dos pacientes por su cuenta. Juntar
// las historias de dos personas distintas es peor que tener un duplicado.

declare(strict_types=1);

const CAMPOS_PACIENTE = ['tipo_documento', 'documento', 'nombres', 'apellidos', 'fecha_nacimiento',
                         'telefono', 'correo', 'representante', 'representante_telefono', 'nota'];

/** El código que se ve en pantalla. Sale del id, así que nunca cambia ni se repite. */
function codigo_paciente(int $id): string
{
    return sprintf('PAC-%06d', $id);
}

function paciente(int $id): ?array
{
    return fila('SELECT * FROM pacientes WHERE id = ? AND activo = 1', [$id]);
}

/* Estas funciones reciben cualquier fila que traiga las columnas del
   paciente: sirven igual para un paciente que para una cita con su paciente. */

function nombre_paciente(array $p): string
{
    return limpiar_texto($p['nombres'] . ' ' . $p['apellidos']);
}

function documento_paciente(array $p): string
{
    return match ($p['tipo_documento']) {
        'SIN_DOCUMENTO' => 'sin documento',
        'PASAPORTE'     => 'pasaporte ' . $p['documento'],
        default         => 'C.I. ' . $p['documento'],
    };
}

function contacto_paciente(array $p): string
{
    return (string)($p['telefono'] ?: $p['representante_telefono'] ?: '');
}

function edad_paciente(array $p): ?int
{
    if (empty($p['fecha_nacimiento'])) return null;
    return (new DateTime($p['fecha_nacimiento']))->diff(new DateTime('today'))->y;
}

function edad_texto(array $p): string
{
    $e = edad_paciente($p);
    if ($e === null) return '';
    if ($e === 0) return 'menos de 1 año';
    return $e === 1 ? '1 año' : "$e años";
}

/**
 * Busca por nombre (en cualquier orden, sin tildes), por código PAC-,
 * por cédula o pasaporte, o por teléfono del paciente o de su representante.
 */
function pacientes_buscar(string $q, int $limite = 40): array
{
    $q = limpiar_texto($q, 80);
    $limite = max(1, min($limite, 200));
    if ($q === '') return filas("SELECT * FROM pacientes WHERE activo = 1 ORDER BY nombre_busqueda LIMIT $limite");

    if (preg_match('/^PAC-?0*(\d+)$/i', $q, $m)) {
        return filas('SELECT * FROM pacientes WHERE activo = 1 AND id = ?', [(int)$m[1]]);
    }

    $condiciones = [];
    $parametros  = [];
    $palabras = array_filter(explode(' ', normalizar($q)));
    if ($palabras) {
        $porNombre = [];
        foreach ($palabras as $p) { $porNombre[] = 'nombre_busqueda LIKE ?'; $parametros[] = '%' . $p . '%'; }
        $condiciones[] = '(' . implode(' AND ', $porNombre) . ')';
    }
    $condiciones[] = 'documento LIKE ?';
    $parametros[]  = '%' . mb_strtoupper(str_replace(' ', '', $q)) . '%';
    $digitos = solo_digitos($q);
    if (strlen($digitos) >= 4) {
        $condiciones[] = 'telefono LIKE ? OR representante_telefono LIKE ?';
        array_push($parametros, "%$digitos%", "%$digitos%");
    }

    return filas('SELECT * FROM pacientes WHERE activo = 1 AND (' . implode(' OR ', $condiciones) . ")
                  ORDER BY nombre_busqueda LIMIT $limite", $parametros);
}

function pacientes_contar(): int
{
    return (int)valor('SELECT COUNT(*) FROM pacientes WHERE activo = 1');
}

/** Personas que ya se llaman exactamente igual, con su última cita para ayudar a decidir. */
function pacientes_homonimos(string $nombres, string $apellidos, int $excluir = 0): array
{
    return filas("SELECT p.*, (SELECT MAX(inicio) FROM citas c WHERE c.paciente_id = p.id) AS ultima_cita
                  FROM pacientes p WHERE p.activo = 1 AND p.nombre_busqueda = ? AND p.id <> ?
                  ORDER BY p.id", [normalizar($nombres . ' ' . $apellidos), $excluir]);
}

/** Grupos de pacientes distintos con el mismo nombre. */
function nombres_repetidos(): array
{
    $grupos = [];
    foreach (filas('SELECT * FROM pacientes WHERE activo = 1 AND nombre_busqueda IN (
                      SELECT nombre_busqueda FROM pacientes WHERE activo = 1
                      GROUP BY nombre_busqueda HAVING COUNT(*) > 1)
                    ORDER BY nombre_busqueda, id') as $p) {
        $grupos[$p['nombre_busqueda']][] = $p;
    }
    return array_values($grupos);
}

/** Deja los datos del formulario listos para validar y guardar. */
function paciente_desde_formulario(array $f): array
{
    $p = [];
    foreach (CAMPOS_PACIENTE as $k) $p[$k] = (string)($f[$k] ?? '');
    $p['tipo_documento'] = in_array($p['tipo_documento'], ['CEDULA', 'PASAPORTE', 'SIN_DOCUMENTO'], true)
        ? $p['tipo_documento'] : 'CEDULA';
    $p['documento'] = match ($p['tipo_documento']) {
        'CEDULA'        => trim($p['documento']),
        'PASAPORTE'     => mb_strtoupper((string)preg_replace('/[\s-]/', '', $p['documento'])),
        'SIN_DOCUMENTO' => '',
    };
    $p['nombres']   = limpiar_texto($p['nombres'], 120);
    $p['apellidos'] = limpiar_texto($p['apellidos'], 120);
    $p['fecha_nacimiento'] = trim($p['fecha_nacimiento']);
    // Al teléfono se le quitan espacios y guiones, pero no se le inventa nada:
    // si quedó mal, se avisa y la persona lo corrige.
    $p['telefono'] = (string)preg_replace('/[\s().-]/', '', $p['telefono']);
    $p['representante_telefono'] = (string)preg_replace('/[\s().-]/', '', $p['representante_telefono']);
    $p['correo'] = mb_strtolower(trim($p['correo']));
    $p['representante'] = limpiar_texto($p['representante'], 160);
    $p['nota'] = limpiar_texto($p['nota'], 255);
    return $p;
}

/** Devuelve campo => mensaje. Vacío quiere decir que todo está bien. */
function validar_paciente(array $p, int $excluir = 0): array
{
    $e = [];

    if ($p['nombres'] === '') $e['nombres'] = 'Escriba el nombre.';
    elseif (!nombre_valido($p['nombres'])) $e['nombres'] = 'El nombre solo puede tener letras.';
    if ($p['apellidos'] === '') $e['apellidos'] = 'Escriba el apellido.';
    elseif (!nombre_valido($p['apellidos'])) $e['apellidos'] = 'El apellido solo puede tener letras.';

    if ($p['tipo_documento'] === 'CEDULA') {
        $c = $p['documento'];
        if ($c === '') $e['documento'] = 'Escriba la cédula, o elija "No tiene documento".';
        elseif (!ctype_digit($c)) $e['documento'] = 'La cédula solo lleva números, sin letras ni guiones.';
        elseif (strlen($c) !== 10) $e['documento'] = 'La cédula tiene 10 dígitos y aquí hay ' . strlen($c) . '. Revise el número con la persona.';
        elseif (!in_array((int)substr($c, 0, 2), [...range(1, 24), 30], true))
            $e['documento'] = 'Los dos primeros números de la cédula son la provincia, y "' . substr($c, 0, 2) . '" no es ninguna. Revise el número con la persona.';
        elseif (!cedula_valida($c)) $e['documento'] = 'Esa cédula no existe: el último dígito no coincide. Revise el número dígito por dígito con la persona.';
    } elseif ($p['tipo_documento'] === 'PASAPORTE' && !pasaporte_valido($p['documento'])) {
        $e['documento'] = 'Escriba el número de pasaporte tal como aparece en el documento (de 6 a 15 letras y números).';
    }

    if (!isset($e['documento']) && $p['tipo_documento'] !== 'SIN_DOCUMENTO') {
        $otro = fila('SELECT * FROM pacientes WHERE tipo_documento = ? AND documento = ? AND id <> ?',
                     [$p['tipo_documento'], $p['documento'], $excluir]);
        if ($otro) $e['documento'] = 'Ese documento ya es de ' . nombre_paciente($otro) . ' (' . codigo_paciente((int)$otro['id'])
            . '). Busque a esa persona en lugar de registrarla otra vez.';
    }

    if ($p['fecha_nacimiento'] !== '') {
        $problema = problema_fecha_nacimiento($p['fecha_nacimiento']);
        if ($problema) $e['fecha_nacimiento'] = $problema;
    } elseif ($p['tipo_documento'] === 'SIN_DOCUMENTO') {
        // Sin documento, el nombre y la fecha de nacimiento son lo que distingue a la persona.
        $e['fecha_nacimiento'] = 'Sin documento, la fecha de nacimiento es obligatoria: ayuda a no confundirla con otra persona.';
    }

    if ($p['telefono'] !== '' && !telefono_valido($p['telefono']))
        $e['telefono'] = 'El teléfono debe tener 10 números, como 0991234567, o 9 si es fijo, como 022345678.';
    if ($p['representante_telefono'] !== '' && !telefono_valido($p['representante_telefono']))
        $e['representante_telefono'] = 'El teléfono debe tener 10 números, como 0991234567, o 9 si es fijo, como 022345678.';
    if ($p['correo'] !== '' && !correo_valido($p['correo']))
        $e['correo'] = 'Ese correo no parece completo. Puede dejarlo vacío si el paciente no tiene.';

    if ($p['tipo_documento'] === 'SIN_DOCUMENTO' && $p['representante'] === '' && $p['telefono'] === '')
        $e['representante'] = 'Si no tiene documento, anote quién responde por el paciente o un teléfono.';

    return $e;
}

/** Registra o actualiza. Devuelve ['ok' => true, 'id' => n] o ['ok' => false, 'errores' => [...]]. */
function paciente_guardar(array $p, int $editarId = 0): array
{
    $errores = validar_paciente($p, $editarId);
    if ($errores) return ['ok' => false, 'errores' => $errores];

    $valores = [];
    foreach (CAMPOS_PACIENTE as $k) $valores[$k] = $p[$k] === '' ? null : $p[$k];
    $valores['nombre_busqueda'] = normalizar($p['nombres'] . ' ' . $p['apellidos']);

    try {
        return transaccion(function () use ($valores, $editarId) {
            $columnas = array_keys($valores);
            if ($editarId) {
                $antes = fila('SELECT * FROM pacientes WHERE id = ? FOR UPDATE', [$editarId]);
                if (!$antes) return ['ok' => false, 'errores' => ['general' => 'No se encontró ese paciente.']];
                consulta('UPDATE pacientes SET ' . implode(' = ?, ', $columnas) . ' = ? WHERE id = ?',
                         [...array_values($valores), $editarId]);
                $cambios = array_filter($valores, fn($v, $k) => (string)$antes[$k] !== (string)$v, ARRAY_FILTER_USE_BOTH);
                registrar('EDITAR_PACIENTE', 'pacientes', $editarId,
                    'Se corrigieron datos de ' . nombre_paciente($valores) . ' (' . codigo_paciente($editarId) . ')',
                    array_intersect_key($antes, $cambios), $cambios);
                return ['ok' => true, 'id' => $editarId];
            }
            consulta('INSERT INTO pacientes (' . implode(', ', $columnas) . ') VALUES ('
                     . implode(', ', array_fill(0, count($columnas), '?')) . ')', array_values($valores));
            $id = (int)db()->lastInsertId();
            registrar('CREAR_PACIENTE', 'pacientes', $id,
                'Nuevo paciente: ' . nombre_paciente($valores) . ' (' . codigo_paciente($id) . ')', null, $valores);
            return ['ok' => true, 'id' => $id];
        });
    } catch (PDOException $e) {
        // Dos personas registrando la misma cédula en el mismo segundo: la base frena a la segunda.
        if (clave_repetida($e) === 'uk_documento') {
            return ['ok' => false, 'errores' => ['documento' => 'Alguien acaba de registrar ese mismo documento. Búsquelo en la lista de pacientes.']];
        }
        throw $e;
    }
}
