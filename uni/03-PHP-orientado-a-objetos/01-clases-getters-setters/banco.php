
<?php

// Clase Banco
class Banco {

    // Campos privados
    private $capital = 0.0;
    private $interes = 0.0;
    private $periodos = 0.0;

    // Constructor con parámetros
    public function __construct($capital, $interes, $periodos) {
        $this->setCapital($capital);
        $this->setInteres($interes);
        $this->setPeriodos($periodos);
    }

    // Getters
    public function getCapital() {
        return $this->capital;
    }

    public function getInteres() {
        return $this->interes;
    }

    public function getPeriodos() {
        return $this->periodos;
    }

    // Setters
    public function setCapital($capital) {
        $this->capital = $capital;
    }

    public function setInteres($interes) {
        $this->interes = $interes;
    }

    public function setPeriodos($periodos) {
        $this->periodos = $periodos;
    }

    // Calcular importe
    public function getImporte() {
        $importe = $this->capital * pow(
            (1 + $this->interes),
            $this->periodos
        );

        return round($importe, 2);
    }
}

// Procesar formulario
$banco = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $capital = (float) $_POST["capital"];
    $interes = (float) $_POST["interes"] / 100;
    $periodos = (int) $_POST["periodos"];

    if ($capital > 0 && $interes >= 0 && $periodos > 0) {
        $banco = new Banco($capital, $interes, $periodos);
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banco | Calculadora de préstamos</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 25px;
        }

        .contenedor {
            width: 100%;
            max-width: 460px;
            background: white;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 10px 35px rgba(15, 23, 42, 0.08);
        }

        .encabezado {
            text-align: center;
            margin-bottom: 30px;
        }

        .icono {
            width: 55px;
            height: 55px;
            margin: 0 auto 15px;
            background: #e8efff;
            color: #315bd6;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 27px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 9px;
        }

        .subtitulo {
            color: #64748b;
            font-size: 14px;
            line-height: 1.5;
        }

        .campo {
            margin-bottom: 19px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s;
        }

        input:focus {
            border-color: #315bd6;
            box-shadow: 0 0 0 3px rgba(49, 91, 214, 0.10);
        }

        button {
            width: 100%;
            padding: 14px;
            margin-top: 5px;
            background: #315bd6;
            color: white;
            border: none;
            border-radius: 9px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover {
            background: #2448b5;
        }

        .resultado {
            margin-top: 28px;
            padding: 22px;
            background: #f0f5ff;
            border: 1px solid #dbe7ff;
            border-radius: 12px;
        }

        .resultado h2 {
            font-size: 17px;
            margin-bottom: 18px;
        }

        .dato {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 13px;
            font-size: 14px;
        }

        .dato span:first-child {
            color: #64748b;
        }

        .dato span:last-child {
            font-weight: bold;
            text-align: right;
        }

        .total {
            border-top: 1px solid #d3def2;
            padding-top: 17px;
            margin-top: 5px;
            margin-bottom: 0;
        }

        .total span:first-child {
            color: #1e293b;
            font-weight: bold;
        }

        .total span:last-child {
            color: #2448b5;
            font-size: 21px;
        }

        .pie {
            margin-top: 25px;
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
        }

        .error {
            margin-top: 20px;
            padding: 12px;
            background: #fff1f2;
            color: #be123c;
            border-radius: 8px;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="contenedor">

        <div class="encabezado">
            <div class="icono">▤</div>
            <h1>Calculadora de préstamos</h1>
            <p class="subtitulo">
                Calcula el importe total de tu préstamo
                con interés compuesto mensual.
            </p>
        </div>

        <form method="POST">

            <div class="campo">
                <label for="capital">Capital del préstamo ($)</label>
                <input
                    type="number"
                    id="capital"
                    name="capital"
                    placeholder="Ej. 1000.00"
                    min="0.01"
                    step="0.01"
                    value="<?= isset($_POST['capital']) ? htmlspecialchars($_POST['capital']) : '' ?>"
                    required
                >
            </div>

            <div class="campo">
                <label for="interes">Interés mensual (%)</label>
                <input
                    type="number"
                    id="interes"
                    name="interes"
                    placeholder="Ej. 2.5"
                    min="0"
                    step="0.01"
                    value="<?= isset($_POST['interes']) ? htmlspecialchars($_POST['interes']) : '' ?>"
                    required
                >
            </div>

            <div class="campo">
                <label for="periodos">Plazo en meses</label>
                <input
                    type="number"
                    id="periodos"
                    name="periodos"
                    placeholder="Ej. 12"
                    min="1"
                    step="1"
                    value="<?= isset($_POST['periodos']) ? htmlspecialchars($_POST['periodos']) : '' ?>"
                    required
                >
            </div>

            <button type="submit">Calcular préstamo →</button>

        </form>

        <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>

            <?php if ($banco != null): ?>

                <div class="resultado">
                    <h2>Resumen del préstamo</h2>

                    <div class="dato">
                        <span>Capital inicial</span>
                        <span>$<?= number_format($banco->getCapital(), 2) ?></span>
                    </div>

                    <div class="dato">
                        <span>Interés mensual</span>
                        <span><?= number_format($banco->getInteres() * 100, 2) ?>%</span>
                    </div>

                    <div class="dato">
                        <span>Plazo</span>
                        <span><?= $banco->getPeriodos() ?> meses</span>
                    </div>

                    <div class="dato total">
                        <span>Total a pagar</span>
                        <span>$<?= number_format($banco->getImporte(), 2) ?></span>
                    </div>
                </div>

            <?php else: ?>

                <div class="error">
                    Ingresa un capital mayor que cero, un interés no negativo
                    y un plazo de al menos un mes.
                </div>

            <?php endif; ?>

        <?php endif; ?>

        <div class="pie">
            Sistema de cálculo financiero · Interés compuesto
        </div>

    </div>

</body>
</html>