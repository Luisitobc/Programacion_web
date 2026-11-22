
<?php

$matriz = [
    "Persona 4 Revival" => [55.99],
    "GTA VI" => [99.99],
    "Minecraft" => [29.99],
    "Lies of P" => [35.99],
    "Sekiro" => [59.99],
    "Project Zomboy" => [12.49],
    "Halo: Campaing Envolved" => [39.99]
];

// Mostrar el array original
echo "<h2 style='text-align:center; color:#2c3e50;'>Array original</h2>";

echo "<table border='1' cellpadding='10' cellspacing='0' 
style='border-collapse:collapse; width:70%; margin:auto; text-align:center;'>";

echo "<tr style='background-color:#2874a6; color:white;'>
        <th>Juego</th>
        <th>Precio</th>
        <th>Resultado</th>
      </tr>";

foreach ($matriz as $juego => $datos) {
    foreach ($datos as $precio) {
        echo "<tr>";
        echo "<td>$juego</td>";
        echo "<td>$" . number_format($precio, 2) . "</td>";

        if ($precio > 50) {
            echo "<td style='color:red;'>No me alcanza</td>";
        } else {
            echo "<td style='color:green;'>Sí me alcanza</td>";
        }

        echo "</tr>";
    }
}

echo "</table>";

// Ordenamiento alfabético con bucles
$claves = array_keys($matriz);
$n = count($claves);

for ($i = 0; $i < $n - 1; $i++) {
    for ($j = 0; $j < $n - 1 - $i; $j++) {
        if (strcmp($claves[$j], $claves[$j + 1]) > 0) {
            $aux = $claves[$j];
            $claves[$j] = $claves[$j + 1];
            $claves[$j + 1] = $aux;
        }
    }
}

// Crear el array ordenado
$ordenado = [];

for ($i = 0; $i < $n; $i++) {
    $juego = $claves[$i];
    $ordenado[$juego] = $matriz[$juego];
}

// Mostrar el array ordenado
echo "<h2 style='text-align:center; color:#2c3e50; margin-top:30px;'>
      Array ordenado alfabéticamente</h2>";

echo "<table border='1' cellpadding='10' cellspacing='0' 
style='border-collapse:collapse; width:70%; margin:auto; text-align:center;'>";

echo "<tr style='background-color:#2874a6; color:white;'>
        <th>Juego</th>
        <th>Precio</th>
        <th>Resultado</th>
      </tr>";

foreach ($ordenado as $juego => $datos) {
    foreach ($datos as $precio) {
        echo "<tr>";
        echo "<td>$juego</td>";
        echo "<td>$" . number_format($precio, 2) . "</td>";

        if ($precio > 50) {
            echo "<td style='color:red;'>No me alcanza</td>";
        } else {
            echo "<td style='color:green;'>Sí me alcanza</td>";
        }

        echo "</tr>";
    }
}

echo "</table>";

?>