<?php

function promedio($arreglo) {
    $suma = 0;

    foreach ($arreglo as $numero) {
        $suma += $numero;
    }

    return $suma / count($arreglo);
}

$numeros = [2, 5, 20, 5];

echo "El promedio es: " . promedio($numeros);

?>