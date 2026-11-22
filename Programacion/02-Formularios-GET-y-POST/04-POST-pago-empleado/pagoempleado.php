<?php
// Obtener datos del formulario
    $nombre = $_POST["nombre"];
    $horas = $_POST["horas"];
    $valorHora = $_POST["valorHora"];

    // Determinar horas normales y horas extras
    if ($horas <= 40) {
        $horasNormales = $horas;
        $horasExtras = 0;
    } else {
        $horasNormales = 40;
        $horasExtras = $horas - 40;
    }

    // Calcular pago de horas normales
    $pagoNormal = $horasNormales * $valorHora;

    // Calcular valor de la hora extra
    $valorHoraExtra = $valorHora * 1.50;

    // Calcular pago de horas extras
    $pagoExtra = $horasExtras * $valorHoraExtra;

    // Calcular total
    $total = $pagoNormal + $pagoExtra;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago semanal del empleado</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<div class="contenedor">
    <h2>Resultado</h2>
    <p> <strong>Empleado:</strong>
                <?php echo $nombre; ?>
    </p>
    <p><strong>Horas normales:</strong>
                <?php echo $horasNormales; ?>
    </p>
    <p> <strong>Horas extras:</strong>
        <?php echo $horasExtras; ?>
    </p>
    <p><strong>Pago horas normales:</strong>
       $<?php echo number_format($pagoNormal, 2); ?>
    </p>
    <p><strong>Pago horas extras:</strong>
       $<?php echo number_format($pagoExtra, 2); ?>
    </p>
    <p class="total"> Total a pagar:
       $<?php echo number_format($total, 2); ?>
    </p>
    <a href="inicio.html">Realizar otro cálculo</a>
 </div>
</body>
</html>
