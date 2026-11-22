<?php

// --------------------------------------------------
// FUNCIÓN 1: Calcular valor de horas extras
// --------------------------------------------------

function calcularExtras($horas, $valorHora)
{
    return $horas * $valorHora;
}


// --------------------------------------------------
// FUNCIÓN 2: Calcular bono según tipo de empleado
// --------------------------------------------------

function calcularBono($sueldo, $tipo)
{
    $porcentaje = 0;

    switch ($tipo) {

        case "Administrativo":
            $porcentaje = 0.05;
            break;

        case "Tecnico":
            $porcentaje = 0.08;
            break;

        case "Operativo":
            $porcentaje = 0.10;
            break;
    }

    return $sueldo * $porcentaje;
}


// --------------------------------------------------
// FUNCIÓN 3: Calcular sueldo bruto
// --------------------------------------------------

function calcularSueldoBruto($sueldo, $extras, $bono)
{
    return $sueldo + $extras + $bono;
}


// --------------------------------------------------
// FUNCIÓN 4: Calcular aporte al IESS
// --------------------------------------------------

function calcularIESS($sueldoBruto)
{
    return $sueldoBruto * 0.0945;
}


// --------------------------------------------------
// FUNCIÓN 5: Calcular sueldo neto
// --------------------------------------------------

function calcularSueldoNeto($sueldoBruto, $iess)
{
    return $sueldoBruto - $iess;
}


// --------------------------------------------------
// FUNCIÓN 6: Formatear valores monetarios
// --------------------------------------------------

function formatearMoneda($valor)
{
    return "$ " . number_format($valor, 2);
}


// --------------------------------------------------
// RECEPCIÓN DE DATOS DEL FORMULARIO
// --------------------------------------------------

$nombre = trim($_POST["nombre"] ?? "");
$sueldo = floatval($_POST["sueldo"] ?? 0);
$horasExtras = intval($_POST["horasExtras"] ?? 0);
$valorHora = floatval($_POST["valorHora"] ?? 0);
$tipo = $_POST["tipo"] ?? "";


// --------------------------------------------------
// VALIDACIÓN DEL SERVIDOR
// --------------------------------------------------

if (
    $nombre === "" ||
    $sueldo <= 0 ||
    $horasExtras < 0 ||
    $valorHora < 0 ||
    $tipo === ""
) {
    die("Error: los datos ingresados no son válidos.");
}


// --------------------------------------------------
// PROCESAMIENTO
// --------------------------------------------------

$extras = calcularExtras($horasExtras, $valorHora);

$bono = calcularBono($sueldo, $tipo);

$sueldoBruto = calcularSueldoBruto(
    $sueldo,
    $extras,
    $bono
);

$iess = calcularIESS($sueldoBruto);

$sueldoNeto = calcularSueldoNeto(
    $sueldoBruto,
    $iess
);

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Resultado</title>

    <link rel="stylesheet" href="estilos.css">

</head>

<body>

<div class="contenedor">

    <h1>Resultado del cálculo</h1>

    <p>
        <strong>Empleado:</strong>
        <?php echo htmlspecialchars($nombre); ?>
    </p>

    <p>
        <strong>Tipo:</strong>
        <?php echo htmlspecialchars($tipo); ?>
    </p>

    <hr>

    <p>
        Sueldo básico:
        <?php echo formatearMoneda($sueldo); ?>
    </p>

    <p>
        Horas extras:
        <?php echo formatearMoneda($extras); ?>
    </p>

    <p>
        Bono:
        <?php echo formatearMoneda($bono); ?>
    </p>

    <p>
        Sueldo bruto:
        <?php echo formatearMoneda($sueldoBruto); ?>
    </p>

    <p>
        Descuento IESS:
        <?php echo formatearMoneda($iess); ?>
    </p>

    <hr>

    <h2>
        Sueldo neto:
        <?php echo formatearMoneda($sueldoNeto); ?>
    </h2>

    <button onclick="window.location.href='inicio.html'">
        Nuevo cálculo
    </button>

</div>

</body>

</html>