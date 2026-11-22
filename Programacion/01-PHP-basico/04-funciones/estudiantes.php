<?php

// Calcular el promedio notas
function calcularPromedio($notas) {
    return array_sum($notas) / count($notas);
}

// Clasificar al estudiante según promedio
function clasificar($promedio) {
    if ($promedio >= 70) {
        return "Aprobado";
    } else {
        return "Reprobado";
    }
}

// Procesar todos los estudiantes
function procesarEstudiantes($estudiantes) {
    $resultado = [];

    foreach ($estudiantes as $nombre => $notas) {

        $promedio = calcularPromedio($notas);
        $estado = clasificar($promedio);

        $resultado[$nombre] = [
            "promedio" => $promedio,
            "estado" => $estado
        ];
    }

    return $resultado;
}

// Ordenar estudiantes de mayor a menor promedio
function ordenarRanking($estudiantes) {
    uasort($estudiantes, function($a, $b) {
        return $b["promedio"] <=> $a["promedio"];
    });

    return $estudiantes;
}

// Generar el reporte completo
function generarReporte($estudiantes) {

    // Procesar estudiantes
    $procesados = procesarEstudiantes($estudiantes);

    // Ordenar ranking
    $ranking = ordenarRanking($procesados);

    // Calcular promedio general
    $suma = 0;

    foreach ($ranking as $estudiante) {
        $suma += $estudiante["promedio"];
    }

    $promedioGeneral = $suma / count($ranking);

    // Obtener mejor estudiante
    $mejorEstudiante = array_key_first($ranking);

    // Contar aprobados
    $aprobados = 0;

    foreach ($ranking as $estudiante) {
        if ($estudiante["estado"] == "Aprobado") {
            $aprobados++;
        }
    }

    // Mostrar reporte
    echo "===== REPORTE DEL CURSO =====\n\n";

    echo "RANKING:\n";

    $posicion = 1;

    foreach ($ranking as $nombre => $estudiante) {
        echo $posicion . ". " . $nombre;
        echo " - Promedio: " . number_format($estudiante["promedio"], 2);
        echo " - Estado: " . $estudiante["estado"] . "\n";

        $posicion++;
    }

    echo "\nPromedio general: " . number_format($promedioGeneral, 2) . "\n";
    echo "Mejor estudiante: " . $mejorEstudiante . "\n";
    echo "Aprobados: " . $aprobados . "\n";
}


// Entrada: arreglo asociativo
$estudiantes = [
    "Mauricio" => [99],
    "Mishell" => [98],
    "Erick" => [999]
];

// Generar reporte
generarReporte($estudiantes);

?>