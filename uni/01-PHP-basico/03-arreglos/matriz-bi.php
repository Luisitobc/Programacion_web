<?php

$matriz = [
    [1, 2, 3, 4],
    [4, 5, 6, 2],
    [7, 9, 9, 3],
    [12, 2, 4, 2]
];

$filas = count($matriz);
$columnas = count($matriz[0]);

for ($i = 0; $i < $filas; $i++) {
    $sumafila = 0;

    for ($j = 0; $j < $columnas; $j++) {
        $sumafila += $matriz[$i][$j];
    }

    echo "Suma fila " . ($i + 1) . ": " . $sumafila . "<br>";
}

?>