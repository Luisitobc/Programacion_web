<?php
/**
 * Prueba por consola de la clase Operacion (sin interfaz web).
 * Ejecutar desde la carpeta calculadora:  php test/test.php
 */

require_once __DIR__ . '/../model/Operacion.php';

$pruebas = array(
    array("suma", 14, 5),
    array("resta", 14, 5),
    array("multiplicacion", 14, 5),
    array("division", 14, 5),
    array("division", 10, 3),
    array("division", 7, 0),
);

foreach ($pruebas as $p) {
    list($oper, $a, $b) = $p;

    $operacion = new Operacion($a, $b);

    switch ($oper) {
        case "suma":
            $suma = $operacion->suma();
            break;
        case "resta":
            $resta = $operacion->resta();
            break;
        case "multiplicacion":
            $multiplicacion = $operacion->multiplicacion();
            break;
        case "division":
            $division = $operacion->division();
            break;
    }

    echo str_pad($oper, 16) . " $a, $b -> " . ($division === null ? "No se puede dividir entre cero" : round($division, 4)) . PHP_EOL;
}