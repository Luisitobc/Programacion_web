<?php
# Calificaciones de un curso - array unidimensional

$notas = [78, 90, 65, 88, 45, 70, 81, 59];

$total = count($notas);
$promedio = array_sum($notas) / $total;
$max = max($notas);
$min = min($notas);

$aprobadas = 0;

for ($i = 0; $i < $total; $i++) {
    if ($notas[$i] >= 70) {
        $aprobadas++;
    }
}

echo "<br>Notas: " . implode(", ", $notas);
printf("<br>Promedio: %.2f <br>Mayor: %d <br>Menor: %d", $promedio, $max, $min);
echo "<br>Aprobadas: $aprobadas de $total";
echo "<br>Notas sobre el promedio: ";

for ($i = 0; $i < $total; $i++) {
    if ($notas[$i] > $promedio) {
        echo "<br>$i = " . $notas[$i];
    }
}

sort($notas);

echo "<br>Ordenadas: " . implode(", ", $notas);

?>