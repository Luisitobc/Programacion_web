<?php
/* Validación en el servidor (por si se salta el HTML o se desactiva el JavaScript).
   Usa las mismas reglas que validaciones.js */
const LET = 'A-Za-zÁÉÍÓÚÜÑáéíóúüñ';

function validar($campo, $valor) {
    $valor = trim($valor);
    switch ($campo) {
        case 'id':
            if (!preg_match('/^[1-9][0-9]{0,8}$/', $valor))
                return "El id del lenguaje debe ser un número entero positivo, sin espacios ni signos (máx. 9 dígitos).";
            break;
        case 'nombre':
            if (!preg_match('/^[A-Za-z0-9+#.\-]{1,30}$/', $valor))
                return "El nombre debe ser una sola palabra, sin espacios (letras, números y + # . -, máx. 30).";
            break;
        case 'anio':
            $d = DateTime::createFromFormat('Y-m-d', $valor);
            if (!$d || $d->format('Y-m-d') !== $valor || $valor < '1940-01-01' || $valor > date('Y-m-d'))
                return "La fecha de creación no es válida (debe estar entre 1940-01-01 y hoy).";
            break;
        case 'creador':
            if (!preg_match('/^[' . LET . '][' . LET . '.\',\-]*( [' . LET . '][' . LET . '.\',\-]*)*$/u', $valor) || mb_strlen($valor) < 3 || mb_strlen($valor) > 60)
                return "El creador solo admite letras, espacios simples y . , ' - (3 a 60 caracteres, sin números).";
            break;
        case 'tipado':
            if (!preg_match('/^[' . LET . '][' . LET . ',\-]*( [' . LET . '][' . LET . ',\-]*)*$/u', $valor) || mb_strlen($valor) < 3 || mb_strlen($valor) > 40)
                return "El tipado solo admite letras, espacios simples, coma y guion (3 a 40 caracteres).";
            break;
        case 'uso':
            $c = '[' . LET . '0-9.,\/()+#\-]';
            if (!preg_match('/^' . $c . '+( ' . $c . '+)*$/u', $valor) || mb_strlen($valor) < 3 || mb_strlen($valor) > 100)
                return "El uso principal solo admite letras, números, espacios simples y . , / ( ) + # - (3 a 100 caracteres).";
            break;
    }
    return null;
}

// Lee un valor de $_POST ya recortado ('' si no llegó)
function post($campo) {
    return isset($_POST[$campo]) ? trim($_POST[$campo]) : '';
}

// Valida una lista de campos y devuelve los errores
function validar_campos($campos, $opcionales = []) {
    $errores = [];
    foreach ($campos as $c) {
        if (in_array($c, $opcionales) && post($c) === '') continue;
        $e = validar($c, post($c));
        if ($e) $errores[] = $e;
    }
    return $errores;
}

function h($t) { return htmlspecialchars($t, ENT_QUOTES, 'UTF-8'); }

function mostrar_errores($errores) {
    foreach ($errores as $e) echo "<div class='mensaje error'>" . h($e) . "</div>";
}
