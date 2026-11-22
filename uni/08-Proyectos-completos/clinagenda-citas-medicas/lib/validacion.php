<?php
// Las reglas que dicen "este dato está mal", más las utilidades de texto y
// fechas. Los mensajes están escritos para la recepcionista: dicen qué está
// mal y cómo arreglarlo, nunca "error de validación".

declare(strict_types=1);

/* ---------- Texto ---------- */

/** Para guardar: quita caracteres de control y espacios repetidos. Respeta las tildes. */
function limpiar_texto(?string $s, int $largo = 255): string
{
    $s = (string)preg_replace('/[\x00-\x1F\x7F]/u', ' ', (string)$s);
    $s = trim((string)preg_replace('/\s+/u', ' ', $s));
    return mb_substr($s, 0, $largo);
}

/**
 * Para buscar: mayúsculas, sin tildes y con un solo espacio.
 * "MARÍA lópez" y "Maria Lopez" dan lo mismo y salen juntas en la búsqueda,
 * pero el sistema nunca las une solo: decide la recepcionista.
 */
function normalizar(?string $s): string
{
    $s = mb_strtoupper(limpiar_texto($s), 'UTF-8');
    return strtr($s, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N']);
}

function solo_digitos(?string $s): string
{
    return (string)preg_replace('/\D/', '', (string)$s);
}

/** Escapa para mostrar en HTML. Se usa siempre que se imprime algo que escribió alguien. */
function h(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Iniciales para los círculos, sin títulos: "Dra. Ana Paredes" da "AP". */
function iniciales(string $nombre): string
{
    $partes = preg_split('/\s+/u', limpiar_texto($nombre)) ?: [];
    $partes = array_values(array_filter($partes,
        fn($p) => !preg_match('/^(dr|dra|lcda|lcdo|ing|sr|sra)\.?$/iu', $p)));
    $ini = '';
    foreach (array_slice($partes, 0, 2) as $p) $ini .= mb_substr($p, 0, 1, 'UTF-8');
    return mb_strtoupper($ini, 'UTF-8');
}

/* ---------- Horas y fechas ---------- */

function a_minutos(string $hhmm): int
{
    [$h, $m] = array_map('intval', explode(':', $hhmm) + [0, 0]);
    return $h * 60 + $m;
}

function a_hora(int $min): string
{
    return sprintf('%02d:%02d', intdiv($min, 60), $min % 60);
}

/** ¿Es una fecha real con formato AAAA-MM-DD? (rechaza 2026-02-31) */
function fecha_valida(?string $f): bool
{
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', (string)$f, $m)) return false;
    return checkdate((int)$m[2], (int)$m[3], (int)$m[1]);
}

function fecha_larga(string $fecha): string
{
    $dias  = [1 => 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
    $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
              'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $t = strtotime($fecha);
    if ($t === false) return $fecha;
    return $dias[(int)date('N', $t)] . ' ' . date('j', $t) . ' de ' . $meses[(int)date('n', $t)];
}

function fecha_corta(string $fecha): string
{
    $t = strtotime($fecha);
    return $t === false ? $fecha : date('d/m/Y', $t);
}

function es_hoy(string $fecha): bool
{
    return $fecha === date('Y-m-d');
}

function nombre_dia(string $fecha): string
{
    return [1 => 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'][(int)date('N', strtotime($fecha))];
}

/* ---------- Documentos y contacto ---------- */

/**
 * Cédula ecuatoriana con dígito verificador (módulo 10).
 * 0000000000 cae porque la provincia 00 no existe, y una cédula inventada
 * que "parece" real cae en el último dígito. Esa es la diferencia entre
 * validar el formato y validar el dato.
 */
function cedula_valida(string $c): bool
{
    if (!preg_match('/^\d{10}$/', $c)) return false;
    $prov = (int)substr($c, 0, 2);
    if (($prov < 1 || $prov > 24) && $prov !== 30) return false;
    if ((int)$c[2] > 5) return false;                 // persona natural

    $coef = [2, 1, 2, 1, 2, 1, 2, 1, 2];
    $suma = 0;
    for ($i = 0; $i < 9; $i++) {
        $p = (int)$c[$i] * $coef[$i];
        $suma += $p > 9 ? $p - 9 : $p;
    }
    return (10 - ($suma % 10)) % 10 === (int)$c[9];
}

/** Celular 09XXXXXXXX (10 dígitos) o fijo 0 + provincia + 7 dígitos (9 dígitos). */
function telefono_valido(string $t): bool
{
    return (bool)preg_match('/^(09\d{8}|0[2-7]\d{7})$/', $t);
}

function correo_valido(string $e): bool
{
    return (bool)filter_var($e, FILTER_VALIDATE_EMAIL);
}

function pasaporte_valido(string $p): bool
{
    return (bool)preg_match('/^[A-Z0-9]{6,15}$/', $p);
}

/** Letras (con tildes), espacios, apóstrofo y guion. Nada de números ni símbolos. */
function nombre_valido(string $n): bool
{
    return (bool)preg_match("/^[\p{L}\p{M}][\p{L}\p{M}' -]*$/u", $n);
}

/** Devuelve un mensaje si la fecha de nacimiento no tiene sentido, o null si está bien. */
function problema_fecha_nacimiento(string $f): ?string
{
    if (!fecha_valida($f)) return 'Escriba la fecha completa: día, mes y año.';
    if ($f > date('Y-m-d')) return 'Revise el año: esa fecha aún no ha llegado.';
    if ($f < '1900-01-01' || (new DateTime($f))->diff(new DateTime('today'))->y > 120)
        return 'Revise el año: esa fecha da más de 120 años de edad.';
    return null;
}
