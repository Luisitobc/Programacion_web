<?php
// El rastro: quién hizo qué y cuándo. Solo se agregan filas; en todo el
// sistema no hay una sola línea que las cambie o las borre.

declare(strict_types=1);

/**
 * Anota un movimiento. $antes y $despues son la foto del dato antes y
 * después del cambio, y se guardan como JSON.
 * $quien solo hace falta cuando todavía no hay sesión (al entrar).
 */
function registrar(string $accion, string $entidad, ?int $entidadId, string $detalle,
                   ?array $antes = null, ?array $despues = null, ?array $quien = null): void
{
    $u = $quien ?? usuario_actual();
    consulta(
        'INSERT INTO auditoria (usuario_id, usuario_nombre, accion, entidad, entidad_id, detalle, antes, despues, ip)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [
            $u['id'] ?? null,
            $u['nombre'] ?? 'Sin sesión',
            $accion,
            $entidad,
            $entidadId,
            limpiar_texto($detalle),
            $antes === null ? null : json_encode($antes, JSON_UNESCAPED_UNICODE),
            $despues === null ? null : json_encode($despues, JSON_UNESCAPED_UNICODE),
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]
    );
}

function auditoria_listar(int $limite = 150, string $entidad = ''): array
{
    $limite = max(1, min($limite, 500));
    if ($entidad !== '') {
        return filas("SELECT * FROM auditoria WHERE entidad = ? ORDER BY id DESC LIMIT $limite", [$entidad]);
    }
    return filas("SELECT * FROM auditoria ORDER BY id DESC LIMIT $limite");
}
