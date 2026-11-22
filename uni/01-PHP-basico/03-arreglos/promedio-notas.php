<?php

$notas = [
    "metodos" => 3.10,
    "modelamiento" => 4,
    "sistemas" => 4.0,
    "base de datos" => 3.30,
    "programacion web" => 3.40,
    "estadistica" => 2.42,
    "sostenibilidad" => 3.50
];


function promedio(array $notas): float {
    if (count($notas) === 0) {
        return 0;
    }

    $suma = 0.0;
    foreach($notas as $nota) {
        $suma += $nota;
    }

    return round($suma / count($notas), 2);
}

// nota mas alta
function notaAlta(array $notas): float {
    if (count($notas) === 0) {
        return 0;
    }

    $notaAlta = max($notas);
    return round($notaAlta, 2);
}

//nota mas baja
function notaBaja(array $notas): float {
    if (count($notas) === 0) {
        return 0;
    }

    $notaBaja = min($notas);
    return round($notaBaja, 2);
}

echo promedio($notas);
echo "\n";
echo notaAlta($notas);
echo "\n";
echo notaBaja($notas);

?>