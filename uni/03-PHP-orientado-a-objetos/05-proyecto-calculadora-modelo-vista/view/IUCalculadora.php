<?php
/**
 * Proyecto: Calculadora (frontend)
 * Archivo: calculadora/view/IUCalculadora.php
 *
 * Flujo:
 *   1. El usuario escribe dos números y elige la operación en el formulario.
 *   2. Un switch decide qué operación se ejecuta.
 *   3. En cada case se crea un objeto de la clase Operacion
 *      ($suma, $resta, $multiplicacion, $division) y se invoca su método.
 */
require_once '../model/Operacion.php';

// Quita ceros sobrantes: 5.50000000 -> 5.5
function formatear($numero)
{
    return rtrim(rtrim(number_format($numero, 8, '.', ''), '0'), '.');
}

$mostrarResultado = false;
$mensaje = null;
$num1 = '';
$num2 = '';
$oper = 'suma';
$simbolo = '';
$resultado = null;

if (isset($_POST["procesar"])) {
    // Datos que llegan del frontend
    $num1 = trim($_POST["num1"] ?? '');
    $num2 = trim($_POST["num2"] ?? '');
    $oper = $_POST["oper"] ?? '';

    if (!is_numeric($num1) || !is_numeric($num2)) {
        $mensaje = "Ingrese dos números válidos.";
    } else {
        $n1 = (float)$num1;
        $n2 = (float)$num2;

        // El switch elige la operación solicitada desde el frontend
        switch ($oper) {
            case "suma":
                $suma = new Operacion();
                $suma->setNum1($n1);
                $suma->setNum2($n2);
                $resultado = $suma->suma();
                $simbolo = "+";
                break;

            case "resta":
                $resta = new Operacion();
                $resta->setNum1($n1);
                $resta->setNum2($n2);
                $resultado = $resta->resta();
                $simbolo = "-";
                break;

            case "multiplicacion":
                $multiplicacion = new Operacion();
                $multiplicacion->setNum1($n1);
                $multiplicacion->setNum2($n2);
                $resultado = $multiplicacion->multiplicacion();
                $simbolo = "×";
                break;

            case "division":
                $division = new Operacion();
                $division->setNum1($n1);
                $division->setNum2($n2);
                $resultado = $division->division();
                $simbolo = "÷";
                if ($resultado === null) {
                    $mensaje = "No se puede dividir entre cero.";
                }
                break;

            default:
                $mensaje = "Operación no válida.";
        }

        if ($mensaje === null) {
            $mostrarResultado = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/estilos.css">
    <title>Calculadora</title>
</head>
<body>
<div class="contenedor">
    <h1>Calculadora</h1>

    <?php if ($mensaje) { ?>
        <div class="error"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php } ?>

    <form name="form1" method="post" action="IUCalculadora.php">
        <table>
            <tr>
                <th>Operando 1</th>
                <th>Operación</th>
                <th>Operando 2</th>
            </tr>
            <tr>
                <td><input name="num1" type="text" id="num1" maxlength="12"
                           value="<?php echo htmlspecialchars($num1); ?>"></td>
                <td>
                    <select name="oper" id="oper">
                        <option value="suma" <?php echo $oper == 'suma' ? 'selected' : ''; ?>>Sumar</option>
                        <option value="resta" <?php echo $oper == 'resta' ? 'selected' : ''; ?>>Restar</option>
                        <option value="multiplicacion" <?php echo $oper == 'multiplicacion' ? 'selected' : ''; ?>>Multiplicar</option>
                        <option value="division" <?php echo $oper == 'division' ? 'selected' : ''; ?>>Dividir</option>
                    </select>
                </td>
                <td><input name="num2" type="text" id="num2" maxlength="12"
                           value="<?php echo htmlspecialchars($num2); ?>"></td>
            </tr>
        </table>
        <input name="procesar" type="submit" class="boton" id="procesar" value="Calcular">
    </form>

    <?php if ($mostrarResultado) { ?>
        <h2>Resultado</h2>
        <div class="resultado-grande">
            <?php echo htmlspecialchars(formatear((float)$num1)); ?>
            <?php echo $simbolo; ?>
            <?php echo htmlspecialchars(formatear((float)$num2)); ?>
            = <?php echo formatear($resultado); ?>
        </div>
    <?php } ?>
</div>
</body>
</html>
